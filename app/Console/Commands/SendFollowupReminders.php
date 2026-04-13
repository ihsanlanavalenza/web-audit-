<?php

namespace App\Console\Commands;

use App\Mail\FollowupDataRequestMail;
use App\Models\DataRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFollowupReminders extends Command
{
    protected $signature = 'audit:send-followup';
    protected $description = 'Kirim email reminder ke auditi & auditor untuk data request yang lewat jatuh tempo';

    public function handle(): int
    {
        $this->info('🔍 Mencari data request yang terlambat...');

        // Cari data request yang:
        // 1. expected_received sudah lewat jatuh tempo
        // 2. Belum ada file yang diupload (input_file = null atau '[]')
        // 3. Followup belum dikirim hari ini (agar bisa kirim ulang harian sampai ada upload)
        /** @var \Illuminate\Database\Eloquent\Collection<int, DataRequest> $overdueRequests */
        $overdueRequests = DataRequest::query()->where(function (Builder $q) {
            $q->whereNull('input_file')
                ->orWhere('input_file', '[]')
                ->orWhere('input_file', '')
                ->orWhere('input_file', 'null');
        })
            ->where(function (Builder $q) {
                $q->whereNull('followup_sent_at')
                    ->orWhere('followup_sent_at', '<', now()->startOfDay());
            })
            ->whereNotNull('expected_received')
            ->whereDate('expected_received', '<', now()->startOfDay())
            ->where('status', '!=', DataRequest::STATUS_NOT_APPLICABLE)
            ->with(['client', 'kapProfile.user'])
            ->get();

        if ($overdueRequests->isEmpty()) {
            $this->info('✅ Tidak ada data request yang perlu di-followup.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($overdueRequests as $request) {
            /** @var DataRequest $request */
            $client = $request->client;
            $kap = $request->kapProfile;

            if (!$client || !$kap) {
                $this->warn("⚠️ Data request #{$request->id} missing client/kap, skipping.");
                continue;
            }

            $auditiEmails = Invitation::query()
                ->where('role', 'auditi')
                ->where('client_id', $client->id)
                ->where('kap_id', $kap->id)
                ->whereNotNull('accepted_at')
                ->where(function (Builder $q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->pluck('email');

            $auditorEmails = User::query()
                ->where('role', 'auditor')
                ->whereHas('clients', function (Builder $q) use ($client) {
                    $q->where('clients.id', $client->id);
                })
                ->pluck('email');

            $recipientEmails = $auditiEmails
                ->merge($auditorEmails)
                ->map(fn($email) => strtolower(trim((string) $email)))
                ->filter()
                ->unique()
                ->values();

            if ($recipientEmails->isEmpty()) {
                $this->warn("⚠️ Tidak ada penerima aktif untuk client '{$client->nama_client}', skipping.");
                continue;
            }

            $daysOverdue = max(1, (int) $request->expected_received->startOfDay()->diffInDays(now()->startOfDay()));
            $sentForRequest = 0;

            foreach ($recipientEmails as $recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(
                        new FollowupDataRequestMail(
                            dataRequest: $request,
                            clientName: $client->nama_client,
                            kapName: $kap->nama_kap,
                            daysOverdue: $daysOverdue,
                        )
                    );

                    $sentForRequest++;
                    $sent++;

                    $this->info("📧 Email dikirim ke {$recipientEmail} untuk request #{$request->no} ({$request->section})");
                } catch (\Throwable $e) {
                    $this->error("❌ Gagal kirim ke {$recipientEmail}: {$e->getMessage()}");
                }
            }

            if ($sentForRequest > 0) {
                // Simpan timestamp kirim terakhir agar command tetap idempotent per hari.
                $request->update(['followup_sent_at' => now()]);
            }
        }

        $this->info("✅ Selesai! {$sent} email followup terkirim.");
        return self::SUCCESS;
    }
}
