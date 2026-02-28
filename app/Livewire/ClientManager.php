<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class ClientManager extends Component
{
    public string $nama_client = '';
    public string $nama_pic = '';
    public string $no_contact = '';
    public string $tahun_audit = '';
    public bool $showModal = false;
    public bool $showDeleteConfirm = false;
    public ?int $editId = null;

    protected function rules()
    {
        return [
            'nama_client' => 'required|min:3|max:255',
            'nama_pic' => 'required|min:3|max:255',
            'no_contact' => 'required|min:8|max:20',
            'tahun_audit' => 'required|digits:4',
        ];
    }

    public function openModal()
    {
        $this->reset(['nama_client', 'nama_pic', 'no_contact', 'tahun_audit', 'editId']);
        $this->tahun_audit = date('Y');
        $this->showModal = true;
    }

    public function editClient(int $id)
    {
        $client = $this->getKapProfile()->clients()->findOrFail($id);
        $this->editId = $client->id;
        $this->nama_client = $client->nama_client;
        $this->nama_pic = $client->nama_pic;
        $this->no_contact = $client->no_contact;
        $this->tahun_audit = $client->tahun_audit;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $kap = $this->getKapProfile();

        if ($this->editId) {
            $client = $kap->clients()->findOrFail($this->editId);
            $client->update([
                'nama_client' => $this->nama_client,
                'nama_pic' => $this->nama_pic,
                'no_contact' => $this->no_contact,
                'tahun_audit' => $this->tahun_audit,
            ]);
            session()->flash('success', 'Klien berhasil diperbarui!');
        } else {
            $kap->clients()->create([
                'nama_client' => $this->nama_client,
                'nama_pic' => $this->nama_pic,
                'no_contact' => $this->no_contact,
                'tahun_audit' => $this->tahun_audit,
            ]);
            session()->flash('success', 'Klien berhasil ditambahkan!');
        }

        $this->showModal = false;
    }

    public function deleteClient(int $id)
    {
        $this->getKapProfile()->clients()->findOrFail($id)->delete();
        session()->flash('success', 'Klien berhasil dihapus!');
    }

    public function deleteAll()
    {
        $this->getKapProfile()->clients()->delete();
        $this->showDeleteConfirm = false;
        session()->flash('success', 'Semua klien berhasil dihapus!');
    }

    private function getKapProfile()
    {
        $kap = auth()->user()->kapProfile;
        if (!$kap) {
            session()->flash('error', 'Silakan isi Profil KAP terlebih dahulu.');
            return redirect()->route('kap-profile');
        }
        return $kap;
    }

    public function render()
    {
        $kap = auth()->user()->kapProfile;
        $clients = $kap ? $kap->clients()->latest()->get() : collect();

        return view('livewire.client-manager', compact('clients'))
            ->layout('layouts.app', ['title' => 'Manajemen Klien']);
    }
}
