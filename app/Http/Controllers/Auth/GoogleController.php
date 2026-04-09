<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            if (!$email) {
                return redirect()->route('login')->with('error', 'Email Google tidak ditemukan. Gunakan akun Google yang memiliki email aktif.');
            }

            // 1. Cek apakah user sudah terdaftar
            $user = User::where('email', $email)->first();

            if ($user) {
                // Update google_id jika belum ada
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }

                Auth::login($user);
                session()->regenerate();
                return redirect()->route('dashboard');
            }

            // 2. Cek apakah user diundang
            $invitation = Invitation::where('email', $email)
                ->whereNull('accepted_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->first();

            if ($invitation) {
                $newUser = User::create([
                    'name' => $googleUser->getName() ?: $email,
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'password' => null,
                    'role' => in_array($invitation->role, ['auditor', 'auditi'], true) ? $invitation->role : 'auditi',
                    'kap_id' => $invitation->kap_id,
                ]);

                $invitation->update(['accepted_at' => now()]);

                Auth::login($newUser);
                session()->regenerate();
                return redirect()->route('dashboard');
            }

            // 3. Jika belum terdaftar dan tidak ada undangan, auto register langsung
            $newUser = User::create([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => null,
                'role' => 'auditi',
                'kap_id' => null,
            ]);

            Auth::login($newUser);
            session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat otomatis lewat Google.');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login menggunakan Google.');
        }
    }
}
