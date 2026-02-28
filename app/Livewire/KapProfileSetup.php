<?php

namespace App\Livewire;

use App\Models\KapProfile;
use Livewire\Component;

class KapProfileSetup extends Component
{
    public string $nama_kap = '';
    public string $nama_pic = '';
    public string $alamat = '';
    public bool $isEdit = false;

    public function mount()
    {
        $profile = auth()->user()->kapProfile;
        if ($profile) {
            $this->nama_kap = $profile->nama_kap;
            $this->nama_pic = $profile->nama_pic;
            $this->alamat = $profile->alamat;
            $this->isEdit = true;
        }
    }

    public function save()
    {
        $this->validate([
            'nama_kap' => 'required|min:3|max:255',
            'nama_pic' => 'required|min:3|max:255',
            'alamat' => 'required|min:10',
        ]);

        KapProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'nama_kap' => $this->nama_kap,
                'nama_pic' => $this->nama_pic,
                'alamat' => $this->alamat,
            ]
        );

        session()->flash('success', 'Profil KAP berhasil ' . ($this->isEdit ? 'diperbarui' : 'disimpan') . '!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.kap-profile-setup')
            ->layout('layouts.app', ['title' => 'Profil KAP']);
    }
}
