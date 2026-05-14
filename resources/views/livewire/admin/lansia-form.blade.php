<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">{{ $id ? 'Edit Data Lansia' : 'Tambah Data Lansia' }}</h2>

        <form wire:submit="simpan" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lansia</label>
                <input wire:model="nama" type="text" placeholder="Nama Lansia" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror">
                @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RW</label>
                <select wire:model="rw" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('rw') border-red-500 @enderror">
                    <option value="">Pilih RW</option>
                    @foreach(['01','02','03','04','05','06','07','08','09','10'] as $r)
                        <option value="{{ $r }}">RW {{ $r }}</option>
                    @endforeach
                </select>
                @error('rw')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input wire:model="nik" type="text" placeholder="16 digit NIK" maxlength="16" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('nik') border-red-500 @enderror">
                @error('nik')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto KTP Lansia</label>
                <input wire:model="foto_ktp" type="file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700">
                @if($lansia?->foto_ktp)
                    <img src="{{ asset('storage/' . $lansia->foto_ktp) }}" alt="KTP" class="mt-2 w-32 h-20 object-cover rounded border">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input wire:model="tanggal_lahir" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('tanggal_lahir') border-red-500 @enderror">
                @error('tanggal_lahir')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="jenis_kelamin" type="radio" value="L"> Pria
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="jenis_kelamin" type="radio" value="P"> Wanita
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Kesehatan</label>
                <select wire:model="kondisi_kesehatan" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    <option value="">Pilih kondisi kesehatan</option>
                    <option value="baik">Sehat / Baik</option>
                    <option value="sedang">Sakit Ringan / Sedang</option>
                    <option value="buruk">Sakit / Buruk</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea wire:model="alamat" placeholder="Alamat lengkap" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('dashboard.lansia') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
