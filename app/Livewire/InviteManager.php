<?php

namespace App\Livewire;

use App\Models\Invitation;
use Livewire\Component;

class InviteManager extends Component
{
    public string $email = '';
    public string $role = 'auditi';
    public ?int $client_id = null;
    public bool $showModal = false;

    public function openModal()
    {
        $this->reset(['email', 'role', 'client_id']);
        $this->role = 'auditi';
        $this->showModal = true;
    }

    public function sendInvite()
    {
        $this->validate([
            'email' => 'required|email',
            'role' => 'required|in:auditor,auditi',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $kap = auth()->user()->kapProfile;
        if (!$kap) {
            session()->flash('error', 'Silakan isi Profil KAP terlebih dahulu.');
            return redirect()->route('kap-profile');
        }

        $invitation = Invitation::create([
            'kap_id' => $kap->id,
            'client_id' => $this->role === 'auditi' ? $this->client_id : null,
            'email' => $this->email,
            'role' => $this->role,
            'token' => Invitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->showModal = false;
        session()->flash('success', 'Undangan berhasil dibuat! Token: ' . $invitation->token);
    }

    public function deleteInvitation(int $id)
    {
        $kap = auth()->user()->kapProfile;
        $kap->invitations()->findOrFail($id)->delete();
        session()->flash('success', 'Undangan berhasil dihapus!');
    }

    public function render()
    {
        $kap = auth()->user()->kapProfile;
        $invitations = $kap ? $kap->invitations()->with('client')->latest()->get() : collect();
        $clients = $kap ? $kap->clients()->get() : collect();

        return view('livewire.invite-manager', compact('invitations', 'clients'))
            ->layout('layouts.app', ['title' => 'Undangan']);
    }
}
