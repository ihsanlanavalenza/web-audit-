<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->index('client_id');
        });

        $timestamp = now();

        // Owner KAP: always gets access to all clients in their KAP.
        $ownerPairs = DB::table('clients')
            ->join('kap_profiles', 'kap_profiles.id', '=', 'clients.kap_id')
            ->whereNotNull('kap_profiles.user_id')
            ->select('kap_profiles.user_id as user_id', 'clients.id as client_id')
            ->get();

        $this->insertAccessRows($ownerPairs, $timestamp);

        // Accepted auditor invitations with explicit client scope.
        $auditorInvitesWithClient = DB::table('invitations')
            ->where('role', 'auditor')
            ->whereNotNull('accepted_at')
            ->whereNotNull('client_id')
            ->select('email', 'client_id')
            ->get();

        foreach ($auditorInvitesWithClient as $invitation) {
            $email = strtolower(trim((string) $invitation->email));
            if ($email === '') {
                continue;
            }

            $userIds = DB::table('users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('role', 'auditor')
                ->pluck('id');

            $pairs = $userIds->map(function ($userId) use ($invitation) {
                return [
                    'user_id' => (int) $userId,
                    'client_id' => (int) $invitation->client_id,
                ];
            });

            $this->insertAccessRows($pairs, $timestamp);
        }

        // Legacy accepted auditor invitations without client scope: grant all KAP clients.
        $legacyAuditorInvites = DB::table('invitations')
            ->where('role', 'auditor')
            ->whereNotNull('accepted_at')
            ->whereNull('client_id')
            ->whereNotNull('kap_id')
            ->select('email', 'kap_id')
            ->distinct()
            ->get();

        foreach ($legacyAuditorInvites as $invitation) {
            $email = strtolower(trim((string) $invitation->email));
            if ($email === '') {
                continue;
            }

            $userIds = DB::table('users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('role', 'auditor')
                ->pluck('id');

            if ($userIds->isEmpty()) {
                continue;
            }

            $clientIds = DB::table('clients')
                ->where('kap_id', (int) $invitation->kap_id)
                ->pluck('id');

            if ($clientIds->isEmpty()) {
                continue;
            }

            $pairs = collect();
            foreach ($userIds as $userId) {
                foreach ($clientIds as $clientId) {
                    $pairs->push([
                        'user_id' => (int) $userId,
                        'client_id' => (int) $clientId,
                    ]);
                }
            }

            $this->insertAccessRows($pairs, $timestamp);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user_access');
    }

    private function insertAccessRows(iterable $pairs, mixed $timestamp): void
    {
        $batch = [];

        foreach ($pairs as $pair) {
            $userId = isset($pair->user_id) ? (int) $pair->user_id : (int) ($pair['user_id'] ?? 0);
            $clientId = isset($pair->client_id) ? (int) $pair->client_id : (int) ($pair['client_id'] ?? 0);

            if ($userId <= 0 || $clientId <= 0) {
                continue;
            }

            $batch[] = [
                'user_id' => $userId,
                'client_id' => $clientId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (count($batch) >= 1000) {
                DB::table('client_user_access')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('client_user_access')->insertOrIgnore($batch);
        }
    }
};
