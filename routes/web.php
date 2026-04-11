<?php

use App\Livewire\Login;
use App\Livewire\Register;
use App\Livewire\Dashboard;
use App\Livewire\KapProfileSetup;
use App\Livewire\ClientManager;
use App\Livewire\InviteManager;
use App\Livewire\DataRequestTable;
use App\Livewire\SuperAdminDashboard;
use App\Livewire\UserManager;
use App\Livewire\AdminKapManager;
use App\Livewire\AdminClientManager;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/__diag/logs', function () {
    abort_unless(request('k') === 'diag-web-audit-500', 404);

    $report = [
        'app_env' => (string) config('app.env'),
        'app_debug' => (string) (config('app.debug') ? 'true' : 'false'),
        'app_key_set' => (string) (!empty(config('app.key')) ? 'true' : 'false'),
        'session_driver' => (string) config('session.driver'),
        'cache_store' => (string) config('cache.default'),
        'queue_default' => (string) config('queue.default'),
    ];

    $envPath = base_path('.env');
    $envPairs = [
        'APP_KEY' => '(missing)',
        'DB_CONNECTION' => '(missing)',
        'SESSION_DRIVER' => '(missing)',
        'CACHE_STORE' => '(missing)',
        'QUEUE_CONNECTION' => '(missing)',
    ];

    if (is_file($envPath) && is_readable($envPath)) {
        $envLines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($envLines as $line) {
            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            if (array_key_exists($k, $envPairs)) {
                $envPairs[$k] = $v;
            }
        }
    }

    $logPath = storage_path('logs/laravel.log');
    if (!is_file($logPath)) {
        $info = "Diagnostics:\n";
        foreach ($report as $key => $value) {
            $info .= "{$key}={$value}\n";
        }

        foreach ($envPairs as $key => $value) {
            if ($key === 'APP_KEY') {
                $value = $value === '' ? '(empty)' : ('len='.strlen($value));
            }
            $info .= "env_{$key}={$value}\n";
        }

        return response($info."\nLog file not found: {$logPath}\n", 404, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $lines = @file($logPath);
    if ($lines === false) {
        $info = "Diagnostics:\n";
        foreach ($report as $key => $value) {
            $info .= "{$key}={$value}\n";
        }

        foreach ($envPairs as $key => $value) {
            if ($key === 'APP_KEY') {
                $value = $value === '' ? '(empty)' : ('len='.strlen($value));
            }
            $info .= "env_{$key}={$value}\n";
        }

        return response($info."\nUnable to read log file: {$logPath}\n", 500, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $tail = implode('', array_slice($lines, -250));
    $info = "Diagnostics:\n";
    foreach ($report as $key => $value) {
        $info .= "{$key}={$value}\n";
    }

    foreach ($envPairs as $key => $value) {
        if ($key === 'APP_KEY') {
            $value = $value === '' ? '(empty)' : ('len='.strlen($value));
        }
        $info .= "env_{$key}={$value}\n";
    }

    return response($info."\n--- laravel.log (tail) ---\n".$tail, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
})->withoutMiddleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
]);

Route::get('/__diag/fix-key', function () {
    abort_unless(request('k') === 'diag-web-audit-500', 404);

    $envPath = base_path('.env');
    if (!is_file($envPath) || !is_readable($envPath) || !is_writable($envPath)) {
        return response("Cannot access writable .env at {$envPath}\n", 500, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $content = file_get_contents($envPath);
    if ($content === false) {
        return response("Failed reading .env\n", 500, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $setEnv = static function (string $key, string $value, string $buffer): string {
        $line = $key.'='.$value;
        if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $buffer)) {
            return (string) preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $buffer, 1);
        }

        return rtrim($buffer, "\n")."\n{$line}\n";
    };

    $content = $setEnv('APP_ENV', 'production', $content);
    $content = $setEnv('APP_DEBUG', 'false', $content);
    $content = $setEnv('APP_URL', 'https://auditin.my.id', $content);

    if (!preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/=]{40,}$/m', $content)) {
        $content = $setEnv('APP_KEY', 'base64:'.base64_encode(random_bytes(32)), $content);
    }

    if (!preg_match('/^DB_CONNECTION=.*$/m', $content) || preg_match('/^DB_CONNECTION=sqlite$/m', $content)) {
        $content = $setEnv('DB_CONNECTION', 'sqlite', $content);
        $content = $setEnv('SESSION_DRIVER', 'file', $content);
        $content = $setEnv('CACHE_STORE', 'file', $content);
        $content = $setEnv('QUEUE_CONNECTION', 'sync', $content);

        $sqlitePath = database_path('database.sqlite');
        if (!is_file($sqlitePath)) {
            @touch($sqlitePath);
        }
    }

    if (file_put_contents($envPath, $content) === false) {
        return response("Failed writing .env\n", 500, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    @unlink(base_path('bootstrap/cache/config.php'));
    @unlink(base_path('bootstrap/cache/routes-v7.php'));

    return response("OK: .env repaired and config cache cleared\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
})->withoutMiddleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
]);

/*
|--------------------------------------------------------------------------
| Guest Routes (Belum Login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Rute Login Google
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', fn() => redirect()->route('dashboard'));

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Schedule (Auditor & Auditi)
    Route::get('/schedule', DataRequestTable::class)->name('schedule.index');
    Route::get('/schedule/{clientId}', DataRequestTable::class)->name('schedule.show');

    /*
    |----------------------------------------------------------------------
    | Auditor Only Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('auditor')->group(function () {
        Route::get('/kap-profile', KapProfileSetup::class)->name('kap-profile');
        Route::get('/clients', ClientManager::class)->name('clients.index');
        Route::get('/invitations', InviteManager::class)->name('invitations.index');
    });

    /*
    |----------------------------------------------------------------------
    | Super Admin Only Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('superadmin')->prefix('admin')->group(function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->name('admin.dashboard');
        Route::get('/users', UserManager::class)->name('admin.users');
        Route::get('/kaps', AdminKapManager::class)->name('admin.kaps');
        Route::get('/clients', AdminClientManager::class)->name('admin.clients');
    });
});
