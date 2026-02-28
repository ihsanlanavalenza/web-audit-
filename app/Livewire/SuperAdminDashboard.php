<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\KapProfile;
use App\Models\Client;
use App\Models\DataRequest;
use App\Models\Invitation;
use Livewire\Component;

class SuperAdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.super-admin-dashboard', [
            'totalUsers' => User::count(),
            'totalAuditors' => User::where('role', 'auditor')->count(),
            'totalAuditis' => User::where('role', 'auditi')->count(),
            'totalKap' => KapProfile::count(),
            'totalClients' => Client::count(),
            'totalRequests' => DataRequest::count(),
            'pendingInvites' => Invitation::whereNull('accepted_at')->count(),
            'statusCounts' => DataRequest::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentRequests' => DataRequest::with(['client'])->latest()->take(5)->get(),
        ])->layout('layouts.app', ['title' => 'Super Admin Dashboard']);
    }
}
