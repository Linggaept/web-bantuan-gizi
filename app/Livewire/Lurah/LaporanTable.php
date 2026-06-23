<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.lurah')]
#[Title('Laporan Bantuan Gizi')]
class LaporanTable extends Component
{
    use WithPagination;

    public string $filterRw = '';

    public string $filterJenis = '';

    public int $filterLimit = 15;

    public function download(): StreamedResponse
    {
        $data = $this->buildQuery()->get();
        $filename = 'laporan-bantuan-gizi-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'NIK', 'Nama', 'Usia', 'RW', 'Periode', 'Status']);
            $no = 1;
            foreach ($data as $item) {
                fputcsv($handle, [
                    $no++,
                    $item->lansia?->nik,
                    $item->lansia?->nama,
                    $item->lansia?->usia,
                    $item->lansia?->rw,
                    \App\Services\PeriodeService::label($item->periode_bulan, $item->periode_tahun),
                    $item->status_penerima,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildQuery()
    {
        $query = BantuanGizi::with(['lansia', 'approver']);

        if ($this->filterRw) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $this->filterRw));
        }

        if ($this->filterJenis && $this->filterJenis !== 'semua') {
            $query->where('status_penerima', $this->filterJenis);
        }

        return $query;
    }

    public function render()
    {
        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.lurah.laporan-table', [
            'laporanList' => $this->buildQuery()->paginate($this->filterLimit),
            'rwOptions' => $rwOptions,
        ]);
    }
}
