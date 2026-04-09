<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
        class="relative p-2 text-slate-400 hover:text-slate-600 focus:outline-none focus:text-slate-600 transition-colors">
        <svg class="h-6 w-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            viewBox="0 0 24 24" stroke="currentColor">
            <path
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
            </path>
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" x-transition.opacity
        class="absolute right-0 mt-2 w-80 bg-white/90 backdrop-blur-md border border-slate-100 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
        <div class="p-3 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
            @if ($unreadCount > 0)
                <button wire:click="markAsRead" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Tandai
                    semua dibaca</button>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto w-full">
            @forelse($notifications as $notification)
                <div wire:click="markAsRead('{{ $notification->id }}')"
                    class="p-3 border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors {{ is_null($notification->read_at) ? 'bg-blue-50/30' : '' }}">
                    <p class="text-sm text-slate-800">{{ $notification->data['message'] ?? 'Notifikasi baru' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="p-4 text-center text-sm text-slate-500">
                    Belum ada notifikasi
                </div>
            @endforelse
        </div>
    </div>
</div>
