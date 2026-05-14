<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Admin Kelurahan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-600 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">L</span>
                    </div>
                    <span class="font-semibold text-gray-800">Lansia</span>
                </div>

                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard.lansia') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.lansia*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Data Lansia
                    </a>
                    <a href="{{ route('dashboard.bantuan') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.bantuan') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Bantuan Gizi
                    </a>
                    <a href="{{ route('dashboard.laporan') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.laporan*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Laporan
                    </a>
                    <a href="{{ route('dashboard.monitoring') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.monitoring') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Monitoring
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
