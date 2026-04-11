<div>
    <h2 class="text-xl font-bold mb-1 text-slate-900">Pilih Role Akun</h2>
    <p class="text-sm text-slate-500 mb-6">Pilih peran Anda untuk melanjutkan penggunaan WebAudit.</p>

    @if (session('success'))
        <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 p-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="saveRole" class="space-y-4">
        <label class="block cursor-pointer">
            <input type="radio" wire:model="role" value="auditor" class="sr-only peer">
            <div
                class="rounded-xl border border-slate-200 p-4 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-200">
                <p class="font-semibold text-slate-900">Auditor</p>
                <p class="text-sm text-slate-500 mt-1">Kelola KAP, klien, undangan, dan proses audit.</p>
            </div>
        </label>

        <label class="block cursor-pointer">
            <input type="radio" wire:model="role" value="auditi" class="sr-only peer">
            <div
                class="rounded-xl border border-slate-200 p-4 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:ring-2 peer-checked:ring-red-200">
                <p class="font-semibold text-slate-900">Auditi</p>
                <p class="text-sm text-slate-500 mt-1">Menerima dan mengunggah dokumen sesuai permintaan audit.</p>
            </div>
        </label>

        @error('role')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn-auditor w-full text-center">
            <span wire:loading.remove>Simpan Role</span>
            <span wire:loading>Menyimpan...</span>
        </button>
    </form>
</div>
