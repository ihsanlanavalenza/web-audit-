<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Undangan</h1>
            <p class="text-sm text-white/50 mt-1">Kelola undangan Auditor dan Auditi</p>
        </div>
        <button wire:click="openModal" class="btn-auditor text-sm" id="btn-invite">+ Buat Undangan</button>
    </div>

    {{-- Invitations Table --}}
    @if($invitations->count() > 0)
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Klien</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $inv)
                    <tr>
                        <td>{{ $inv->email }}</td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->role === 'auditor' ? 'bg-blue-500/20 text-blue-300' : 'bg-red-500/20 text-red-300' }}">
                                {{ ucfirst($inv->role) }}
                            </span>
                        </td>
                        <td>{{ $inv->client?->nama_client ?? '-' }}</td>
                        <td>
                            <code class="text-xs bg-white/5 px-2 py-1 rounded font-mono">{{ substr($inv->token, 0, 16) }}...</code>
                        </td>
                        <td>
                            @if($inv->accepted_at)
                                <span class="badge-received">Diterima</span>
                            @elseif($inv->isExpired())
                                <span class="badge-not-applicable">Kedaluwarsa</span>
                            @else
                                <span class="badge-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="navigator.clipboard.writeText('{{ url('/register?token=' . $inv->token) }}')" class="text-blue-400 hover:text-blue-300 text-xs font-medium">
                                    Salin Link
                                </button>
                                <button wire:click="deleteInvitation({{ $inv->id }})" wire:confirm="Yakin hapus undangan ini?" class="text-red-400 hover:text-red-300 text-xs font-medium">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="glass-card p-12 text-center">
        <p class="text-white/40 text-sm">Belum ada undangan. Klik "Buat Undangan" untuk mengundang orang.</p>
    </div>
    @endif

    {{-- Create Invitation Modal --}}
    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="modal-content p-8">
            <h3 class="text-lg font-bold mb-6">Buat Undangan Baru</h3>
            <form wire:submit="sendInvite" class="space-y-4">
                <div>
                    <label class="form-label">Email</label>
                    <input wire:model="email" type="email" class="form-input" placeholder="email@contoh.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Peran</label>
                    <select wire:model.live="role" class="form-input">
                        <option value="auditor">🔵 Auditor</option>
                        <option value="auditi">🔴 Auditi</option>
                    </select>
                </div>
                @if($role === 'auditi')
                <div>
                    <label class="form-label">Klien (untuk akses Auditi)</label>
                    <select wire:model="client_id" class="form-input">
                        <option value="">-- Pilih Klien --</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->nama_client }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-auditor">Buat Undangan</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-ghost">Batal</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
