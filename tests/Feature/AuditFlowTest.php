<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\KapProfile;
use App\Models\Client;
use App\Models\DataRequest;
use App\Models\ActivityLog;
use App\Models\Invitation;
use App\Notifications\DataRequestRevisionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;

class AuditFlowTest extends TestCase
{
    use RefreshDatabase;

    private $auditor;
    private $auditi;
    private $kap;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditor = User::factory()->create(['role' => 'auditor']);
        $this->kap = KapProfile::create(['user_id' => $this->auditor->id, 'nama_kap' => 'KAP Test', 'nama_pic' => 'Test PIC', 'alamat' => 'Address']);
        $this->client = Client::create(['kap_id' => $this->kap->id, 'nama_client' => 'PT Test', 'nama_pic' => 'PIC C', 'no_contact' => '1234', 'alamat' => 'Address Test']);

        $this->auditi = User::factory()->create(['role' => 'auditi']);

        Invitation::create([
            'client_id' => $this->client->id,
            'kap_id' => $this->kap->id,
            'email' => $this->auditi->email,
            'role' => 'auditi',
            'invited_by' => $this->auditor->id,
            'token' => 'random_token',
            'accepted_at' => now(),
        ]);
    }

    public function test_activity_log_is_created_on_data_request_update(): void
    {
        $this->actingAs($this->auditor);

        $request = DataRequest::create([
            'client_id' => $this->client->id,
            'kap_id' => $this->kap->id,
            'no' => 1,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => DataRequest::class,
            'model_id' => $request->id,
            'action' => 'created'
        ]);

        $request->update(['status' => 'on_review']);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => DataRequest::class,
            'model_id' => $request->id,
            'action' => 'status_changed'
        ]);
    }

    public function test_data_request_revision_triggers_notification(): void
    {
        Notification::fake();

        $this->actingAs($this->auditor);

        $request = DataRequest::create([
            'client_id' => $this->client->id,
            'kap_id' => $this->kap->id,
            'no' => 1,
            'status' => 'on_review',
            'comment_auditor' => 'Tolong lengkapi file B.',
            'pic_id' => $this->auditi->id,
        ]);

        $component = \Livewire\Livewire::actingAs($this->auditor)
            ->test(\App\Livewire\DataRequestTable::class, ['clientId' => $this->client->id])
            ->call('requestRevision', $request->id);


        Notification::assertSentTo(
            $this->auditi,
            DataRequestRevisionNotification::class
        );

        $request->refresh();
        $this->assertEquals(DataRequest::STATUS_PARTIALLY_RECEIVED, $request->status);
    }
}
