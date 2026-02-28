<div>
    <h2 class="text-xl font-bold mb-1">Masuk</h2>
    <p class="text-sm text-white/50 mb-6">Login ke akun WebAudit Anda</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="form-label">Email</label>
            <input wire:model="email" type="email" class="form-input" placeholder="email@contoh.com" id="login-email">
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Password</label>
            <input wire:model="password" type="password" class="form-input" placeholder="Masukkan password" id="login-password">
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input wire:model="remember" type="checkbox" class="rounded border-white/20 bg-white/5 text-blue-600" id="login-remember">
            <label for="login-remember" class="text-sm text-white/60">Ingat saya</label>
        </div>

        <button type="submit" class="btn-auditor w-full text-center" id="login-submit">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses...</span>
        </button>
    </form>

    <p class="text-center text-sm text-white/40 mt-6">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-medium">Daftar</a>
    </p>
</div>
