<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
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

    public function mount(): void
    {
        $this->periodeBulan = now()->month;
        $this->periodeTahun = now()->year;
    }

    public function approve(int $bantuanId): void
    {
        BantuanGizi::findOrFail($bantuanId)->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
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

    public function render()
    {
        $query = BantuanGizi::with(['lansia', 'approver'])
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->orderByDesc('skor_ranking');

        $totalPenerima = (clone $query)->where('status_penerima', 'penerima')->count();
        $totalApproved = (clone $query)->where('status_penerima', 'penerima')->whereNotNull('approved_at')->count();
        $hasPending = (clone $query)->where('status_penerima', 'penerima')->whereNull('approved_at')->exists();

        return view('livewire.lurah.approval-table', [
            'bantuanList' => $query->paginate(20),
            'totalPenerima' => $totalPenerima,
            'totalApproved' => $totalApproved,
            'hasPending' => $hasPending,
        ]);
    }
}
