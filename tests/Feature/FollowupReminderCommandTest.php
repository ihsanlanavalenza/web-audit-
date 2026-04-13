<?php

namespace Tests\Feature;

use App\Mail\FollowupDataRequestMail;
use App\Models\Client;
use App\Models\DataRequest;
use App\Models\Invitation;
use App\Models\KapProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FollowupReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_followup_command_sends_daily_to_auditi_and_scoped_auditors_until_upload(): void
    {
        Mail::fake();

        $ownerAuditor = User::factory()->create([
            'role' => 'auditor',
            'email' => 'owner-followup@example.com',
        ]);

        $kap = KapProfile::create([
            'user_id' => $ownerAuditor->id,
            'nama_kap' => 'KAP Followup',
            'nama_pic' => 'PIC Followup',
            'alamat' => 'Jl. Followup 1',
        ]);

        $client = Client::create([
            'kap_id' => $kap->id,
            'nama_client' => 'PT Followup Client',
            'nama_pic' => 'PIC Followup Client',
            'no_contact' => '081234560010',
            'alamat' => 'Jl. Followup Client',
            'tahun_audit' => now()->toDateString(),
        ]);

        $invitedAuditor = User::factory()->create([
            'role' => 'auditor',
            'email' => 'invited-followup@example.com',
            'kap_id' => $kap->id,
        ]);
        $invitedAuditor->clients()->syncWithoutDetaching([$client->id]);

        $auditi = User::factory()->create([
            'role' => 'auditi',
            'email' => 'auditi-followup@example.com',
        ]);

        Invitation::create([
            'kap_id' => $kap->id,
            'client_id' => $client->id,
            'email' => $auditi->email,
            'role' => 'auditi',
            'token' => Invitation::generateToken(),
            'accepted_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
        ]);

        $request = DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 1,
            'section_code' => 'A',
            'section_no' => '1',
            'account_process' => 'Kas',
            'status' => DataRequest::STATUS_PENDING,
            'expected_received' => now()->subDays(2)->toDateString(),
            'input_file' => null,
            'followup_sent_at' => null,
        ]);

        $this->assertSame(0, Artisan::call('audit:send-followup'));

        Mail::assertSent(FollowupDataRequestMail::class, 3);
        Mail::assertSent(FollowupDataRequestMail::class, fn(FollowupDataRequestMail $mail) => $mail->hasTo($auditi->email));
        Mail::assertSent(FollowupDataRequestMail::class, fn(FollowupDataRequestMail $mail) => $mail->hasTo($ownerAuditor->email));
        Mail::assertSent(FollowupDataRequestMail::class, fn(FollowupDataRequestMail $mail) => $mail->hasTo($invitedAuditor->email));

        $request->refresh();
        $this->assertNotNull($request->followup_sent_at);

        // Same day should not resend.
        $this->assertSame(0, Artisan::call('audit:send-followup'));
        Mail::assertSent(FollowupDataRequestMail::class, 3);

        // Next day (simulated by rolling back followup_sent_at) should resend.
        $request->update(['followup_sent_at' => now()->subDay()]);

        $this->assertSame(0, Artisan::call('audit:send-followup'));
        Mail::assertSent(FollowupDataRequestMail::class, 6);
    }
}
