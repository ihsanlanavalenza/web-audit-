<div>
    <h2 class="text-xl font-bold mb-1">Daftar Akun</h2>
    <p class="text-sm text-white/50 mb-6">Buat akun baru untuk mulai menggunakan WebAudit</p>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label class="form-label">Nama Lengkap</label>
            <input wire:model="name" type="text" class="form-input" placeholder="Masukkan nama lengkap" id="register-name">
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Email</label>
            <input wire:model="email" type="email" class="form-input" placeholder="email@contoh.com" id="register-email" {{ $invitation_token ? 'readonly' : '' }}>
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Peran</label>
            <select wire:model="role" class="form-input" id="register-role" {{ $invitation_token ? 'disabled' : '' }}>
                <option value="auditor">🔵 Auditor</option>
                <option value="auditi">🔴 Auditi</option>
            </select>
        </div>

        <div>
            <label class="form-label">Password</label>
            <input wire:model="password" type="password" class="form-input" placeholder="Minimal 8 karakter" id="register-password">
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Konfirmasi Password</label>
            <input wire:model="password_confirmation" type="password" class="form-input" placeholder="Ulangi password" id="register-password-confirm">
        </div>

        <button type="submit" class="btn-auditor w-full text-center" id="register-submit">
            <span wire:loading.remove>Daftar</span>
            <span wire:loading>Memproses...</span>
        </button>
    </form>

    <p class="text-center text-sm text-white/40 mt-6">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Masuk</a>
    </p>
</div>
