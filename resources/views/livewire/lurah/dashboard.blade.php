<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
                <p class="text-sm text-gray-500">Penerima Bantuan</p>
                <p class="text-3xl font-bold text-green-600">{{ $totalPenerima }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Approval</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $pendingApproval }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">RW {{ $rwTerbanyak?->rw ?? '-' }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ $rwTerbanyak?->total ?? 0 }}</p>
                <p class="text-xs text-gray-400">Lansia Terbanyak</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Chart + Kondisi --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
        <div class="col-span-1 lg:col-span-3 bg-white rounded-lg shadow-sm p-4">
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
                                backgroundColor: '#16a34a',
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

        <div class="col-span-1 lg:col-span-2 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Kondisi Kesehatan</h3>
            <div class="flex flex-col gap-4 justify-center h-40">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Sehat</span>
                    <span class="text-2xl font-bold text-green-600">{{ $kondisiSehat }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php $total = $kondisiSehat + $kondisiSakit ?: 1; @endphp
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ round($kondisiSehat / $total * 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Sakit / Ringan</span>
                    <span class="text-2xl font-bold text-red-500">{{ $kondisiSakit }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-red-400 h-2 rounded-full" style="width: {{ round($kondisiSakit / $total * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribusi Per RW --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b">
            <h3 class="font-medium text-gray-800 text-sm">Distribusi Penerima Bantuan per RW</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Total Lansia</th>
                        <th class="text-left px-4 py-3 text-gray-600">Penerima</th>
                        <th class="text-left px-4 py-3 text-gray-600">Tidak Penerima</th>
                        <th class="text-left px-4 py-3 text-gray-600">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiPerRw as $row)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-3 font-medium">RW {{ $row->rw }}</td>
                        <td class="px-4 py-3">{{ $row->total_lansia }}</td>
                        <td class="px-4 py-3 text-green-600 font-medium">{{ $row->total_penerima }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $row->tidak_penerima }}</td>
                        <td class="px-4 py-3 text-blue-600 font-medium">{{ $row->total_approved }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
