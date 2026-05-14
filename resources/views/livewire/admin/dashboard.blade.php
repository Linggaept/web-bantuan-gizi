<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Lansia</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalLansia }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">RW {{ $rwTerbanyak?->rw ?? '-' }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ $rwTerbanyak?->total ?? 0 }}</p>
                <p class="text-xs text-gray-400">Lansia</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sehat</p>
                <p class="text-3xl font-bold text-green-600">{{ $kondisiSehat }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-sm">
                {{ $kondisiSehat }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sakit/Ringan</p>
                <p class="text-3xl font-bold text-red-500">{{ $kondisiSakit }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-500 font-bold text-sm">
                {{ $kondisiSakit }}
            </div>
        </div>
    </div>

    {{-- Chart + Table Row --}}
    <div class="grid grid-cols-5 gap-4">
        {{-- Bar Chart --}}
        <div class="col-span-3 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Distribusi Usia Lansia</h3>
            <div
                x-data="{}"
                x-init="
                    new Chart($refs.canvas, {
                        type: 'bar',
                        data: {
                            labels: {{ json_encode($chartLabels) }},
                            datasets: [{
                                label: 'Jumlah Lansia',
                                data: {{ json_encode($chartData) }},
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                        }
                    })
                "
            >
                <canvas x-ref="canvas" height="200"></canvas>
            </div>
        </div>

        {{-- Latest Lansia Table --}}
        <div class="col-span-2 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Data Lansia Terbaru</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">RW</th>
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">Usia</th>
                        <th class="pb-2">Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lansiaTerbaru as $l)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $l->rw }}</td>
                        <td class="py-2">{{ $l->nama }}</td>
                        <td class="py-2">{{ $l->usia }}</td>
                        <td class="py-2">
                            @php $kondisi = $l->pemeriksaan->first()?->hasil_periksa ?? '-' @endphp
                            <span class="px-2 py-0.5 rounded text-xs {{ $kondisi === 'baik' ? 'bg-green-100 text-green-700' : ($kondisi === 'sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($kondisi) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
