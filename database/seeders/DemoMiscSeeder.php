<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\SummaryReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoMiscSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNotifications();
        $this->seedSummaryReports();
        $this->seedActivityLog();
    }

    // Notifications

    private function seedNotifications(): void
    {
        $existing = \DB::table('notifications')->where('type', 'like', '%DonationReceipt%')->count();
        if ($existing > 5) {
            $this->command->info('⏭ Demo notifications already seeded.');

            return;
        }

        $donors = User::where('membership_type', User::MEMBERSHIP_DONOR)
            ->where('status', User::STATUS_ACTIVE)
            ->where('email', 'like', 'demo-donor-%')
            ->get();
        if ($donors->isEmpty()) {
            $donors = User::where('membership_type', User::MEMBERSHIP_DONOR)->where('status', User::STATUS_ACTIVE)->get();
        }

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        $recipients = User::where('membership_type', User::MEMBERSHIP_RECIPIENT)
            ->where('status', User::STATUS_ACTIVE)
            ->where('email', 'like', 'demo-recipient-%')
            ->limit(5)
            ->get();
        if ($recipients->isEmpty()) {
            $recipients = User::where('membership_type', User::MEMBERSHIP_RECIPIENT)->where('status', User::STATUS_ACTIVE)->limit(5)->get();
        }
        $providers = User::where('membership_type', User::MEMBERSHIP_PROVIDER)->where('status', User::STATUS_ACTIVE)->limit(5)->get();

        $count = 0;

        // Donation receipts for donors (some read, some unread)
        foreach ($donors->take(8) as $i => $donor) {
            $createdAt = now()->subWeeks(rand(0, 6))->subDays(rand(0, 6));
            \DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\DonationReceiptNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $donor->id,
                'data' => json_encode([
                    'title' => 'إيصال تبرع',
                    'message' => 'شكراً لتبرعك السخي بمبلغ '.(rand(5, 50) * 10).' ريال',
                    'type' => 'donation_receipt',
                ]),
                'read_at' => $i % 3 === 0 ? null : $createdAt->copy()->addHours(rand(1, 48)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $count++;
        }

        // Account approval pending for admins
        foreach ($admins as $admin) {
            for ($j = 0; $j < 2; $j++) {
                $createdAt = now()->subDays(rand(0, 5));
                \DB::table('notifications')->insert([
                    'id' => Str::uuid()->toString(),
                    'type' => 'App\\Notifications\\AccountApprovalPendingNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $admin->id,
                    'data' => json_encode([
                        'title' => 'حساب جديد بانتظار المراجعة',
                        'message' => 'تم تسجيل مستخدم جديد بانتظار الموافقة',
                        'type' => 'account_approval_pending',
                    ]),
                    'read_at' => $j === 0 ? null : $createdAt->copy()->addHours(2),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $count++;
            }
        }

        // Request status updates for recipients
        foreach ($recipients->take(4) as $i => $recipient) {
            $createdAt = now()->subDays(rand(0, 14));
            \DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\RequestStatusChangedNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $recipient->id,
                'data' => json_encode([
                    'title' => 'تحديث حالة الطلب',
                    'message' => 'تم تحديث حالة طلبك',
                    'type' => 'request_status_changed',
                    'status' => ['APPROVED', 'REDEEMABLE', 'FULFILLED', 'REJECTED'][$i],
                ]),
                'read_at' => $i % 2 === 0 ? null : $createdAt->copy()->addHours(rand(1, 12)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $count++;
        }

        // New request notifications for providers
        foreach ($providers->take(3) as $i => $provider) {
            $createdAt = now()->subDays(rand(0, 7));
            \DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\ProviderNewRequestNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $provider->id,
                'data' => json_encode([
                    'title' => 'طلب جديد',
                    'message' => 'لديك طلب جديد من مستفيد',
                    'type' => 'provider_new_request',
                ]),
                'read_at' => $i === 0 ? null : $createdAt->copy()->addHours(rand(1, 6)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $count++;
        }

        // Payout transferred for providers
        foreach ($providers->take(2) as $i => $provider) {
            $createdAt = now()->subDays(rand(1, 14));
            \DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\ProviderPayoutTransferredNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $provider->id,
                'data' => json_encode([
                    'title' => 'تم التحويل',
                    'message' => 'تم تحويل مستحقاتك المالية إلى حسابك البنكي',
                    'type' => 'payout_transferred',
                ]),
                'read_at' => $createdAt->copy()->addHours(rand(1, 24)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $count++;
        }

        $this->command->info("✓ Seeded {$count} demo notifications");
    }

    // Summary Reports

    private function seedSummaryReports(): void
    {
        if (SummaryReport::exists()) {
            $this->command->info('⏭ Summary reports already seeded.');

            return;
        }

        $reports = [];

        // 4 weekly reports
        for ($w = 1; $w <= 4; $w++) {
            $from = now()->subWeeks($w)->startOfWeek(\Carbon\Carbon::SUNDAY);
            $to = $from->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

            $succAmt = rand(3000, 12000) + rand(0, 99) / 100;
            $failAmt = rand(200, 1500) + rand(0, 99) / 100;
            $ledgerIn = $succAmt * 0.95;
            $ledgerOut = rand(1000, 5000) + rand(0, 99) / 100;

            $reports[] = [
                'type' => SummaryReport::TYPE_WEEKLY,
                'period_from' => $from->toDateString(),
                'period_to' => $to->toDateString(),
                'payload' => $this->buildPayload($from, $to, $succAmt, $failAmt, $ledgerIn, $ledgerOut),
                'generated_at' => $to->copy()->addDay()->setHour(2)->setMinute(59),
            ];
        }

        // 2 monthly reports
        for ($m = 1; $m <= 2; $m++) {
            $from = now()->subMonths($m)->startOfMonth();
            $to = $from->copy()->endOfMonth();

            $succAmt = rand(15000, 50000) + rand(0, 99) / 100;
            $failAmt = rand(800, 5000) + rand(0, 99) / 100;
            $ledgerIn = $succAmt * 0.95;
            $ledgerOut = rand(5000, 20000) + rand(0, 99) / 100;

            $reports[] = [
                'type' => SummaryReport::TYPE_MONTHLY,
                'period_from' => $from->toDateString(),
                'period_to' => $to->toDateString(),
                'payload' => $this->buildPayload($from, $to, $succAmt, $failAmt, $ledgerIn, $ledgerOut),
                'generated_at' => $to->copy()->addDays(2)->setHour(2)->setMinute(59),
            ];
        }

        foreach ($reports as $r) {
            SummaryReport::create([
                'type' => $r['type'],
                'period_from' => $r['period_from'],
                'period_to' => $r['period_to'],
                'payload' => $r['payload'],
                'generated_at' => $r['generated_at'],
            ]);
        }

        $this->command->info('✓ Seeded '.count($reports).' summary reports (4 weekly + 2 monthly)');
    }

    // Activity Log (20-30 entries)

    private function seedActivityLog(): void
    {
        if (Activity::where('properties->demo_seed', true)->exists()) {
            $this->command->info('⏭ Demo activity log already seeded.');

            return;
        }

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->pluck('id')->toArray();
        $providers = User::where('membership_type', User::MEMBERSHIP_PROVIDER)->where('status', User::STATUS_ACTIVE)->pluck('id')->toArray();
        $allUsers = array_merge($admins, $providers);

        if (empty($allUsers)) {
            $this->command->warn('⚠ No users for activity log. Skipping.');

            return;
        }

        $entries = [
            ['log' => 'account_approval', 'event' => 'approved',                    'desc' => 'Admin approved recipient account'],
            ['log' => 'account_approval', 'event' => 'rejected',                    'desc' => 'Admin rejected recipient account'],
            ['log' => 'account_approval', 'event' => 'approved',                    'desc' => 'Admin approved provider account'],
            ['log' => 'request',          'event' => 'provider_credited_as_donor',   'desc' => 'Provider adopted request'],
            ['log' => 'request',          'event' => 'approved_city_fund',           'desc' => 'Provider approved request with city fund'],
            ['log' => 'request',          'event' => 'rejected_by_provider',         'desc' => 'Provider rejected request'],
            ['log' => 'request',          'event' => 'fulfillment_proof_uploaded',   'desc' => 'Provider uploaded fulfillment proof'],
            ['log' => 'request',          'event' => 'cancelled_by_recipient',       'desc' => 'Recipient cancelled request'],
            ['log' => 'notification',     'event' => 'sent',                         'desc' => 'Notification sent to user'],
            ['log' => 'notification',     'event' => 'sent',                         'desc' => 'Donation receipt notification sent'],
            ['log' => 'fund_transactions', 'event' => 'created',                      'desc' => 'Fund transaction created for donation'],
            ['log' => 'fund_transactions', 'event' => 'created',                      'desc' => 'Fund transaction created for redemption'],
            ['log' => 'payment',          'event' => 'succeeded',                    'desc' => 'Payment succeeded via MyFatoorah'],
            ['log' => 'payment',          'event' => 'failed',                       'desc' => 'Payment failed'],
            ['log' => 'provider_payout',  'event' => 'generated',                   'desc' => 'Provider payout generated'],
            ['log' => 'provider_payout',  'event' => 'confirmed',                   'desc' => 'Admin confirmed provider payout transfer'],
            ['log' => 'provider_payout',  'event' => 'rejected',                    'desc' => 'Admin rejected provider payout'],
            ['log' => 'role',             'event' => 'permission_synced',            'desc' => 'Role permissions updated'],
            ['log' => 'user',             'event' => 'deactivated',                  'desc' => 'Admin deactivated user account'],
            ['log' => 'user',             'event' => 'activated',                    'desc' => 'Admin activated user account'],
            ['log' => 'menu_item',        'event' => 'admin_blocked',               'desc' => 'Admin blocked menu item'],
            ['log' => 'menu_item',        'event' => 'admin_unblocked',             'desc' => 'Admin unblocked menu item'],
            ['log' => 'allocation',       'event' => 'engine_paused',               'desc' => 'Allocation engine paused globally'],
            ['log' => 'allocation',       'event' => 'engine_resumed',              'desc' => 'Allocation engine resumed'],
            ['log' => 'qr',              'event' => 'redeemed',                     'desc' => 'QR code redeemed by provider'],
        ];

        // Chain tip to continue from (seeders run under WithoutModelEvents, so the
        // Activity::creating hook does NOT fire — we set previous_hash + sha256_hash
        // manually to keep the hash chain valid and verifiable).
        $previousHash = Activity::orderByDesc('id')->value('sha256_hash') ?? Activity::GENESIS_HASH;

        foreach ($entries as $i => $entry) {
            $causerId = $allUsers[array_rand($allUsers)];
            $createdAt = now()->subWeeks(rand(0, 7))->subDays(rand(0, 6))->subHours(rand(0, 23));

            $activity = new Activity([
                'log_name' => $entry['log'],
                'description' => $entry['desc'],
                'event' => $entry['event'],
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $causerId,
                'subject_type' => null,
                'subject_id' => null,
                'properties' => ['demo_seed' => true, 'index' => $i],
                'batch_uuid' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            // WithoutModelEvents suppresses booted() hook; set chain fields + hash manually.
            $activity->previous_hash = $previousHash;
            $activity->sha256_hash = Activity::computeHashFor($activity);
            $activity->save();

            $previousHash = $activity->sha256_hash;
        }

        $this->command->info('✓ Seeded '.count($entries).' activity log entries');
    }

    // Helpers

    /**
     * Build a payload array matching FinancialService::getRangeSummary() keys.
     */
    private function buildPayload($from, $to, float $succAmt, float $failAmt, float $ledgerIn, float $ledgerOut): array
    {
        $succCnt = rand(8, 25);
        $failCnt = rand(1, 5);
        $pendCnt = rand(0, 3);
        $totalCnt = $succCnt + $failCnt + $pendCnt;

        $reqTotal = rand(15, 50);
        $reqFulfilled = (int) ($reqTotal * 0.45);
        $reqApproved = (int) ($reqTotal * 0.10);
        $reqRedeemable = (int) ($reqTotal * 0.15);
        $reqRejected = rand(1, 5);
        $reqCancelled = rand(0, 3);
        $reqPending = $reqTotal - $reqFulfilled - $reqApproved - $reqRedeemable - $reqRejected - $reqCancelled;
        if ($reqPending < 0) {
            $reqPending = 0;
        }

        $payoutsOut = round($ledgerOut * 0.6, 2);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),

            // payments
            'payments_by_status' => [
                ['status' => 'SUCCEEDED',  'cnt' => $succCnt, 'total' => round($succAmt, 2)],
                ['status' => 'FAILED',     'cnt' => $failCnt, 'total' => round($failAmt, 2)],
                ['status' => 'PENDING',    'cnt' => $pendCnt, 'total' => round(rand(100, 500), 2)],
            ],
            'payments_total_count' => $totalCnt,
            'payments_succeeded_count' => $succCnt,
            'payments_succeeded_amount' => round($succAmt, 2),
            'payments_failed_count' => $failCnt,
            'payments_failed_amount' => round($failAmt, 2),
            'payments_pending_count' => $pendCnt,

            // ledger
            'ledger_entries_count' => rand(20, 80),
            'ledger_in_amount' => round($ledgerIn, 2),
            'ledger_out_amount' => round($ledgerOut, 2),
            'ledger_net_amount' => round($ledgerIn - $ledgerOut, 2),
            'payouts_to_providers' => $payoutsOut,

            // requests
            'requests_total' => $reqTotal,
            'requests_fulfilled' => $reqFulfilled,
            'requests_approved' => $reqApproved,
            'requests_redeemable' => $reqRedeemable,
            'requests_pending' => $reqPending,
            'requests_rejected' => $reqRejected,
            'requests_cancelled' => $reqCancelled,
            'requests_city_fund' => rand(3, 10),
            'requests_adopted' => rand(0, 4),
            'requests_fulfilled_amount' => round($reqFulfilled * rand(30, 80), 2),

            // redemptions
            'redemptions_total' => rand(10, 40),
            'redemptions_redeemed' => rand(5, 30),
            'redemptions_expired' => rand(1, 5),
            'redemptions_pending' => rand(0, 5),

            // participation
            'active_providers_total' => rand(5, 9),
            'providers_with_requests' => rand(3, 7),
            'active_recipients_with_requests' => rand(8, 20),
        ];
    }
}
