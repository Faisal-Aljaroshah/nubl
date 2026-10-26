<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\NotificationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreProviderProofRequest;
use App\Models\OrderProof;
use App\Models\OrderRedemption;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProviderProofController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private NotificationServiceInterface $notificationService
    ) {}

    public function index($redemptionId)
    {
        $redemption = OrderRedemption::where('provider_id', auth()->id())
            ->with(['request.items.menuItem.menuItemCategory'])
            ->findOrFail($redemptionId);

        if ($redemption->status !== 'REDEEMED') {
            return redirect()->route('provider.qr.scan')->with('error', __('Only REDEEMED orders can have proof uploaded.'));
        }

        if ($redemption->proof) {
            return redirect()->route('provider.requests.index')->with('success', __('Proof has already been uploaded for this order.'));
        }

        return view('provider.qr.proof', compact('redemption'));
    }

    public function store(StoreProviderProofRequest $request, $redemptionId)
    {
        $redemption = OrderRedemption::where('provider_id', auth()->id())->findOrFail($redemptionId);

        if ($redemption->status !== 'REDEEMED') {
            return back()->with('error', __('You can only upload proof for redeemed tokens.'));
        }

        if (! $request->hasFile('proof_file') && empty($request->input('proof_photo_base64'))) {
            return back()->with('error', __('You must either upload a file or capture a photo.'));
        }

        $path = null;

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $path = $file->store("private/proofs/{$redemption->id}", config('filesystems.private_disk'));
        } elseif (! empty($request->input('proof_photo_base64'))) {
            $base64Data = $request->input('proof_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc.

                if (! in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    return back()->with('error', __('Invalid image format captured.'));
                }

                $data = base64_decode($data);
                if ($data === false) {
                    return back()->with('error', __('Failed to decode captured image.'));
                }

                $fileName = 'capture_'.time().'.'.$type;
                $path = "private/proofs/{$redemption->id}/$fileName";
                Storage::disk(config('filesystems.private_disk'))->put($path, $data);
            } else {
                return back()->with('error', __('Invalid image data received.'));
            }
        }

        try {
            DB::beginTransaction();

            $redemption->refresh();
            if ($redemption->proof) {
                DB::rollBack();
                if ($path !== null) {
                    Storage::disk(config('filesystems.private_disk'))->delete($path);
                }

                return redirect()->route('provider.requests.index')->with('success', __('Proof already exists.'));
            }

            OrderProof::create([
                'order_redemption_id' => $redemption->id,
                'proof_url' => $path,
                'is_provider_donation' => $redemption->request->funding_source === 'PROVIDER_ADOPTION',
                'fulfilled_at' => now(),
            ]);

            // Mark the Request as FULFILLED safely
            $requestModel = $redemption->request;
            $requestModel->status = 'FULFILLED';
            $requestModel->save();

            // Supplement RequestObserver status_changed: keep proof/redemption context for audit trail
            $this->auditService->log('request', 'fulfillment_proof_uploaded', [
                'request_id' => $requestModel->id,
                'redemption_id' => $redemption->id,
                'proof_url' => $path,
            ], auth()->id());

            $this->notificationService->sendRequestStatusChanged($requestModel->load('recipient'), 'FULFILLED');
            $this->auditService->log('notification', 'sent', [
                'type' => 'request_status_changed',
                'recipient_user_id' => $requestModel->recipient_id,
                'request_id' => $requestModel->id,
                'status' => 'FULFILLED',
            ]);

            DB::commit();

            return redirect()->route('provider.requests.index')->with('success', __('Request successfully fulfilled!'));
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk(config('filesystems.private_disk'))->delete($path); // clean up file

            return back()->with('error', __('Failed to upload proof. Please try again.'));
        }
    }
}
