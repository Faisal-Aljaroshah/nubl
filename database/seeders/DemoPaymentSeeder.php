<?php

namespace Database\Seeders;

use App\Models\Ewallet;
use App\Models\FundTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Reattribute any bootstrap/fixture payments (e.g. from EwalletSeeder) to demo donors
        // so normal fixture accounts like 'donor-seed@nubl.com' never appear on the public leaderboard.
        $demoDonor = User::where('membership_type', User::MEMBERSHIP_DONOR)
            ->where('status', User::STATUS_ACTIVE)
            ->where('email', 'like', 'demo-donor-%')
            ->first();

        if ($demoDonor) {
            $fixtureDonorIds = User::whereIn('email', ['donor-seed@nubl.com', 'donor@nubl.com'])->pluck('id');
            if ($fixtureDonorIds->isNotEmpty()) {
                Payment::whereIn('sponsor_id', $fixtureDonorIds)->update(['sponsor_id' => $demoDonor->id]);
                FundTransaction::whereIn('sponsor_id', $fixtureDonorIds)->update(['sponsor_id' => $demoDonor->id]);
            }
        }

        if (Payment::where('external_payment_id', 'like', 'DEMO-%')->exists()) {
            $this->command->info('⏭ Demo payments already seeded.');

            return;
        }

        // Only pick from demo donors so fixture/test accounts are never used in demo payments
        $donors = User::where('membership_type', User::MEMBERSHIP_DONOR)
            ->where('status', User::STATUS_ACTIVE)
            ->where('email', 'like', 'demo-donor-%')
            ->pluck('id')
            ->toArray();

        if (empty($donors)) {
            $donors = User::where('membership_type', User::MEMBERSHIP_DONOR)
                ->where('status', User::STATUS_ACTIVE)
                ->whereNotIn('email', ['donor-seed@nubl.com', 'donor@nubl.com'])
                ->pluck('id')
                ->toArray();
        }

        if (empty($donors)) {
            $this->command->warn('⚠ No active demo donors found. Skipping DemoPaymentSeeder.');

            return;
        }

        $systemWallet = Ewallet::where('owner_type', 'SYSTEM')->first();
        if (! $systemWallet) {
            $this->command->warn('⚠ System wallet not found. Skipping DemoPaymentSeeder.');

            return;
        }

        $statusDistribution = $this->buildStatusDistribution();
        $created = 0;
        $succeededCount = 0;

        foreach ($statusDistribution as $row) {
            $donorId = $donors[array_rand($donors)];
            $weeksAgo = $row['weeks_ago'];
            $createdAt = now()->subWeeks($weeksAgo)->subDays(rand(0, 6))->subHours(rand(0, 23));

            $amount = $this->randomAmount($row['status']);

            $payment = Payment::create([
                'sponsor_id' => $donorId,
                'gateway' => Payment::GATEWAY_MYFATOORAH,
                'external_payment_id' => 'DEMO-'.strtoupper(substr(md5(uniqid()), 0, 10)),
                'status' => $row['status'],
                'amount' => $amount,
                'notes' => null,
                'idempotency_key' => Str::uuid()->toString(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Create fund_transaction for SUCCEEDED donations → system wallet
            if ($row['status'] === Payment::STATUS_SUCCEEDED) {
                FundTransaction::create([
                    'wallet_id' => $systemWallet->id,
                    'sponsor_id' => $donorId,
                    'source' => FundTransaction::SOURCE_DONATION,
                    'amount' => $amount,
                    'direction' => FundTransaction::DIRECTION_IN,
                    'payment_id' => $payment->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $succeededCount++;
            }

            $created++;
        }

        $this->command->info("✓ Seeded {$created} demo payments ({$succeededCount} SUCCEEDED with donation fund_transactions)");
    }

    private function buildStatusDistribution(): array
    {
        $rows = [];

        // 35 SUCCEEDED spread over 8 weeks
        for ($i = 0; $i < 35; $i++) {
            $rows[] = ['status' => Payment::STATUS_SUCCEEDED, 'weeks_ago' => $i % 8];
        }

        // 5 FAILED
        for ($i = 0; $i < 5; $i++) {
            $rows[] = ['status' => Payment::STATUS_FAILED, 'weeks_ago' => rand(0, 6)];
        }

        // 5 PENDING
        for ($i = 0; $i < 5; $i++) {
            $rows[] = ['status' => Payment::STATUS_PENDING, 'weeks_ago' => rand(0, 2)];
        }

        // 3 INITIATED
        for ($i = 0; $i < 3; $i++) {
            $rows[] = ['status' => Payment::STATUS_INITIATED, 'weeks_ago' => 0];
        }

        // 2 PROCESSING
        for ($i = 0; $i < 2; $i++) {
            $rows[] = ['status' => Payment::STATUS_PROCESSING, 'weeks_ago' => 0];
        }

        shuffle($rows);

        return $rows;
    }

    private function randomAmount(string $status): string
    {
        // Realistic donation amounts in SAR
        $amounts = [25, 50, 75, 100, 150, 200, 250, 300, 500, 750, 1000, 1500, 2000, 5000];

        if ($status === Payment::STATUS_FAILED) {
            // Failed payments tend to be smaller test amounts
            return (string) $amounts[array_rand(array_slice($amounts, 0, 5))];
        }

        return (string) $amounts[array_rand($amounts)];
    }
}
