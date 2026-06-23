<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Kuota Form --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-medium text-gray-800 mb-4">Pengaturan Kuota Bantuan</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <div class="flex gap-2">
                        <select wire:model="periodeBulan" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="1">Q1 (Jan–Mar)</option>
                            <option value="4">Q2 (Apr–Jun)</option>
                            <option value="7">Q3 (Jul–Sep)</option>
                            <option value="10">Q4 (Okt–Des)</option>
                        </select>
                        <input wire:model="periodeTahun" type="number" min="2020" class="w-24 border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jumlah Maksimal Penerima</label>
                    <input wire:model="kuota" type="number" min="1" placeholder="Masukkan kuota" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('kuota') border-red-500 @enderror">
                    @error('kuota')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button wire:click="simpanKuota" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">
                    Simpan Kuota
                </button>
            </div>
        </div>

        {{-- Ranking Trigger --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-medium text-gray-800 mb-4">Ranking Otomatis Penerima Bantuan</h3>
            <p class="text-sm text-gray-500 mb-4">Sistem akan menranking lansia berdasarkan usia (60%) dan kondisi kesehatan (40%). Lansia dengan skor tertinggi diprioritaskan.</p>

            @if($rankingMessage)
            <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                <p class="text-green-700 text-sm">{{ $rankingMessage }}</p>
            </div>
            @endif

            <button wire:click="jalankanRanking" wire:loading.attr="disabled" class="w-full bg-green-600 text-white py-2 rounded text-sm hover:bg-green-700 disabled:opacity-50">
                <span wire:loading.remove>Jalankan Ranking Otomatis</span>
                <span wire:loading>Memproses ranking...</span>
            </button>
        </div>
    </div>

    {{-- Trend Kesehatan per Periode --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6" wire:ignore>
        <h3 class="font-medium text-gray-800 mb-4">Trend Kesehatan Lansia per Periode</h3>
        <div
            x-data="{}"
            x-init="
                new Chart($refs.trendCanvas, {
                    type: 'line',
                    data: {
                        labels: {{ json_encode($trendLabels) }},
                        datasets: [
                            {
                                label: 'Sehat',
                                data: {{ json_encode($trendSehat) }},
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22,163,74,0.1)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Sakit',
                                data: {{ json_encode($trendSakit) }},
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.1)',
                                tension: 0.3,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                })
            "
        >
            <canvas x-ref="trendCanvas" height="120"></canvas>
        </div>
    </div>

    {{-- Hasil Ranking --}}
    @if($hasilRanking->count() > 0 || $search)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-medium text-gray-800">Hasil Ranking Penerima Bantuan — {{ \Carbon\Carbon::create()->month($periodeBulan)->translatedFormat('F') }} {{ $periodeTahun }}</h3>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIK..." class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-64 focus:outline-none focus:ring-1 focus:ring-blue-400">
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600">No.</th>
                    <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                    <th class="text-left px-4 py-3 text-gray-600">RW</th>
                    <th class="text-left px-4 py-3 text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hasilRanking as $bantuan)
                <tr class="border-b last:border-0">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium">{{ $bantuan->lansia?->nama }}</td>
                    <td class="px-4 py-3">{{ $bantuan->lansia?->usia }}</td>
                    <td class="px-4 py-3">{{ $bantuan->lansia?->rw }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $bantuan->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $bantuan->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada hasil cocok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>
