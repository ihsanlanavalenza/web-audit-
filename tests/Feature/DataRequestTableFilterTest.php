<?php

namespace Tests\Feature;

use App\Livewire\DataRequestTable;
use App\Models\Client;
use App\Models\DataRequest;
use App\Models\KapProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataRequestTableFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeAuditorContext(): array
    {
        $auditor = User::factory()->create([
            'role' => 'auditor',
            'email' => 'filter-auditor@example.com',
        ]);

        $kap = KapProfile::create([
            'user_id' => $auditor->id,
            'nama_kap' => 'KAP Filter',
            'nama_pic' => 'PIC Filter',
            'alamat' => 'Jl. Filter 1',
        ]);

        $client = Client::create([
            'kap_id' => $kap->id,
            'nama_client' => 'PT Client Filter',
            'nama_pic' => 'PIC Client',
            'no_contact' => '0811111111',
            'alamat' => 'Jl. Client 1',
            'tahun_audit' => now()->toDateString(),
        ]);

        return [$auditor, $kap, $client];
    }

    public function test_can_filter_by_status(): void
    {
        [$auditor, $kap, $client] = $this->makeAuditorContext();

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 1,
            'section_code' => 'A',
            'section_no' => '1',
            'account_process' => 'ACC_PENDING_ONLY',
            'status' => DataRequest::STATUS_PENDING,
        ]);

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 2,
            'section_code' => 'B',
            'section_no' => '1',
            'account_process' => 'ACC_RECEIVED_ONLY',
            'status' => DataRequest::STATUS_RECEIVED,
        ]);

        Livewire::actingAs($auditor)
            ->test(DataRequestTable::class, ['clientId' => $client->id])
            ->set('filterStatuses', [DataRequest::STATUS_PENDING])
            ->assertSee('ACC_PENDING_ONLY')
            ->assertDontSee('ACC_RECEIVED_ONLY');
    }

    public function test_can_filter_by_expected_received_date_range(): void
    {
        [$auditor, $kap, $client] = $this->makeAuditorContext();

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 1,
            'section_code' => 'A',
            'section_no' => '1',
            'account_process' => 'ACC_EXPECTED_OLD',
            'expected_received' => '2026-04-01',
            'status' => DataRequest::STATUS_PENDING,
        ]);

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 2,
            'section_code' => 'B',
            'section_no' => '1',
            'account_process' => 'ACC_EXPECTED_TARGET',
            'expected_received' => '2026-04-15',
            'status' => DataRequest::STATUS_PENDING,
        ]);

        Livewire::actingAs($auditor)
            ->test(DataRequestTable::class, ['clientId' => $client->id])
            ->set('filterExpectedReceivedFrom', '2026-04-10')
            ->set('filterExpectedReceivedTo', '2026-04-20')
            ->assertSee('ACC_EXPECTED_TARGET')
            ->assertDontSee('ACC_EXPECTED_OLD');
    }

    public function test_can_filter_by_input_file_state_uploaded(): void
    {
        [$auditor, $kap, $client] = $this->makeAuditorContext();

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 1,
            'section_code' => 'A',
            'section_no' => '1',
            'account_process' => 'ACC_FILE_EMPTY',
            'input_file' => null,
            'status' => DataRequest::STATUS_PENDING,
        ]);

        DataRequest::create([
            'client_id' => $client->id,
            'kap_id' => $kap->id,
            'no' => 2,
            'section_code' => 'B',
            'section_no' => '1',
            'account_process' => 'ACC_FILE_UPLOADED',
            'input_file' => [
                [
                    'version' => 1,
                    'files' => ['uploads/filter/doc.pdf'],
                    'uploaded_at' => now()->format('Y-m-d H:i:s'),
                    'uploaded_by' => 'Seeder',
                ],
            ],
            'status' => DataRequest::STATUS_ON_REVIEW,
        ]);

        Livewire::actingAs($auditor)
            ->test(DataRequestTable::class, ['clientId' => $client->id])
            ->set('filterInputFileState', 'uploaded')
            ->assertSee('ACC_FILE_UPLOADED')
            ->assertDontSee('ACC_FILE_EMPTY');
    }
}
