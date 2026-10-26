<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderPayout;
use App\Services\AuditService;
use App\Services\ProviderPayoutConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProviderPayoutController extends Controller
{
    public function __construct(
        private ProviderPayoutConfirmationService $confirmationService,
        private AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $query = ProviderPayout::query()
            ->with(['provider.providerProfile'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('provider_id')) {
            $query->where('provider_id', (int) $request->input('provider_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->string('date_to'));
        }

        $payouts = $query->paginate(20)->withQueryString();

        return view('admin.finances.provider-payouts.index', compact('payouts'));
    }

    public function show(ProviderPayout $providerPayout): View
    {
        $providerPayout->load([
            'items.fundTransaction.request',
            'items.fundTransaction.orderRedemption',
            'provider.providerProfile',
            'providerWallet',
            'confirmedBy',
            'rejectedBy',
            'cancelledBy',
            'fundTransactionOut',
        ]);

        return view('admin.finances.provider-payouts.show', ['payout' => $providerPayout]);
    }

    public function storeReceipt(Request $request, ProviderPayout $providerPayout): RedirectResponse
    {
        if (! $providerPayout->isConfirmable()) {
            return back()->with('error', __('finance.provider_payouts.errors.not_editable'));
        }

        $validated = $request->validate([
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $validated['receipt']->store('provider-payout-receipts/'.$providerPayout->id, config('filesystems.private_disk'));

        $providerPayout->update(['receipt_path' => $path]);

        $this->auditService->log('provider_payout', 'payout_receipt_uploaded', [
            'decision' => 'upload_receipt',
            'provider_payout_id' => $providerPayout->id,
            'path' => $path,
        ], auth()->id());

        return back()->with('success', __('finance.provider_payouts.receipt_saved'));
    }

    public function receiptFile(ProviderPayout $providerPayout): StreamedResponse|RedirectResponse
    {
        $path = $providerPayout->receipt_path;
        if (! $path || ! Storage::disk(config('filesystems.private_disk'))->exists($path)) {
            abort(404);
        }

        return Storage::disk(config('filesystems.private_disk'))->response($path);
    }

    public function confirm(Request $request, ProviderPayout $providerPayout): RedirectResponse
    {
        $rules = [
            'reference_number' => ['nullable', 'string', 'max:200'],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];

        if (! $providerPayout->receipt_path) {
            $rules['receipt'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'];
        }

        $validated = $request->validate($rules);

        $receiptPath = $providerPayout->receipt_path;
        if (! empty($validated['receipt'])) {
            $receiptPath = $validated['receipt']->store('provider-payout-receipts/'.$providerPayout->id, config('filesystems.private_disk'));
            $providerPayout->update(['receipt_path' => $receiptPath]);
            $this->auditService->log('provider_payout', 'payout_receipt_uploaded', [
                'provider_payout_id' => $providerPayout->id,
                'path' => $receiptPath,
            ], auth()->id());
        }

        if (! $receiptPath) {
            return back()->with('error', __('finance.provider_payouts.receipt_required'));
        }

        try {
            $this->confirmationService->confirm(
                $providerPayout->fresh(),
                auth()->user(),
                $validated['reference_number'] ?? null,
                $receiptPath,
                $validated['admin_note'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.finances.provider-payouts.show', $providerPayout)
            ->with('success', __('finance.provider_payouts.confirmed'));
    }

    public function reject(Request $request, ProviderPayout $providerPayout): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $this->confirmationService->reject(
                $providerPayout,
                auth()->user(),
                $validated['admin_note'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.finances.provider-payouts.show', $providerPayout)
            ->with('success', __('finance.provider_payouts.rejected'));
    }

    public function cancel(Request $request, ProviderPayout $providerPayout): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $this->confirmationService->cancel(
                $providerPayout,
                auth()->user(),
                $validated['admin_note'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.finances.provider-payouts.show', $providerPayout)
            ->with('success', __('finance.provider_payouts.cancelled'));
    }
}
