<?php

namespace App\Console\Commands;

use App\Mail\FollowupDataRequestMail;
use App\Models\DataRequest;
use App\Models\Invitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFollowupReminders extends Command
{
    protected $signature = 'audit:send-followup';
    protected $description = 'Kirim email reminder ke auditi untuk data request yang terlambat 5-6 hari';

    public function handle(): int
    {
        $this->info('🔍 Mencari data request yang terlambat...');

        // Cari data request yang:
        // 1. expected_received sudah lewat 5-6 hari
        // 2. Belum ada file yang diupload (input_file = null)
        // 3. Belum pernah dikirim followup (followup_sent_at = null)
        $overdueRequests = DataRequest::whereNull('input_file')
            ->whereNull('followup_sent_at')
            ->whereNotNull('expected_received')
            ->where('expected_received', '<=', now()->subDays(5))
            ->where('expected_received', '>=', now()->subDays(7)) // Batas atas supaya tidak spam
            ->where('status', '!=', DataRequest::STATUS_NOT_APPLICABLE)
            ->with(['client', 'kapProfile'])
            ->get();

        if ($overdueRequests->isEmpty()) {
            $this->info('✅ Tidak ada data request yang perlu di-followup.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($overdueRequests as $request) {
            $client = $request->client;
            $kap = $request->kapProfile;

            if (!$client || !$kap) {
                $this->warn("⚠️ Data request #{$request->id} missing client/kap, skipping.");
                continue;
            }

            // Cari email auditi dari invitation
            $invitation = Invitation::where('client_id', $client->id)
                ->where('kap_id', $kap->id)
                ->whereNotNull('accepted_at')
                ->first();

            if (!$invitation || !$invitation->email) {
                $this->warn("⚠️ Tidak ada auditi terdaftar untuk client '{$client->nama_client}', skipping.");
                continue;
            }

            $daysOverdue = (int) now()->diffInDays($request->expected_received);

            try {
                Mail::to($invitation->email)->send(
                    new FollowupDataRequestMail(
                        dataRequest: $request,
                        clientName: $client->nama_client,
                        kapName: $kap->nama_kap,
                        daysOverdue: $daysOverdue,
                    )
                );

                // Tandai sudah dikirim
                $request->update(['followup_sent_at' => now()]);
                $sent++;

                $this->info("📧 Email dikirim ke {$invitation->email} untuk request #{$request->no} ({$request->section})");
            } catch (\Exception $e) {
                $this->error("❌ Gagal kirim ke {$invitation->email}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Selesai! {$sent} email followup terkirim.");
        return self::SUCCESS;
    }
}
