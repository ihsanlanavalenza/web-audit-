<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Client Assistance Schedule</h1>
            <p class="text-sm text-white/50 mt-1">Jadwal permintaan dan tracking data audit</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Client Selector (Auditor Only) --}}
            @if(auth()->user()->isAuditor() && count($clients) > 0)
            <select wire:model.live="clientId" class="form-input w-auto text-sm" id="client-selector">
                <option value="">-- Pilih Klien --</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}">{{ $c->nama_client }}</option>
                @endforeach
            </select>
            @endif

            @if($clientId && auth()->user()->isAuditor())
            <button wire:click="openAddModal" class="btn-auditor text-sm" id="btn-add-request">
                + Tambah Request
            </button>
            @endif
        </div>
    </div>

    @if(!$clientId)
    <div class="glass-card p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125"/></svg>
        </div>
        <p class="text-white/40 text-sm">Pilih klien terlebih dahulu untuk melihat schedule.</p>
    </div>
    @elseif($requests->count() === 0)
    <div class="glass-card p-12 text-center">
        <p class="text-white/40 text-sm">Belum ada data request untuk klien ini.</p>
    </div>
    @else
    {{-- Data Request Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Section</th>
                        <th>Account / Process</th>
                        <th>Description</th>
                        <th>Request Date</th>
                        <th>Expected Received</th>
                        <th>Input File</th>
                        <th>Status</th>
                        <th>Last Update</th>
                        <th>Date Input</th>
                        <th>Comment (Client)</th>
                        <th>Comment (Auditor)</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr wire:key="req-{{ $req->id }}">
                        <td class="font-mono font-semibold">{{ $req->no }}</td>
                        <td>{{ $req->section ?? '-' }}</td>
                        <td>{{ $req->account_process ?? '-' }}</td>
                        <td class="max-w-[200px] truncate" title="{{ $req->description }}">{{ $req->description ?? '-' }}</td>
                        <td class="whitespace-nowrap">{{ $req->request_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap">{{ $req->expected_received?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            @if($req->input_file)
                                <a href="{{ asset('storage/' . $req->input_file) }}" target="_blank" class="text-blue-400 hover:text-blue-300 text-xs font-medium underline">
                                    📎 Lihat File
                                </a>
                            @else
                                @if(auth()->user()->isAuditi())
                                <div x-data="{ uploading: false }">
                                    <input type="file" wire:model="uploadFile"
                                        x-on:livewire-upload-start="uploading = true"
                                        x-on:livewire-upload-finish="uploading = false"
                                        class="hidden" id="file-{{ $req->id }}"
                                        x-on:change="$wire.uploadFileForRow({{ $req->id }})">
                                    <label for="file-{{ $req->id }}" class="cursor-pointer inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-xs text-white/60 hover:text-white transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                        Upload
                                    </label>
                                    <div x-show="uploading" class="text-xs text-amber-400 mt-1">Uploading...</div>
                                </div>
                                @else
                                <span class="text-xs text-white/30">—</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->isAuditor())
                            <select wire:change="updateStatus({{ $req->id }}, $event.target.value)" class="text-xs rounded-full px-2 py-1 border-0 badge-{{ str_replace('_', '-', $req->status) }} cursor-pointer">
                                @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ $req->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="badge-{{ str_replace('_', '-', $req->status) }}">
                                {{ $statuses[$req->status] ?? $req->status }}
                            </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-xs text-white/50">{{ $req->last_update?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap">{{ $req->date_input?->format('d/m/Y') ?? '-' }}</td>
                        {{-- Comments --}}
                        <td>
                            @if($commentRowId === $req->id && !auth()->user()->isAuditor())
                            <div class="flex gap-1">
                                <input wire:model="newComment" type="text" class="form-input text-xs py-1 px-2" style="min-width:100px">
                                <button wire:click="saveComment({{ $req->id }})" class="text-emerald-400 text-xs">✓</button>
                            </div>
                            @else
                            <div class="flex items-center gap-1">
                                <span class="text-xs truncate max-w-[120px]" title="{{ $req->comment_client }}">{{ $req->comment_client ?: '-' }}</span>
                                @if(auth()->user()->isAuditi())
                                <button wire:click="openComment({{ $req->id }})" class="text-blue-400 text-xs">✎</button>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($commentRowId === $req->id && auth()->user()->isAuditor())
                            <div class="flex gap-1">
                                <input wire:model="newComment" type="text" class="form-input text-xs py-1 px-2" style="min-width:100px">
                                <button wire:click="saveComment({{ $req->id }})" class="text-emerald-400 text-xs">✓</button>
                            </div>
                            @else
                            <div class="flex items-center gap-1">
                                <span class="text-xs truncate max-w-[120px]" title="{{ $req->comment_auditor }}">{{ $req->comment_auditor ?: '-' }}</span>
                                @if(auth()->user()->isAuditor())
                                <button wire:click="openComment({{ $req->id }})" class="text-blue-400 text-xs">✎</button>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->isAuditor())
                            <div class="flex gap-2">
                                <button wire:click="editRow({{ $req->id }})" class="text-blue-400 hover:text-blue-300 text-xs font-medium">Edit</button>
                                <button wire:click="deleteRow({{ $req->id }})" wire:confirm="Yakin hapus baris ini?" class="text-red-400 hover:text-red-300 text-xs font-medium">Hapus</button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="modal-content p-8" style="max-width: 48rem;">
            <h3 class="text-lg font-bold mb-6">{{ $editId ? 'Edit Data Request' : 'Tambah Data Request' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">No</label>
                        <input wire:model="no" type="number" class="form-input" min="1">
                        @error('no') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Section</label>
                        <input wire:model="section" type="text" class="form-input" placeholder="Bagian / Section">
                    </div>
                </div>
                <div>
                    <label class="form-label">Account / Process</label>
                    <input wire:model="account_process" type="text" class="form-input" placeholder="Nama akun atau proses">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" class="form-input" rows="2" placeholder="Deskripsi data yang diminta"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Request Date</label>
                        <input wire:model="request_date" type="date" class="form-input">
                        @error('request_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Expected Received</label>
                        <input wire:model="expected_received" type="date" class="form-input">
                        @error('expected_received') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-input">
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Date Input</label>
                        <input wire:model="date_input" type="date" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Comment (Client)</label>
                        <input wire:model="comment_client" type="text" class="form-input" placeholder="Komentar klien">
                    </div>
                    <div>
                        <label class="form-label">Comment (Auditor)</label>
                        <input wire:model="comment_auditor" type="text" class="form-input" placeholder="Komentar auditor">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-auditor">{{ $editId ? 'Perbarui' : 'Simpan' }}</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-ghost">Batal</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
