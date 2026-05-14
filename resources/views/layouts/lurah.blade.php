<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Lurah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    <nav x-data="{ open: false }" class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-green-600 rounded flex items-center justify-center">
                    <span class="text-white text-xs font-bold">L</span>
                </div>
                <span class="font-semibold text-gray-800">Lansia</span>
            </div>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('lurah.dashboard') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('lurah.approval') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.approval') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Approval Bantuan
                </a>
                <a href="{{ route('lurah.laporan') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.laporan*') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Laporan
                </a>
            </div>

            {{-- Desktop logout --}}
            <div class="hidden md:flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
            </div>

            {{-- Mobile --}}
            <div class="flex items-center gap-2 md:hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
                <button @click="open = !open" class="p-2 rounded text-gray-600 hover:bg-gray-100 focus:outline-none" aria-label="Toggle menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display:none"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div x-show="open" class="md:hidden border-t mt-2 pb-2" style="display:none">
            <div class="flex flex-col pt-2 gap-1">
                <a href="{{ route('lurah.dashboard') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('lurah.approval') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.approval') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Approval Bantuan
                </a>
                <a href="{{ route('lurah.laporan') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.laporan*') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Laporan
                </a>
                <div class="px-4 py-2 text-sm text-gray-500 border-t mt-1 pt-2">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </nav>

    <main class="p-4 sm:p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
