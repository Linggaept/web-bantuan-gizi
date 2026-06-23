<div>
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Laporan Penyaluran Bantuan Gizi</h2>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterJenis" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="semua">Semua</option>
                <option value="penerima">Penerima</option>
                <option value="tidak_penerima">Tidak Penerima</option>
            </select>

            <div class="flex flex-wrap gap-2 ml-auto">
                <button wire:click="download" class="flex items-center gap-1 px-3 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">
                    Unduh Laporan
                </button>
                <a href="{{ route('lurah.laporan.print', array_filter(['rw' => $filterRw, 'jenis' => $filterJenis])) }}" target="_blank" class="flex items-center gap-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    Cetak Laporan
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-t">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">No.</th>
                        <th class="text-left px-4 py-3 text-gray-600">NIK</th>
                        <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Periode</th>
                        <th class="text-left px-4 py-3 text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporanList as $item)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->lansia?->nik }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->lansia?->nama }}</td>
                        <td class="px-4 py-3">{{ $item->lansia?->usia }}</td>
                        <td class="px-4 py-3">{{ $item->lansia?->rw }}</td>
                        <td class="px-4 py-3">{{ \App\Services\PeriodeService::label($item->periode_bulan, $item->periode_tahun) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $item->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $item->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data laporan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <p class="text-sm text-gray-500">Menampilkan {{ $laporanList->firstItem() ?? 0 }} sampai {{ $laporanList->lastItem() ?? 0 }} dari {{ $laporanList->total() }} data</p>
            {{ $laporanList->links() }}
        </div>
    </div>
</div>
