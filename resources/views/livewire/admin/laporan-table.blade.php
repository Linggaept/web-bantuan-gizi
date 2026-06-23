<div>
    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Cetak Laporan Pendataan Lansia</h2>

        {{-- Filter Row --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterJenis" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto">
                <option value="semua">Semua</option>
                <option value="penerima">Penerima</option>
                <option value="tidak_penerima">Tidak Penerima</option>
            </select>

            <select wire:model.live="filterPeriode" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto">
                <option value="">Semua Periode</option>
                <option value="1">Q1 (Jan–Mar)</option>
                <option value="4">Q2 (Apr–Jun)</option>
                <option value="7">Q3 (Jul–Sep)</option>
                <option value="10">Q4 (Okt–Des)</option>
            </select>

            <input wire:model.live.debounce.500ms="filterTahun" type="number" placeholder="Tahun" min="2020" class="border border-gray-300 rounded px-3 py-2 text-sm w-24">

            <div class="flex flex-wrap gap-2 ml-auto">
                <button wire:click="download" class="flex items-center gap-1 px-3 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">
                    Unduh Laporan
                </button>
                <a href="{{ route('dashboard.laporan.print', array_filter(['rw' => $filterRw, 'jenis' => $filterJenis, 'periode' => $filterPeriode, 'tahun' => $filterTahun])) }}" target="_blank" class="flex items-center gap-1 px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    Cetak Laporan
                </a>
            </div>
        </div>

        {{-- Table --}}
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
                <tr class="border-b last:border-0">
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

        <div class="mt-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Menampilkan {{ $laporanList->firstItem() ?? 0 }} sampai {{ $laporanList->lastItem() ?? 0 }} dari {{ $laporanList->total() }} data</p>
            {{ $laporanList->links() }}
        </div>
    </div>
</div>
