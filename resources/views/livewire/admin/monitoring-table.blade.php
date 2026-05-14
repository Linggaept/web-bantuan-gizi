<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Input Hari Ini</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalInputHariIni }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Verifikasi Pending</p>
            <p class="text-3xl font-bold text-yellow-500">{{ $totalPending }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Total Penerima Bantuan</p>
            <p class="text-3xl font-bold text-green-600">{{ $totalPenerima }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Log Input Operator --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b">
                <h3 class="font-medium text-gray-800 text-sm">Log Input Operator (20 Terbaru)</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Operator</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Lansia</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Status</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logInput as $log)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $log->user?->name }}</td>
                        <td class="px-4 py-2">{{ $log->lansia?->nama }}</td>
                        <td class="px-4 py-2">
                            <span class="px-1.5 py-0.5 rounded text-xs {{ $log->status_verifikasi === 'terverifikasi' ? 'bg-green-100 text-green-700' : ($log->status_verifikasi === 'menunggu' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $log->status_verifikasi }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Distribusi Bantuan per RW --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b">
                <h3 class="font-medium text-gray-800 text-sm">Distribusi Bantuan per RW</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">RW</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Total Lansia</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Penerima</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Tidak Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiPerRw as $row)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2 font-medium">RW {{ $row->rw }}</td>
                        <td class="px-4 py-2">{{ $row->total_lansia }}</td>
                        <td class="px-4 py-2 text-green-600 font-medium">{{ $row->total_penerima }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $row->tidak_penerima }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
