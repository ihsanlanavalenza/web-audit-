<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Undangan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola undangan Auditor dan Auditi</p>
        </div>
        <button wire:click="openModal" class="btn-auditor text-sm" id="btn-invite">+ Buat Undangan</button>
    </div>

    {{-- Invitations Table --}}
    @if ($invitations->count() > 0)
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
                        @foreach ($invitations as $inv)
                            <tr>
                                <td class="text-slate-900">{{ $inv->email }}</td>
                                <td>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->role === 'auditor' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                        {{ ucfirst($inv->role) }}
                                    </span>
                                </td>
                                <td class="text-slate-600">{{ $inv->client?->nama_client ?? '-' }}</td>
                                <td>
                                    <code
                                        class="text-xs bg-slate-100 px-2 py-1 rounded font-mono text-slate-600">{{ substr($inv->token, 0, 16) }}...</code>
                                </td>
                                <td>
                                    @if ($inv->accepted_at)
                                        <span class="badge-received">Diterima</span>
                                    @elseif($inv->isExpired())
                                        <span class="badge-not-applicable">Kedaluwarsa</span>
                                    @else
                                        <span class="badge-pending">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button
                                            onclick="navigator.clipboard.writeText('{{ url('/register?token=' . $inv->token) }}')"
                                            class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            Salin Link
                                        </button>
                                        <button wire:click="deleteInvitation({{ $inv->id }})"
                                            wire:confirm="Yakin hapus undangan ini?"
                                            class="text-red-600 hover:text-red-700 text-xs font-medium">
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
            <p class="text-slate-400 text-sm">Belum ada undangan. Klik "Buat Undangan" untuk mengundang orang.</p>
        </div>
    @endif

    {{-- Create Invitation Modal --}}
    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-content p-8">
                <h3 class="text-lg font-bold mb-6 text-slate-900">Buat Undangan Baru</h3>
                <form wire:submit="sendInvite" class="space-y-4">
                    <div>
                        <label class="form-label">Email</label>
                        <input wire:model="email" type="email" class="form-input" placeholder="email@contoh.com">
                        @error('email')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Peran</label>
                        <select wire:model.live="role" class="form-input">
                            <option value="auditor">🔵 Auditor</option>
                            <option value="auditi">🔴 Auditi</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Klien (akses {{ $role === 'auditor' ? 'Auditor' : 'Auditi' }})</label>
                        <select wire:model="client_id" class="form-input">
                            <option value="">-- Pilih Klien --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->nama_client }}</option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn-auditor">Buat Undangan</button>
                        <button type="button" wire:click="$set('showModal', false)" class="btn-ghost">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
