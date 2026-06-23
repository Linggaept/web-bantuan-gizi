<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.lurah')]
#[Title('Approval Bantuan Gizi')]
class ApprovalTable extends Component
{
    use WithPagination;

    public int $periodeBulan;

    public int $periodeTahun;

    public string $search = '';

    public string $filterRw = '';

    public string $filterStatus = '';

    public string $filterApproval = '';

    public function mount(): void
    {
        $periode = \App\Services\PeriodeService::current();
        $this->periodeBulan = $periode['bulan'];
        $this->periodeTahun = $periode['tahun'];
    }

    public function approve(int $bantuanId): void
    {
        BantuanGizi::findOrFail($bantuanId)->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function tolak(int $bantuanId): void
    {
        BantuanGizi::findOrFail($bantuanId)->update([
            'status_penerima' => 'ditolak',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approveAll(): void
    {
        BantuanGizi::where('status_penerima', 'penerima')
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->whereNull('approved_at')
            ->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
    }

    public function updatingPeriodeBulan(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodeTahun(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRw(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterApproval(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BantuanGizi::with(['lansia', 'approver'])
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->orderByDesc('skor_ranking');

        if ($this->search) {
            $query->whereHas('lansia', function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterRw) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $this->filterRw));
        }

        if ($this->filterStatus) {
            $query->where('status_penerima', $this->filterStatus);
        }

        if ($this->filterApproval === 'approved') {
            $query->whereNotNull('approved_at');
        } elseif ($this->filterApproval === 'pending') {
            $query->where('status_penerima', 'penerima')->whereNull('approved_at');
        }

        $baseQuery = BantuanGizi::where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun);

        $totalPenerima = (clone $baseQuery)->where('status_penerima', 'penerima')->count();
        $totalApproved = (clone $baseQuery)->where('status_penerima', 'penerima')->whereNotNull('approved_at')->count();
        $hasPending = (clone $baseQuery)->where('status_penerima', 'penerima')->whereNull('approved_at')->exists();
        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.lurah.approval-table', [
            'bantuanList' => $query->paginate(20),
            'totalPenerima' => $totalPenerima,
            'totalApproved' => $totalApproved,
            'hasPending' => $hasPending,
            'rwOptions' => $rwOptions,
        ]);
    }
}
