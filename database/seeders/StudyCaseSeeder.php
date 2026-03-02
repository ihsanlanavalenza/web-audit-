<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\DataRequest;
use App\Models\Invitation;
use App\Models\KapProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudyCaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. AUDITOR USER + KAP PROFILE ───────────────────────
        $auditorUser = User::updateOrCreate(
            ['email' => 'auditor@webaudit.com'],
            [
                'name' => 'Abdul Aziz Ramdani',
                'password' => Hash::make('auditor123'),
                'role' => 'auditor',
            ]
        );

        $kap = KapProfile::updateOrCreate(
            ['user_id' => $auditorUser->id],
            [
                'nama_kap' => 'KAP Hendry, Ferdy, dan Rekan',
                'nama_pic' => 'Abdul Aziz Ramdani',
                'alamat' => 'Jl. Raya Casablanca, RT.16/RW.5, Menteng Dalam, Tebet, Jakarta Selatan',
            ]
        );

        // Update user kap_id
        $auditorUser->update(['kap_id' => $kap->id]);

        // ── 2. CLIENT (AUDITI) ──────────────────────────────────
        $client = Client::updateOrCreate(
            ['kap_id' => $kap->id, 'nama_client' => 'PT PMUI Tbk'],
            [
                'nama_pic' => 'Bu Lili Solihah',
                'no_contact' => '+6289677674806',
                'alamat' => 'Cirebon',
                'tahun_audit' => '2025-12-31',
            ]
        );

        // ── 3. AUDITI USER + INVITATION ─────────────────────────
        $auditiUser = User::updateOrCreate(
            ['email' => 'auditi@webaudit.com'],
            [
                'name' => 'Bu Lili Solihah',
                'password' => Hash::make('auditi123'),
                'role' => 'auditi',
                'kap_id' => $kap->id,
            ]
        );

        Invitation::updateOrCreate(
            ['email' => 'auditi@webaudit.com', 'kap_id' => $kap->id],
            [
                'client_id' => $client->id,
                'role' => 'auditi',
                'token' => Invitation::generateToken(),
                'accepted_at' => now(),
            ]
        );

        // ── 4. SAMPLE DATA REQUESTS ─────────────────────────────
        $samples = [
            [
                'no' => 1,
                'section_code' => 'A',
                'section_no' => 1,
                'account_process' => 'Cash & Bank',
                'description' => 'Rekening Koran Bank periode Jan-Des 2025',
                'request_date' => '2025-12-15',
                'expected_received' => '2025-12-20',
                'status' => DataRequest::STATUS_RECEIVED,
                'input_file' => null,
                'date_input' => '2025-12-19 14:30:00',
            ],
            [
                'no' => 2,
                'section_code' => 'A',
                'section_no' => 2,
                'account_process' => 'Cash & Bank',
                'description' => 'Buku Besar Kas dan Bank',
                'request_date' => '2025-12-15',
                'expected_received' => '2025-12-22',
                'status' => DataRequest::STATUS_PENDING,
                'input_file' => null,
                'date_input' => null,
            ],
            [
                'no' => 3,
                'section_code' => 'B',
                'section_no' => 1,
                'account_process' => 'Account Receivable',
                'description' => 'Daftar Piutang Usaha per 31 Des 2025',
                'request_date' => '2025-12-16',
                'expected_received' => '2025-12-25',
                'status' => DataRequest::STATUS_ON_REVIEW,
                'input_file' => null,
                'date_input' => null,
            ],
            [
                'no' => 4,
                'section_code' => 'B',
                'section_no' => 2,
                'account_process' => 'Account Receivable',
                'description' => 'Konfirmasi Piutang kepada Debitur',
                'request_date' => '2025-12-16',
                'expected_received' => '2025-12-28',
                'status' => DataRequest::STATUS_PARTIALLY_RECEIVED,
                'input_file' => null,
                'date_input' => null,
            ],
            [
                'no' => 5,
                'section_code' => 'C',
                'section_no' => 1,
                'account_process' => 'Fixed Assets',
                'description' => 'Daftar Aset Tetap dan Penyusutan',
                'request_date' => '2025-12-17',
                'expected_received' => '2025-12-30',
                'status' => DataRequest::STATUS_NOT_APPLICABLE,
                'input_file' => null,
                'date_input' => null,
            ],
        ];

        foreach ($samples as $sample) {
            DataRequest::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'no' => $sample['no'],
                ],
                array_merge($sample, [
                    'client_id' => $client->id,
                    'kap_id' => $kap->id,
                ])
            );
        }
    }
}
