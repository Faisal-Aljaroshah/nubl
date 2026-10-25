<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PendingAllocation;
use App\Models\ProviderMenuItem;
use App\Models\Request;
use App\Models\RequestItem;
use App\Models\RequestPaymentLink;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (Request::where('invoice_id', 'like', 'DEMO-%')->exists()) {
            $this->command->info('⏭ Demo requests already seeded.');

            return;
        }

        $recipients = User::where('membership_type', User::MEMBERSHIP_RECIPIENT)
            ->where('status', User::STATUS_ACTIVE)
            ->where('email', 'like', 'demo-recipient-%')
            ->pluck('id')
            ->toArray();

        if (empty($recipients)) {
            $recipients = User::where('membership_type', User::MEMBERSHIP_RECIPIENT)
                ->where('status', User::STATUS_ACTIVE)
                ->where('email', '!=', 'recipient@nubl.com')
                ->pluck('id')
                ->toArray();
        }

        $providers = User::where('membership_type', User::MEMBERSHIP_PROVIDER)
            ->where('status', User::STATUS_ACTIVE)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($recipients) || empty($providers)) {
            $this->command->warn('⚠ No active recipients or providers found. Skipping DemoRequestSeeder.');

            return;
        }

        // Pre-load menu items grouped by provider
        $menuItemsByProvider = [];
        foreach ($providers as $pid) {
            $items = ProviderMenuItem::where('provider_id', $pid)
                ->where('is_active', true)
                ->get();
            if ($items->isNotEmpty()) {
                $menuItemsByProvider[$pid] = $items;
            }
        }

        // Filter to providers that actually have menu items
        $providers = array_keys($menuItemsByProvider);
        if (empty($providers)) {
            $this->command->warn('⚠ No providers with active menu items. Skipping.');

            return;
        }

        // Build request distribution: ~100 requests across 6 statuses and 8 weeks
        $distribution = $this->buildDistribution();

        // Grab SUCCEEDED payment IDs for request_payment_links (FIFO simulation)
        $succeededPaymentIds = Payment::where('status', Payment::STATUS_SUCCEEDED)
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();
        $paymentCursor = 0;

        $requestCount = 0;
        $itemCount = 0;
        $linkCount = 0;

        foreach ($distribution as $spec) {
            $recipientId = $recipients[array_rand($recipients)];
            $providerId = $providers[array_rand($providers)];
            $items = $menuItemsByProvider[$providerId];
            $weeksAgo = $spec['weeks_ago'];

            $createdAt = now()->subWeeks($weeksAgo)->subDays(rand(0, 6))->subHours(rand(6, 22));

            // Pick 1–3 random menu items for this request
            $pickedItems = $items->random(min(rand(1, 3), $items->count()));
            $reservedAmount = '0.00';
            $requestItemsData = [];

            foreach ($pickedItems as $menuItem) {
                $qty = rand(1, (int) ($menuItem->max_per_request ?? 3));
                $price = $menuItem->price;
                $line = bcmul((string) $price, (string) $qty, 2);
                $reservedAmount = bcadd($reservedAmount, $line, 2);

                $requestItemsData[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $qty,
                    'price_snapshot' => $price,
                ];
            }

            // Determine funding_source and rejection fields
            $fundingSource = null;
            $rejectionReasonCode = null;
            $rejectionReasonNote = null;

            switch ($spec['status']) {
                case 'APPROVED':
                    $fundingSource = 'PROVIDER_ADOPTION';
                    break;
                case 'REDEEMABLE':
                case 'FULFILLED':
                    $fundingSource = 'CITY_FUND';
                    break;
                case 'REJECTED':
                    $rejectionReasonCode = ['out_of_stock', 'outside_service_area', 'duplicate', 'policy_violation'][array_rand(['out_of_stock', 'outside_service_area', 'duplicate', 'policy_violation'])];
                    $rejectionReasonNote = [
                        'out_of_stock' => 'المنتجات المطلوبة غير متوفرة حالياً',
                        'outside_service_area' => 'العنوان خارج نطاق التوصيل',
                        'duplicate' => 'طلب مكرر',
                        'policy_violation' => 'مخالفة سياسة الاستخدام',
                    ][$rejectionReasonCode];
                    break;
            }

            // Create the request
            $request = Request::create([
                'recipient_id' => $recipientId,
                'provider_id' => $providerId,
                'reserved_amount' => $reservedAmount,
                'funding_source' => $fundingSource,
                'status' => $spec['status'],
                'rejection_reason_code' => $rejectionReasonCode,
                'rejection_reason_note' => $rejectionReasonNote,
                'is_flagged' => rand(1, 20) === 1, // 5% flagged
                'invoice_id' => 'DEMO-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $requestCount++;

            // Create request items
            foreach ($requestItemsData as $ri) {
                RequestItem::create([
                    'request_id' => $request->id,
                    'menu_item_id' => $ri['menu_item_id'],
                    'quantity' => $ri['quantity'],
                    'price_snapshot' => $ri['price_snapshot'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $itemCount++;
            }

            // Create request_payment_links for CITY_FUND funded requests (REDEEMABLE, FULFILLED)
            if (in_array($spec['status'], ['REDEEMABLE', 'FULFILLED']) && ! empty($succeededPaymentIds)) {
                $paymentId = $succeededPaymentIds[$paymentCursor % count($succeededPaymentIds)];
                $paymentCursor++;

                RequestPaymentLink::create([
                    'payment_id' => $paymentId,
                    'request_id' => $request->id,
                    'amount' => $reservedAmount,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $linkCount++;
            }
        }

        // Seed 3 pending_allocations for demo
        $this->seedPendingAllocations($providers, $recipients, $menuItemsByProvider);

        $this->command->info("✓ Seeded {$requestCount} demo requests, {$itemCount} request items, {$linkCount} payment links");
    }

    private function buildDistribution(): array
    {
        $rows = [];

        // FULFILLED: 45 (spread across weeks 1-7, none in week 0)
        for ($i = 0; $i < 45; $i++) {
            $rows[] = ['status' => 'FULFILLED', 'weeks_ago' => rand(1, 7)];
        }

        // REQUESTED: 15 (recent: weeks 0-1)
        for ($i = 0; $i < 15; $i++) {
            $rows[] = ['status' => 'REQUESTED', 'weeks_ago' => rand(0, 1)];
        }

        // REDEEMABLE: 15 (recent: weeks 0-2)
        for ($i = 0; $i < 15; $i++) {
            $rows[] = ['status' => 'REDEEMABLE', 'weeks_ago' => rand(0, 2)];
        }

        // APPROVED: 10 (weeks 0-3)
        for ($i = 0; $i < 10; $i++) {
            $rows[] = ['status' => 'APPROVED', 'weeks_ago' => rand(0, 3)];
        }

        // REJECTED: 10 (spread across all weeks)
        for ($i = 0; $i < 10; $i++) {
            $rows[] = ['status' => 'REJECTED', 'weeks_ago' => rand(0, 7)];
        }

        // CANCELLED: 5 (spread)
        for ($i = 0; $i < 5; $i++) {
            $rows[] = ['status' => 'CANCELLED', 'weeks_ago' => rand(0, 5)];
        }

        shuffle($rows);

        return $rows;
    }

    private function seedPendingAllocations(array $providers, array $recipients, array $menuItemsByProvider): void
    {
        // Create 3 "paused" allocation records
        for ($i = 0; $i < 3; $i++) {
            $providerId = $providers[array_rand($providers)];
            $recipientId = $recipients[array_rand($recipients)];
            $items = $menuItemsByProvider[$providerId];
            $menuItem = $items->random();

            $amount = bcmul((string) $menuItem->price, (string) rand(1, 2), 2);

            // Create a REQUESTED request for this pending allocation
            $request = Request::create([
                'recipient_id' => $recipientId,
                'provider_id' => $providerId,
                'reserved_amount' => $amount,
                'status' => 'REQUESTED',
                'invoice_id' => 'DEMO-PA-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                'created_at' => now()->subHours(rand(1, 12)),
                'updated_at' => now()->subHours(rand(1, 12)),
            ]);

            RequestItem::create([
                'request_id' => $request->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => rand(1, 2),
                'price_snapshot' => $menuItem->price,
            ]);

            PendingAllocation::create([
                'request_id' => $request->id,
                'provider_id' => $providerId,
                'amount' => $amount,
                'paused_by' => $i < 2 ? 'global' : 'provider',
            ]);
        }

        $this->command->info('✓ Seeded 3 pending allocations');
    }
}
