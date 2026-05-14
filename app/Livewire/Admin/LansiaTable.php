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
        $query = Lansia::with(['pemeriksaan' => fn ($q) => $q->latest('tanggal_periksa')->limit(1), 'pendataan' => fn ($q) => $q->latest()]);

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
            $query->whereHas('pemeriksaan', function ($q) {
                $q->where('hasil_periksa', $this->filterKondisi)
                    ->whereIn('pemeriksaan_id', function ($sub) {
                        $sub->selectRaw('MAX(pemeriksaan_id)')->from('pemeriksaan_kesehatan')->groupBy('lansia_id');
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
