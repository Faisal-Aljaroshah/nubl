<?php

namespace App\Services;

use App\Models\FundTransaction;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

/**
 * Aggregates real database figures for the public landing page.
 *
 * Privacy contract:
 *  - No recipient names, IDs, phone numbers, or addresses are exposed.
 *  - Feed / ledger entries use only provider city and an opaque reference code.
 *  - Counts are always integers (no fraction that could imply a single record).
 *  - Falls back to static translation copy when the database has no data yet.
 *  - Fallback data is clearly marked `is_live => false` so views can label it correctly.
 */
class LandingPageStatsService
{
    /**
     * Returns all landing-page statistics in one call.
     *
     * @return array{
     *   totalDelivered: int,
     *   familiesSupported: int,
     *   localProviders: int,
     *   feedItems: list<array{row1: string, row2: string}>,
     *   trustLedger: array{is_live: bool, rows: list<array{desc: string, meta: string, amount: int}>, shown: int, total: int},
     *   trustBadges: array{delivered: float, held: float}|null,
     *   providerCounts: array{grocery: int, catering: int, bakery: int, restaurant: int},
     * }
     *
     * `feedItems` is merged after the cache: it is always computed fresh so the hero
     * live ticker is not tied to the 5‑minute hero-stats cache. Use
     * {@see getLiveFeedItems()} for the same payload from JSON polling.
     */
    public function getHeroStats(): array
    {
        $cached = Cache::remember('landing_hero_stats_' . app()->getLocale(), 300, fn () => [
            'totalDelivered' => $this->totalDelivered(),
            'familiesSupported' => $this->familiesSupported(),
            'localProviders' => $this->localProviders(),
            'trustLedger' => $this->trustLedgerEntries(),
            'trustBadges' => $this->trustBadges(),
            'providerCounts' => $this->providerCategoryCounts(),
        ]);
        $cached['feedItems'] = $this->getLiveFeedItems();

        return $cached;
    }

    /**
     * Privacy-safe live feed rows (same as merged into getHeroStats); never cached.
     *
     * @return list<array{row1: string, row2: string}>
     */
    public function getLiveFeedItems(): array
    {
        return $this->liveFeedItems();
    }

    /**
     * Returns hero numbers + feed items in a single uncached call for live polling.
     *
     * @return array{totalDelivered: int, familiesSupported: int, localProviders: int, feedItems: list<array{row1: string, row2: string}>}
     */
    public function getLiveSnapshot(): array
    {
        return [
            'totalDelivered' => $this->totalDelivered(),
            'familiesSupported' => $this->familiesSupported(),
            'localProviders' => $this->localProviders(),
            'feedItems' => $this->liveFeedItems(),
        ];
    }

    private const DELIVERED_SOURCES = [
        FundTransaction::SOURCE_PAYOUT,
        FundTransaction::SOURCE_REDEMPTION,
    ];

    /**
     * Total SAR delivered: sum of PAYOUT / REDEMPTION IN fund transactions.
     * Only counts money that physically changed hands at the provider (QR scanned),
     * not funds merely reserved/approved.
     */
    private function totalDelivered(): int
    {
        return (int) FundTransaction::whereIn('source', self::DELIVERED_SOURCES)
            ->where('direction', FundTransaction::DIRECTION_IN)
            ->sum('amount');
    }

    /**
     * Unique recipient families who have had a QR physically scanned at the provider:
     * count of distinct recipient_id values on requests linked to a PAYOUT / REDEMPTION IN
     * fund transaction (created at QR scan time, before proof upload).
     */
    private function familiesSupported(): int
    {
        return Request::whereIn('id', function ($q) {
            $q->select('request_id')
                ->from('fund_transactions')
                ->whereIn('source', self::DELIVERED_SOURCES)
                ->where('direction', FundTransaction::DIRECTION_IN)
                ->whereNotNull('request_id');
        })->distinct('recipient_id')->count('recipient_id');
    }

    /**
     * Providers publicly visible to recipients right now.
     *
     * Uses the same `openForRecipients` scope as ProviderMenuController so the
     * landing page count matches what recipients actually see:
     *   - Spatie `provider` role assigned
     *   - status = active, is_active = true, accepting_orders = true
     *   - provider profile record exists
     */
    private function localProviders(): int
    {
        return $this->visibleProviderQuery()->count();
    }

