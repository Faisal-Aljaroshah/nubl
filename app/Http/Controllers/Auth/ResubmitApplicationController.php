<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateResubmitApplicationRequest;
use App\Models\User;
use App\Services\ResubmitApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rejected recipients/providers edit the same application data shown on the admin review screen.
 * Status returns to pending_approval; admin notification via UserObserver.
 */
class ResubmitApplicationController extends Controller
{
    public function __construct(
        private ResubmitApplicationService $resubmitApplicationService,
    ) {}

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user->status !== User::STATUS_REJECTED) {
            return redirect()->route('approval.pending');
        }
        if (! in_array($user->membership_type, [User::MEMBERSHIP_RECIPIENT, User::MEMBERSHIP_PROVIDER], true)) {
            abort(403);
        }

        if ($user->membership_type === User::MEMBERSHIP_PROVIDER) {
            return view('auth.resubmit-provider', $this->resubmitApplicationService->prepareProviderEditData($user));
        }

        return view('auth.resubmit-recipient', $this->resubmitApplicationService->prepareRecipientEditData($user));
    }

    /**
     * Serve current application documents to the authenticated applicant only (for previews on resubmit form).
     */
    public function serveFile(Request $request, string $type): StreamedResponse
    {
        $path = $this->resubmitApplicationService->resolveDocumentPath($request->user(), $type);

        if ($path === null) {
            abort(404);
        }

        return Storage::disk(config('filesystems.private_disk'))->response($path);
    }

    public function update(UpdateResubmitApplicationRequest $request): RedirectResponse
    {
        $user = $request->user();

        return $user->membership_type === User::MEMBERSHIP_RECIPIENT
            ? $this->updateRecipient($request, $user)
            : $this->updateProvider($request, $user);
    }

    private function updateRecipient(UpdateResubmitApplicationRequest $request, User $user): RedirectResponse
    {
        $idPhoto = $request->filled('id_photo_base64') ? $request->string('id_photo_base64')->toString() : null;

        $ok = $this->resubmitApplicationService->resubmitRecipient(
            $user,
            $request->validated(),
            $idPhoto,
        );

        return $this->redirectAfterResubmit($ok);
    }

    private function updateProvider(UpdateResubmitApplicationRequest $request, User $user): RedirectResponse
    {
        $ok = $this->resubmitApplicationService->resubmitProvider(
            $user,
            $request->validated(),
            $request->normalizedOperatingHours(),
            $request->hasFile('business_license') ? $request->file('business_license') : null,
            $request->hasFile('id_or_iqama') ? $request->file('id_or_iqama') : null,
        );

        return $this->redirectAfterResubmit($ok);
    }

    private function redirectAfterResubmit(bool $ok): RedirectResponse
    {
        if (! $ok) {
            return redirect()->route('approval.pending')->with('error', __('Application data is incomplete.'));
        }

        return redirect()->route('approval.pending')->with('success', __('Your application has been submitted for review.'));
    }
}
