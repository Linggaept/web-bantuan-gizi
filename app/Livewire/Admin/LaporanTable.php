<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Laporan Bantuan Gizi')]
class LaporanTable extends Component
{
    use WithPagination;

    public string $filterRw = '';

    public string $filterJenis = '';

    public string $filterKondisi = '';

    public string $filterPeriode = '';

    public string $filterTahun = '';

    public int $filterLimit = 15;

    public function download(): StreamedResponse
    {
        $query = $this->buildQuery();
        $data = $query->get();

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
        $query = BantuanGizi::with('lansia');

        if ($this->filterRw) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $this->filterRw));
        }

        if ($this->filterJenis && $this->filterJenis !== 'semua') {
            $query->where('status_penerima', $this->filterJenis);
        }

        if ($this->filterPeriode !== '') {
            $query->where('periode_bulan', (int) $this->filterPeriode);
        }

        if ($this->filterTahun !== '') {
            $query->where('periode_tahun', (int) $this->filterTahun);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildQuery();
        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.admin.laporan-table', [
            'laporanList' => $query->paginate($this->filterLimit),
            'rwOptions' => $rwOptions,
        ]);
    }
}