    /**
     * Active provider counts broken down by business category for the
     * "Local Providers" section.
     *
     * Uses the same `openForRecipients` visibility scope as `localProviders()` and
     * `ProviderMenuController` so counts reflect what recipients can actually see.
     * `whereJsonContains` works because `business_category` is stored as a JSON
     * array in `provider_profiles`.  A provider tagged with multiple categories
     * is counted once per matching category.
     *
     * @return array{grocery: int, catering: int, bakery: int, restaurant: int}
     */
    private function providerCategoryCounts(): array
    {
        $categories = ['grocery', 'catering', 'bakery', 'restaurant'];
        $counts = [];

        foreach ($categories as $cat) {
            $counts[$cat] = $this->visibleProviderQuery()
                ->whereHas('providerProfile', fn ($q) => $q->whereJsonContains('business_category', $cat))
                ->count();
        }

        return $counts;
    }

    private function visibleProviderQuery()
    {
        $query = User::query()
            ->openForRecipients()
            ->whereHas('providerProfile');

        if (Role::where('name', 'provider')->where('guard_name', 'web')->exists()) {
            return $query->role('provider');
        }

        return $query->where('membership_type', User::MEMBERSHIP_PROVIDER);
    }

    // Hero live feed (cycling ticker)

    /**
     * Build up to 5 privacy-safe live-feed entries from the most recently
     * funded requests (REDEEMABLE or FULFILLED).  Falls back to the static
     * translation copy when no such records exist yet (e.g. development /
     * staging with an empty database).
     *
     * @return list<array{row1: string, row2: string}>
     */
    private function liveFeedItems(int $limit = 5): array
    {
        $fulfilled = Request::with(['provider.providerProfile'])
            ->whereIn('id', function ($q) {
                $q->select('request_id')
                    ->from('fund_transactions')
                    ->whereIn('source', self::DELIVERED_SOURCES)
                    ->where('direction', FundTransaction::DIRECTION_IN)
                    ->whereNotNull('request_id');
            })
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        if ($fulfilled->isEmpty()) {
            // Static copy from the translation file keeps the feed alive during
            // early deployment when no real redemptions exist yet.
            return (array) __('welcome.feed');
        }

        return $fulfilled->map(function (Request $req): array {
            $profile = $req->provider?->providerProfile;
            $city = $profile?->city ?? (string) __('welcome.feed_live.city_fallback');
            $category = $profile?->business_category; // cast to array by the model
            $type = $this->categoryLabel(is_array($category) ? $category : null);

            return [
                'row1' => (string) __('welcome.feed_live.template', [
                    'type' => $type,
                    'city' => $city,
                ]),
                'row2' => $this->buildFeedRow2($req, $city),
            ];
        })->all();
    }

    /**
     * Build the second row of a hero feed entry:
     *   "NBL-XXXX-YY · {city} · {relative time}"
     */
    private function buildFeedRow2(Request $req, string $city): string
    {
        $hash = sha1('nubl-feed-'.$req->id);
        $ref = 'NBL-'.strtoupper(substr($hash, 0, 4)).'-'.strtoupper(substr($hash, 4, 2));
        $when = $this->humanDiff($req->updated_at ?? $req->created_at ?? now());

        return "{$ref} · {$city} · {$when}";
    }

    // Trust / Transparency section ledger

    /**
     * Returns up to 5 privacy-safe trust-ledger rows from the last 24 hours.
     * Falls back to `staticLedgerPreview()` when no QR-redeemed requests exist
     * in that window; the `is_live` flag tells the view which
     * heading/footer to use.
     *
     * @return array{is_live: bool, rows: list<array{desc: string, meta: string, amount: int}>, shown: int, total: int}
     */
    private function trustLedgerEntries(): array
    {
        $cutoff = now()->subHours(24);

        $redeemedIds = FundTransaction::whereIn('source', self::DELIVERED_SOURCES)
            ->where('direction', FundTransaction::DIRECTION_IN)
            ->whereNotNull('request_id')
            ->where('created_at', '>=', $cutoff)
            ->pluck('request_id');

        $recent = Request::with(['provider.providerProfile'])
            ->whereIn('id', $redeemedIds)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $total = $redeemedIds->count();

        if ($recent->isEmpty()) {
            return $this->staticLedgerPreview();
        }

        $rows = $recent->map(function (Request $req): array {
            $profile = $req->provider?->providerProfile;
            $city = $profile?->city ?? (string) __('welcome.feed_live.city_fallback');
            $typeLabel = $this->categoryLabel(
                is_array($profile?->business_category) ? $profile->business_category : null
            );

            $hash = sha1('nubl-ledger-'.$req->id);
            $ref = 'NBL-'.strtoupper(substr($hash, 0, 4)).'-'.strtoupper(substr($hash, 4, 2));
            $time = ($req->updated_at ?? $req->created_at ?? now())->format('H:i');

            return [
                'desc' => $typeLabel.' · '.$city,
                'meta' => $time.' · '.$ref.' · '.$city,
                'amount' => (int) $req->reserved_amount,
            ];
        })->all();

        return [
            'is_live' => true,
            'rows' => $rows,
            'shown' => count($rows),
            'total' => $total,
        ];
    }

