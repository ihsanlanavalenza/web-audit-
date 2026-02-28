<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'WebAudit' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-mesh text-white antialiased">
    {{-- Dot Grid Pattern Canvas --}}
    <div class="bg-canvas" aria-hidden="true"></div>

    <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:py-12 relative z-10">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-6 sm:mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-red-500 mb-4 shadow-lg animate-glow">
                    <span class="text-xl sm:text-2xl font-extrabold tracking-tighter">WA</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">WebAudit</h1>
                <p class="text-xs sm:text-sm text-white/40 mt-1">Client Assistance Schedule</p>
            </div>

            {{-- Content Slot --}}
            <div class="glass-card p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
