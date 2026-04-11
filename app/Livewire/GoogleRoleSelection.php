<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GoogleRoleSelection extends Component
{
    public string $role = '';

    public function mount()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ((int) session('google_role_selection_user_id') !== $user->id) {
            return $this->redirect(route('dashboard'), navigate: false);
        }
    }

    public function saveRole()
    {
        $this->validate([
            'role' => 'required|in:auditor,auditi',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ((int) session('google_role_selection_user_id') !== $user->id) {
            return redirect()->route('dashboard');
        }

        $user->update([
            'role' => $this->role,
        ]);

        session()->forget('google_role_selection_user_id');

        return redirect()->route('dashboard')
            ->with('success', 'Role Anda berhasil disimpan. Selamat datang di WebAudit.');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.google-role-selection');
    }
}
