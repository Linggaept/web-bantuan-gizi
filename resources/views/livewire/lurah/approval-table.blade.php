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
                    <option value="1">Q1 (Jan–Mar)</option>
                    <option value="4">Q2 (Apr–Jun)</option>
                    <option value="7">Q3 (Jul–Sep)</option>
                    <option value="10">Q4 (Okt–Des)</option>
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

        <div class="flex flex-wrap gap-2 mb-4">
            <input wire:model.live="search" type="text" placeholder="Cari nama atau NIK..." class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-56 focus:outline-none focus:ring-1 focus:ring-blue-400">

            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua Status</option>
                <option value="penerima">Penerima</option>
                <option value="tidak_penerima">Tidak Penerima</option>
                <option value="ditolak">Ditolak</option>
            </select>

            <select wire:model.live="filterApproval" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua Approval</option>
                <option value="approved">Sudah Disetujui</option>
                <option value="pending">Belum Disetujui</option>
            </select>
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
                            @if($bantuan->status_penerima === 'penerima')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Penerima</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">Tidak Penerima</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->status_penerima === 'ditolak')
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Ditolak</span>
                            @elseif($bantuan->approved_at)
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">✓ Approved</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->status_penerima === 'penerima' && !$bantuan->approved_at)
                            <div class="flex items-center gap-2">
                                <button wire:click="approve({{ $bantuan->bantuan_id }})" wire:loading.attr="disabled" wire:target="approve({{ $bantuan->bantuan_id }})" class="inline-flex items-center gap-1.5 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-700 border border-green-300 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all duration-150 disabled:opacity-60">
                                    <svg wire:loading.remove wire:target="approve({{ $bantuan->bantuan_id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg wire:loading wire:target="approve({{ $bantuan->bantuan_id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    Setujui
                                </button>
                                <button wire:click="tolak({{ $bantuan->bantuan_id }})" wire:confirm="Tolak bantuan untuk {{ $bantuan->lansia?->nama }}?" wire:loading.attr="disabled" wire:target="tolak({{ $bantuan->bantuan_id }})" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-600 border border-red-300 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-all duration-150 disabled:opacity-60">
                                    <svg wire:loading.remove wire:target="tolak({{ $bantuan->bantuan_id }})" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <svg wire:loading wire:target="tolak({{ $bantuan->bantuan_id }})" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    Tolak
                                </button>
                            </div>
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
