<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'auditor';
    public string $invitation_token = '';

    public function mount()
    {
        if (request()->has('token')) {
            $this->invitation_token = request()->get('token');
            $invitation = \App\Models\Invitation::where('token', $this->invitation_token)
                ->whereNull('accepted_at')
                ->first();
            if ($invitation) {
                $this->email = $invitation->email;
                $this->role = $invitation->role;
            }
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:auditor,auditi',  // super_admin tidak bisa via registrasi publik
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'invitation_token' => $this->invitation_token ?: null,
        ]);

        // Accept invitation if exists
        if ($this->invitation_token) {
            $invitation = \App\Models\Invitation::where('token', $this->invitation_token)
                ->whereNull('accepted_at')
                ->first();
            if ($invitation) {
                $invitation->update(['accepted_at' => now()]);
                if ($invitation->role === 'auditi' && $invitation->client_id) {
                    $user->update(['kap_id' => $invitation->kap_id]);
                }
            }
        }

        Auth::login($user);

        if ($user->isAuditor()) {
            return redirect()->route('kap-profile');
        }

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.register')
            ->layout('layouts.guest');
    }
}
