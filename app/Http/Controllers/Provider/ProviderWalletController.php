<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\FundTransaction;
use App\Models\ProviderPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProviderWalletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->providerProfile;
        $wallet = $profile?->ewallet;

        $transactions = FundTransaction::query()
            ->where('wallet_id', $wallet?->id ?? 0)
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'tx_page');

        $payouts = ProviderPayout::query()
            ->where('provider_id', $user->id)
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'payout_page');

        return view('provider.wallet.index', compact('wallet', 'transactions', 'payouts', 'profile'));
    }

    public function downloadReceipt(Request $request, ProviderPayout $providerPayout): StreamedResponse
    {
        if ((int) $providerPayout->provider_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($providerPayout->status !== ProviderPayout::STATUS_TRANSFERRED || ! $providerPayout->receipt_path) {
            abort(404);
        }

        if (! Storage::disk(config('filesystems.private_disk'))->exists($providerPayout->receipt_path)) {
            abort(404);
        }

        return Storage::disk(config('filesystems.private_disk'))->response($providerPayout->receipt_path);
    }
}
