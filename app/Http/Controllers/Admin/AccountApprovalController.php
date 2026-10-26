<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Services\Admin\AccountApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountApprovalController extends Controller
{
    public function __construct(
        private AccountApprovalService $approvalService
    ) {}

    public function index(): View
    {
        $pendingUsers = $this->approvalService->getPendingUsers();

        return view('admin.users.pending', compact('pendingUsers'));
    }

    public function approve(User $user): RedirectResponse
    {
        if (! in_array($user->status, [User::STATUS_PENDING_APPROVAL, User::STATUS_REJECTED])) {
            return redirect()->route('admin.users.pending')->with('error', __('Invalid request.'));
        }

        $this->approvalService->approve($user, auth()->id());

        return redirect()->route('admin.users.pending')->with('success', __('Account approved successfully.'));
    }

    public function showRejectForm(User $user): View|RedirectResponse
    {
        if ($user->status !== User::STATUS_PENDING_APPROVAL) {
            return redirect()->route('admin.users.pending')->with('error', __('Invalid request.'));
        }

        return view('admin.users.reject-form', compact('user'));
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_PENDING_APPROVAL) {
            return redirect()->route('admin.users.pending')->with('error', __('Invalid request.'));
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->approvalService->reject($user, $validated['rejection_reason'], auth()->id());

        return redirect()->route('admin.users.pending')->with('success', __('Account rejected.'));
    }

    public function showApplication(User $user): View|RedirectResponse
    {
        if ($user->membership_type === User::MEMBERSHIP_PROVIDER && $user->providerProfile) {
            return $this->showProviderApplication($user);
        }
        if ($user->membership_type === User::MEMBERSHIP_RECIPIENT && $user->recipientProfile) {
            return $this->showRecipientApplication($user);
        }

        return redirect()->route('admin.users.pending')->with('error', __('No application found.'));
    }

    public function showProviderApplication(User $user): View|RedirectResponse
    {
        if (! $user->providerProfile) {
            return redirect()->route('admin.users.pending')->with('error', __('No provider application found.'));
        }

        return view('admin.users.provider-application', [
            'user' => $user,
            'providerData' => [
                'profile' => $user->providerProfile,
                'operating' => $user->providerOperatingInfo,
                'financial' => $user->providerFinancialInfo,
                'documents' => $user->providerDocuments,
            ],
        ]);
    }

    public function showRecipientApplication(User $user): View|RedirectResponse
    {
        if (! $user->recipientProfile) {
            return redirect()->route('admin.users.pending')->with('error', __('No recipient application found.'));
        }

        $registrationLog = Activity::where('causer_id', $user->id)
            ->where('description', 'registration.completed')
            ->first();

        return view('admin.users.recipient-application', [
            'user' => $user,
            'registrationLog' => $registrationLog,
        ]);
    }

    public function serveFile(User $user, string $type): StreamedResponse|RedirectResponse
    {
        $path = $this->approvalService->resolveFilePath($user, $type);

        if (! $path || ! Storage::disk(config('filesystems.private_disk'))->exists($path)) {
            abort(404);
        }

        return Storage::disk(config('filesystems.private_disk'))->response($path);
    }
}
