<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight">Profil KAP</h1>
        <p class="text-sm text-white/50 mt-1">Lengkapi profil Kantor Akuntan Publik Anda</p>
    </div>

    <div class="glass-card p-8 max-w-xl">
        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="form-label">Nama KAP</label>
                <input wire:model="nama_kap" type="text" class="form-input" placeholder="KAP Contoh & Rekan" id="kap-nama">
                @error('nama_kap') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Nama PIC</label>
                <input wire:model="nama_pic" type="text" class="form-input" placeholder="Nama PIC yang bertanggung jawab" id="kap-pic">
                @error('nama_pic') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Alamat</label>
                <textarea wire:model="alamat" class="form-input" rows="3" placeholder="Alamat lengkap KAP" id="kap-alamat"></textarea>
                @error('alamat') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-auditor" id="kap-submit">
                <span wire:loading.remove>{{ $isEdit ? 'Perbarui Profil' : 'Simpan Profil' }}</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </form>
    </div>
</div>
