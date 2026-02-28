<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function mount()
    {
        // Super Admin → redirect ke admin dashboard
        if (auth()->user()->isSuperAdmin()) {
            return $this->redirect(route('admin.dashboard'), navigate: false);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $data = [];

        if ($user->isAuditor()) {
            $kap = $user->kapProfile;
            $data = [
                'hasKap' => !!$kap,
                'totalClients' => $kap ? $kap->clients()->count() : 0,
                'totalRequests' => $kap ? $kap->dataRequests()->count() : 0,
                'pendingInvites' => $kap ? $kap->invitations()->pending()->count() : 0,
                'statusCounts' => $kap ? $kap->dataRequests()
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray() : [],
            ];
        } else {
            // Auditi - find clients they are invited to
            $invitation = \App\Models\Invitation::where('email', $user->email)
                ->whereNotNull('accepted_at')
                ->first();
            $data = [
                'invitation' => $invitation,
                'clientId' => $invitation?->client_id,
            ];
        }

        return view('livewire.dashboard', $data)
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}

