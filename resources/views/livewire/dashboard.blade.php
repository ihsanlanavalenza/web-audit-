<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>

    @if(auth()->user()->isAuditor())
        {{-- Auditor Dashboard --}}
        @if(!$hasKap)
        <div class="glass-card p-6 mb-6 border-l-4 border-l-amber-500">
            <h3 class="font-semibold text-amber-700 mb-2">⚠️ Profil KAP Belum Diisi</h3>
            <p class="text-sm text-slate-500 mb-4">Silakan isi profil KAP terlebih dahulu untuk mulai menggunakan fitur.</p>
            <a href="{{ route('kap-profile') }}" class="btn-auditor inline-block text-sm">Isi Profil KAP</a>
        </div>
        @endif

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Klien</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-slate-900">{{ $totalClients }}</p>
            </div>
            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Data Request</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-slate-900">{{ $totalRequests }}</p>
            </div>
            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Undangan Pending</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-slate-900">{{ $pendingInvites }}</p>
            </div>
        </div>

        {{-- Status Overview --}}
        @if(count($statusCounts) > 0)
        <div class="glass-card p-6">
            <h3 class="text-sm font-semibold text-slate-600 mb-4 uppercase tracking-wider">Status Overview</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach(\App\Models\DataRequest::STATUSES as $key => $label)
                <div class="text-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="badge-{{ str_replace('_', '-', $key) }} text-xs">{{ $label }}</span>
                    <p class="text-2xl font-bold mt-2 text-slate-900">{{ $statusCounts[$key] ?? 0 }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    @else
        {{-- Auditi Dashboard --}}
        <div class="glass-card p-6">
            <h3 class="font-semibold text-slate-900 mb-2">Akses Anda</h3>
            @if($clientId)
                <p class="text-sm text-slate-500 mb-4">Anda memiliki akses ke jadwal data request klien berikut.</p>
                <a href="{{ route('schedule.show', $clientId) }}" class="btn-auditi inline-block text-sm">Lihat Schedule</a>
            @else
                <p class="text-sm text-slate-400">Anda belum memiliki akses ke klien manapun. Hubungi Auditor untuk mendapatkan undangan.</p>
            @endif
        </div>
    @endif
</div>
