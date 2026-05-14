<?php

namespace App\Livewire\Admin;

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Edit Data Lansia')]
class LansiaForm extends Component
{
    use WithFileUploads;

    public ?int $id = null;

    public ?Lansia $lansia = null;

    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('required|string|max:5')]
    public string $rw = '';

    #[Validate('required|string|size:16')]
    public string $nik = '';

    #[Validate('required|date')]
    public string $tanggal_lahir = '';

    #[Validate('required|in:L,P')]
    public string $jenis_kelamin = 'L';

    #[Validate('required|string')]
    public string $alamat = '';

    #[Validate('nullable|string|max:5')]
    public string $rt = '';

    #[Validate('nullable|in:baik,sedang,buruk')]
    public string $kondisi_kesehatan = '';

    public $foto_ktp = null;

    public function mount(string $id): void
    {
        if ($id !== 'new') {
            $this->id = (int) $id;
            $this->lansia = Lansia::findOrFail($this->id);
            $this->nama = $this->lansia->nama;
            $this->rw = $this->lansia->rw;
            $this->nik = $this->lansia->nik;
            $this->tanggal_lahir = $this->lansia->tanggal_lahir->toDateString();
            $this->jenis_kelamin = $this->lansia->jenis_kelamin;
            $this->alamat = $this->lansia->alamat;
            $this->rt = $this->lansia->rt ?? '';

            $latestPeriksa = $this->lansia->pemeriksaan()->latest('tanggal_periksa')->first();
            $this->kondisi_kesehatan = $latestPeriksa?->hasil_periksa ?? '';
        }
    }

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'rw' => $this->rw,
            'nik' => $this->nik,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'rt' => $this->rt ?: null,
        ];

        if ($this->id) {
            $this->lansia->update($data);
            $lansia = $this->lansia;
        } else {
            $data['created_by'] = auth()->id();
            $lansia = Lansia::create($data);
        }

        if ($this->foto_ktp) {
            $path = $this->foto_ktp->store('foto-ktp', 'public');
            $lansia->update(['foto_ktp' => $path]);
        }

        if ($this->kondisi_kesehatan) {
            PemeriksaanKesehatan::create([
                'lansia_id' => $lansia->lansia_id,
                'tanggal_periksa' => now()->toDateString(),
                'hasil_periksa' => $this->kondisi_kesehatan,
            ]);
        }

        $this->redirect(route('dashboard.lansia'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.lansia-form');
    }
}
