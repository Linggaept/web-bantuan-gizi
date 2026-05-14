<div>
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Approval Penerima Bantuan Gizi</h2>
                @if($totalPenerima > 0)
                <p class="text-sm text-gray-500 mt-1">
                    <span class="text-green-600 font-medium">{{ $totalApproved }}</span> dari
                    <span class="font-medium">{{ $totalPenerima }}</span> penerima sudah disetujui
                </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="periodeBulan" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
                <input wire:model.live="periodeTahun" type="number" min="2020" class="w-24 border border-gray-300 rounded px-3 py-2 text-sm">

                @if($hasPending)
                <button wire:click="approveAll" wire:confirm="Setujui semua penerima periode ini?" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 flex items-center gap-1">
                    ✓ Approve Semua Penerima
                </button>
                @else
                <span class="text-sm text-green-600 font-medium bg-green-50 px-3 py-2 rounded border border-green-200">✓ Semua sudah diapprove</span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-t">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">No.</th>
                        <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Skor</th>
                        <th class="text-left px-4 py-3 text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 text-gray-600">Approval</th>
                        <th class="text-left px-4 py-3 text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bantuanList as $bantuan)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $bantuan->lansia?->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $bantuan->lansia?->usia }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $bantuan->lansia?->rw }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $bantuan->skor_ranking ? number_format($bantuan->skor_ranking, 4) : '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $bantuan->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $bantuan->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->approved_at)
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">✓ Approved</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->status_penerima === 'penerima' && !$bantuan->approved_at)
                            <button wire:click="approve({{ $bantuan->bantuan_id }})" class="text-green-600 hover:text-green-800 text-xs font-medium">
                                Approve
                            </button>
                            @elseif($bantuan->approved_at)
                            <span class="text-xs text-gray-400">{{ $bantuan->approver?->name }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data bantuan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bantuanList->links() }}
        </div>
    </div>
</div>
