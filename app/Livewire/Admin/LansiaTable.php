<?php

namespace App\Livewire\Admin;

use App\Models\Lansia;
use App\Models\Pendataan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Data Lansia')]
class LansiaTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterRw = '';

    public string $filterKondisi = '';

    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteLansia(int $lansiaId): void
    {
        Lansia::findOrFail($lansiaId)->delete();
        $this->dispatch('lansia-deleted');
    }

    public function verifikasiLansia(int $pendataanId, string $status): void
    {
        $pendataan = Pendataan::findOrFail($pendataanId);
        $pendataan->update([
            'status_verifikasi' => $status,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
    }

    public function render()
    {
        $query = Lansia::with(['pemeriksaan' => fn ($q) => $q->orderByDesc('tanggal_periksa')->orderByDesc('pemeriksaan_id')->limit(1), 'pendataan' => fn ($q) => $q->latest()]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterRw) {
            $query->where('rw', $this->filterRw);
        }

        if ($this->filterKondisi) {
            $kondisi = $this->filterKondisi;
            $query->where(function ($q) use ($kondisi) {
                // Has pemeriksaan: latest pemeriksaan.hasil_periksa must match
                $q->whereHas('pemeriksaan', function ($sub) use ($kondisi) {
                    $sub->where('hasil_periksa', $kondisi)
                        ->whereRaw('pemeriksaan_id = (SELECT p2.pemeriksaan_id FROM pemeriksaan_kesehatan p2 WHERE p2.lansia_id = pemeriksaan_kesehatan.lansia_id ORDER BY p2.tanggal_periksa DESC, p2.pemeriksaan_id DESC LIMIT 1)');
                })
                // OR no pemeriksaan at all AND lansia.kondisi_kesehatan matches
                ->orWhere(function ($sub) use ($kondisi) {
                    $sub->whereDoesntHave('pemeriksaan')
                        ->where('kondisi_kesehatan', $kondisi);
                });
            });
        }

        if ($this->filterStatus) {
            $query->whereHas('pendataan', fn ($q) => $q->where('status_verifikasi', $this->filterStatus));
        }

        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.admin.lansia-table', [
            'lansiaList' => $query->paginate(15),
            'rwOptions' => $rwOptions,
        ]);
    }
}
