<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Services\RankingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Manajemen Bantuan Gizi')]
class BantuanManagement extends Component
{
    #[Validate('required|integer|min:1')]
    public int $kuota = 0;

    #[Validate('required|integer|min:1|max:12')]
    public int $periodeBulan;

    #[Validate('required|integer|min:2020')]
    public int $periodeTahun;

    public ?string $rankingMessage = null;

    public bool $rankingDone = false;

    public string $search = '';

    public function mount(): void
    {
        $this->periodeBulan = now()->month;
        $this->periodeTahun = now()->year;

        $existing = cache()->get("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}");
        if ($existing) {
            $this->kuota = $existing['kuota'];
        }
    }

    public function simpanKuota(): void
    {
        $this->validateOnly('kuota');
        $this->validateOnly('periodeBulan');
        $this->validateOnly('periodeTahun');

        cache()->put("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}", [
            'kuota' => $this->kuota,
            'periode_bulan' => $this->periodeBulan,
            'periode_tahun' => $this->periodeTahun,
        ]);

        $this->dispatch('notify', message: 'Kuota berhasil disimpan.');
    }

    public function jalankanRanking(RankingService $rankingService): void
    {
        $kuotaData = cache()->get("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}");
        $kuota = $kuotaData['kuota'] ?? 999;

        $result = $rankingService->rank($this->periodeBulan, $this->periodeTahun, $kuota);

        $this->rankingMessage = "Ranking selesai. {$result['total_penerima']} dari {$result['total_diproses']} lansia terpilih sebagai penerima bantuan.";
        $this->rankingDone = true;
    }

    public function render()
    {
        $query = BantuanGizi::with('lansia')
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->orderByDesc('skor_ranking');

        if ($this->search) {
            $query->whereHas('lansia', function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        $hasilRanking = $query->get();

        return view('livewire.admin.bantuan-management', compact('hasilRanking'));
    }
}
