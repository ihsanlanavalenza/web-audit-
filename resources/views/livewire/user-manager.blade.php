<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Kelola User</h1>
            <p class="text-sm text-white/50 mt-1">Manajemen semua pengguna sistem</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" class="form-input text-sm sm:w-64" placeholder="🔍 Cari nama atau email..." id="search-user">
            <button wire:click="openModal" class="btn-superadmin text-sm whitespace-nowrap" id="btn-add-user">+ Tambah User</button>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="font-mono">{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0
                                    {{ $user->role === 'super_admin' ? 'bg-gradient-to-br from-amber-500 to-yellow-400' : ($user->role === 'auditor' ? 'bg-gradient-to-br from-blue-600 to-blue-400' : 'bg-gradient-to-br from-red-600 to-red-400') }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="font-medium truncate max-w-[150px]">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="text-white/60 text-xs">{{ $user->email }}</td>
                        <td>
                            <select wire:change="changeRole({{ $user->id }}, $event.target.value)"
                                class="text-xs rounded-full px-2 py-1 border-0 cursor-pointer font-semibold
                                {{ $user->role === 'super_admin' ? 'bg-amber-500/20 text-amber-300' : ($user->role === 'auditor' ? 'bg-blue-500/20 text-blue-300' : 'bg-red-500/20 text-red-300') }}"
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="auditor" {{ $user->role === 'auditor' ? 'selected' : '' }}>Auditor</option>
                                <option value="auditi" {{ $user->role === 'auditi' ? 'selected' : '' }}>Auditi</option>
                            </select>
                        </td>
                        <td class="text-xs text-white/50 whitespace-nowrap">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button wire:click="editUser({{ $user->id }})" class="text-blue-400 hover:text-blue-300 text-xs font-medium">Edit</button>
                                @if($user->id !== auth()->id())
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Yakin hapus user {{ $user->name }}?" class="text-red-400 hover:text-red-300 text-xs font-medium">Hapus</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-white/40">Tidak ada user ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-white/5">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="modal-content p-6 sm:p-8">
            <h3 class="text-lg font-bold mb-6">{{ $editId ? 'Edit User' : 'Tambah User Baru' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input wire:model="name" type="text" class="form-input" placeholder="Nama lengkap">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input wire:model="email" type="email" class="form-input" placeholder="email@contoh.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <select wire:model="role" class="form-input">
                        <option value="super_admin">🛡️ Super Admin</option>
                        <option value="auditor">🔵 Auditor</option>
                        <option value="auditi">🔴 Auditi</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Password {{ $editId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input wire:model="password" type="password" class="form-input" placeholder="{{ $editId ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-superadmin">{{ $editId ? 'Perbarui' : 'Simpan' }}</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-ghost">Batal</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
