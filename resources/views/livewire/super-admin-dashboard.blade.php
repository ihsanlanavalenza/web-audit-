<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight">🛡️ Super Admin Dashboard</h1>
        <p class="text-sm text-white/50 mt-1">Kelola dan monitor seluruh sistem WebAudit</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="glass-card p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-white/50 font-medium uppercase tracking-wider">Total Users</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-yellow-400 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-bold">{{ $totalUsers }}</p>
            <div class="flex gap-2 mt-2 text-xs text-white/40">
                <span>🔵 {{ $totalAuditors }} Auditor</span>
                <span>🔴 {{ $totalAuditis }} Auditi</span>
            </div>
        </div>
        <div class="glass-card p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-white/50 font-medium uppercase tracking-wider">KAP</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-bold">{{ $totalKap }}</p>
        </div>
        <div class="glass-card p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-white/50 font-medium uppercase tracking-wider">Klien</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-bold">{{ $totalClients }}</p>
        </div>
        <div class="glass-card p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-white/50 font-medium uppercase tracking-wider">Data Requests</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-bold">{{ $totalRequests }}</p>
        </div>
    </div>

    {{-- Status Overview --}}
    @if(count($statusCounts) > 0)
    <div class="glass-card p-4 sm:p-6 mb-8">
        <h3 class="text-sm font-semibold text-white/70 mb-4 uppercase tracking-wider">Status Overview — Semua Request</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach(\App\Models\DataRequest::STATUSES as $key => $label)
            <div class="text-center p-3 rounded-xl bg-white/5">
                <span class="badge-{{ str_replace('_', '-', $key) }} text-xs">{{ $label }}</span>
                <p class="text-2xl font-bold mt-2">{{ $statusCounts[$key] ?? 0 }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {{-- Recent Users --}}
        <div class="glass-card p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wider">User Terbaru</h3>
                <a href="{{ route('admin.users') }}" class="text-xs text-blue-400 hover:text-blue-300">Lihat Semua →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentUsers as $user)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0
                        {{ $user->role === 'super_admin' ? 'bg-gradient-to-br from-amber-500 to-yellow-400' : ($user->role === 'auditor' ? 'bg-gradient-to-br from-blue-600 to-blue-400' : 'bg-gradient-to-br from-red-600 to-red-400') }}">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                        <p class="text-xs text-white/40 truncate">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold shrink-0
                        {{ $user->role === 'super_admin' ? 'bg-amber-500/20 text-amber-300' : ($user->role === 'auditor' ? 'bg-blue-500/20 text-blue-300' : 'bg-red-500/20 text-red-300') }}">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-white/40 text-center py-4">Belum ada user.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Requests --}}
        <div class="glass-card p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-white/70 mb-4 uppercase tracking-wider">Request Terbaru</h3>
            <div class="space-y-3">
                @forelse($recentRequests as $req)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $req->description ?? $req->account_process ?? 'No. ' . $req->no }}</p>
                        <p class="text-xs text-white/40 truncate">{{ $req->client?->nama_client ?? '-' }}</p>
                    </div>
                    <span class="badge-{{ str_replace('_', '-', $req->status) }} text-xs shrink-0">
                        {{ \App\Models\DataRequest::STATUSES[$req->status] ?? $req->status }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-white/40 text-center py-4">Belum ada request.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Flow Explanation --}}
    <div class="glass-card p-4 sm:p-6 mt-8">
        <h3 class="text-sm font-semibold text-white/70 mb-4 uppercase tracking-wider">📋 Alur Kerja Sistem</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-500/10 to-yellow-500/5 border border-amber-500/20">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-300 text-sm">🛡️</div>
                    <h4 class="font-semibold text-amber-300 text-sm">Super Admin</h4>
                </div>
                <ul class="text-xs text-white/60 space-y-1.5 list-disc list-inside">
                    <li>Kelola semua User & Role</li>
                    <li>Monitor semua KAP & Klien</li>
                    <li>Lihat statistik global</li>
                    <li>Akses seluruh data request</li>
                </ul>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-500/10 to-blue-600/5 border border-blue-500/20">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-300 text-sm">🔵</div>
                    <h4 class="font-semibold text-blue-300 text-sm">Auditor</h4>
                </div>
                <ul class="text-xs text-white/60 space-y-1.5 list-disc list-inside">
                    <li>Daftar → Isi Profil KAP</li>
                    <li>Tambah Client baru</li>
                    <li>Buat Data Request schedule</li>
                    <li>Kirim undangan ke Auditi</li>
                    <li>Review file & ubah status</li>
                    <li>Berikan komentar auditor</li>
                </ul>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-red-500/10 to-red-600/5 border border-red-500/20">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center text-red-300 text-sm">🔴</div>
                    <h4 class="font-semibold text-red-300 text-sm">Auditi</h4>
                </div>
                <ul class="text-xs text-white/60 space-y-1.5 list-disc list-inside">
                    <li>Terima undangan via link token</li>
                    <li>Daftar akun → akses schedule</li>
                    <li>Lihat data request dari auditor</li>
                    <li>Upload file → status "On Review"</li>
                    <li>Berikan komentar klien</li>
                </ul>
            </div>
        </div>
    </div>
</div>