    /**
     * Compute the two trust-badge percentages shown in the transparency section.
     *
     * - badge1 ("of funds reach recipients directly"): REDEEMABLE + FULFILLED
     *   reserved_amount as a share of all DONATION-IN fund transactions.
     * - badge2 ("currently held in the system"): 100 − badge1.
     *
     * Returns null when no donation income has been recorded yet so the view
     * can omit the badges entirely rather than show 0 % / 100 %.
     *
     * @return array{delivered: float, held: float}|null
     */
    private function trustBadges(): ?array
    {
        $totalDonated = (float) FundTransaction::where('source', FundTransaction::SOURCE_DONATION)
            ->where('direction', FundTransaction::DIRECTION_IN)
            ->sum('amount');

        if ($totalDonated <= 0) {
            return null;
        }

        $totalFulfilled = (float) FundTransaction::whereIn('source', self::DELIVERED_SOURCES)
            ->where('direction', FundTransaction::DIRECTION_IN)
            ->sum('amount');
        $deliveredPct = round(min($totalFulfilled / $totalDonated * 100, 100), 1);
        $heldPct = round(100 - $deliveredPct, 1);

        return [
            'delivered' => $deliveredPct,
            'held' => $heldPct,
        ];
    }

    /**
     * Static preview rows shown in the trust ledger when the database has no
     * fulfilled requests in the last 24 hours.
     * Amounts are illustrative sample figures — the view labels this clearly.
     *
     * @return array{is_live: bool, rows: list<array{desc: string, meta: string, amount: int}>, shown: int, total: int}
     */
    private function staticLedgerPreview(): array
    {
        return [
            'is_live' => false,
            'rows' => [
                ['desc' => (string) __('welcome.trust.ledger_1'), 'meta' => '', 'amount' => 158],
                ['desc' => (string) __('welcome.trust.ledger_2'), 'meta' => '', 'amount' => 68],
                ['desc' => (string) __('welcome.trust.ledger_3'), 'meta' => '', 'amount' => 105],
                ['desc' => (string) __('welcome.trust.ledger_4'), 'meta' => '', 'amount' => 132],
                ['desc' => (string) __('welcome.trust.ledger_5'), 'meta' => '', 'amount' => 172],
            ],
            'shown' => 0,
            'total' => 0,
        ];
    }

    // Shared helpers

    /**
     * Resolve a provider's business_category array to a `feed_live.*` translation
     * key suffix (e.g. 'type_supermarket').  Returns 'type_general' as fallback.
     *
     * @param  array<int|string, string>|null  $categories
     */
    private function resolveCategoryKey(?array $categories): string
    {
        $map = [
            'supermarket' => 'type_supermarket',
            'grocery' => 'type_grocery',
            'bakery' => 'type_bakery',
            'restaurant' => 'type_restaurant',
        ];

        foreach ((array) $categories as $cat) {
            $key = strtolower((string) $cat);
            if (isset($map[$key])) {
                return $map[$key];
            }
        }

        return 'type_general';
    }

    /**
     * Translate a provider's business_category array to a human-readable label.
     * Never exposes the provider name — only a generic category description.
     *
     * @param  array<int|string, string>|null  $categories
     */
    private function categoryLabel(?array $categories): string
    {
        return (string) __('welcome.feed_live.'.$this->resolveCategoryKey($categories));
    }

    /**
     * Compact human-readable relative time string for feed entries.
     */
    private function humanDiff(Carbon $dt): string
    {
        $minutes = (int) $dt->diffInMinutes(now());

        if ($minutes < 1) {
            return (string) __('welcome.feed_live.just_now');
        }

        if ($minutes < 60) {
            return (string) __('welcome.feed_live.min_ago', ['n' => $minutes]);
        }

        $hours = (int) $dt->diffInHours(now());
        if ($hours < 24) {
            return (string) __('welcome.feed_live.hr_ago', ['n' => $hours]);
        }

        $days = (int) $dt->diffInDays(now());

        return (string) __('welcome.feed_live.day_ago', ['n' => $days]);
    }
}
