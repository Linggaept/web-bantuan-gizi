<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-3 flex-1">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari berdasarkan nama atau NIK..."
                class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto focus:outline-none">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterKondisi" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto focus:outline-none">
                <option value="">Semua Kondisi</option>
                <option value="baik">Sehat</option>
                <option value="sedang">Sakit Ringan</option>
                <option value="buruk">Sakit</option>
            </select>
            <select wire:model.live="filterStatus" class="border border-gray-300 rounded px-3 py-2 text-sm w-full sm:w-auto focus:outline-none">
                <option value="">Semua Status</option>
                <option value="menunggu">Menunggu</option>
                <option value="terverifikasi">Terverifikasi</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">No.</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Usia</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">RW</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kondisi</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status Verifikasi</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lansiaList as $lansia)
                @php
                    $kondisi = $lansia->pemeriksaan->first()?->hasil_periksa ?? null;
                    $pendataan = $lansia->pendataan->first();
                    $statusVerif = $pendataan?->status_verifikasi ?? 'belum_ada';
                @endphp
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $lansia->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lansia->usia }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lansia->rw }}</td>
                    <td class="px-4 py-3">
                        @if($kondisi)
                        <span class="px-2 py-1 rounded-full text-xs {{ $kondisi === 'baik' ? 'bg-green-100 text-green-700' : ($kondisi === 'sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ $kondisi === 'baik' ? 'Sehat' : ($kondisi === 'sedang' ? 'Sakit Ringan' : 'Sakit') }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">Belum periksa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($statusVerif === 'terverifikasi')
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Terverifikasi</span>
                        @elseif($statusVerif === 'ditolak')
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Ditolak</span>
                        @elseif($statusVerif === 'menunggu')
                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Menunggu</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-500">Belum ada</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2" x-data="{ confirmDelete: false }">
                            @if($pendataan && $statusVerif === 'menunggu')
                            <button wire:click="verifikasiLansia({{ $pendataan->pendataan_id }}, 'terverifikasi')" class="text-green-500 hover:text-green-700 text-xs">Verifikasi</button>
                            <button wire:click="verifikasiLansia({{ $pendataan->pendataan_id }}, 'ditolak')" class="text-red-500 hover:text-red-700 text-xs">Tolak</button>
                            @endif

                            <button @click="confirmDelete = true" class="text-red-400 hover:text-red-600 text-xs">Hapus</button>

                            <div x-show="confirmDelete" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" style="display:none">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <p class="text-sm mb-4">Hapus lansia <strong>{{ $lansia->nama }}</strong>?</p>
                                    <div class="flex gap-3">
                                        <button @click="confirmDelete = false" class="px-3 py-1 text-sm border rounded">Batal</button>
                                        <button wire:click="deleteLansia({{ $lansia->lansia_id }})" @click="confirmDelete = false" class="px-3 py-1 text-sm bg-red-500 text-white rounded">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data lansia.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $lansiaList->links() }}
        </div>
    </div>
</div>
