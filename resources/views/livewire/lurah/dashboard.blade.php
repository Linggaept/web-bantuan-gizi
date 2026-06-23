<div>
    {{-- Periode & Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Penerima Bantuan</p>
                <p class="text-4xl font-bold text-green-600">{{ $totalPenerima }}</p>
                <p class="text-xs text-gray-400 mt-1">Periode {{ $periodeLabel }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Periode Aktif</p>
                <p class="text-xl font-bold text-gray-800">{{ $periodeLabel }}</p>
                <p class="text-xs text-gray-400 mt-1">Pemeriksaan setiap 3 bulan</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Daftar Penerima Bantuan --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-medium text-gray-800">Daftar Penerima Bantuan — {{ $periodeLabel }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-600">No.</th>
                        <th class="text-left px-6 py-3 text-gray-600">Nama</th>
                        <th class="text-left px-6 py-3 text-gray-600">NIK</th>
                        <th class="text-left px-6 py-3 text-gray-600">Usia</th>
                        <th class="text-left px-6 py-3 text-gray-600">RW</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lansiaPenerima as $i => $lansia)
                    <tr class="border-b last:border-0">
                        <td class="px-6 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-6 py-3 font-medium">{{ $lansia->nama }}</td>
                        <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ $lansia->nik }}</td>
                        <td class="px-6 py-3">{{ $lansia->usia }} th</td>
                        <td class="px-6 py-3">RW {{ $lansia->rw }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada penerima bantuan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
