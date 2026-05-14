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
                <button wire:click="approveAll" wire:confirm="Setujui semua penerima periode ini?" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm transition-all duration-150 disabled:opacity-60">
                    <svg wire:loading.remove wire:target="approveAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg wire:loading wire:target="approveAll" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    Approve Semua
                </button>
                @else
                <span class="inline-flex items-center gap-1.5 text-sm text-green-700 font-medium bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Semua sudah disetujui
                </span>
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
                            <button wire:click="approve({{ $bantuan->bantuan_id }})" wire:loading.attr="disabled" wire:target="approve({{ $bantuan->bantuan_id }})" class="inline-flex items-center gap-1.5 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-700 border border-green-300 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all duration-150 disabled:opacity-60">
                                <svg wire:loading.remove wire:target="approve({{ $bantuan->bantuan_id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg wire:loading wire:target="approve({{ $bantuan->bantuan_id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                Setujui
                            </button>
                            @elseif($bantuan->approved_at)
                            <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ $bantuan->approver?->name ?? 'Disetujui' }}
                            </span>
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
