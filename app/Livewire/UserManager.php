<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class UserManager extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'auditor';
    public string $search = '';

    public function openModal()
    {
        $this->reset(['name', 'email', 'password', 'role', 'editId']);
        $this->role = 'auditor';
        $this->showModal = true;
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);
        $this->editId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email' . ($this->editId ? ",{$this->editId}" : ''),
            'role' => 'required|in:super_admin,auditor,auditi',
        ];

        if (!$this->editId || $this->password) {
            $rules['password'] = 'required|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editId) {
            User::findOrFail($this->editId)->update($data);
            session()->flash('success', 'User berhasil diperbarui!');
        } else {
            User::create($data);
            session()->flash('success', 'User berhasil ditambahkan!');
        }

        $this->showModal = false;
    }

    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri!');
            return;
        }
        $user->delete();
        session()->flash('success', 'User berhasil dihapus!');
    }

    public function changeRole(int $id, string $role)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => $role]);
        session()->flash('success', "Role {$user->name} diubah ke {$role}!");
    }

    public function render()
    {
        $users = User::when($this->search, function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->latest()->paginate(15);

        return view('livewire.user-manager', [
            'users' => $users,
        ])->layout('layouts.app', ['title' => 'Kelola User']);
    }
}
