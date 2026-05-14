<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Models\BantuanGizi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        $limit = min((int) ($request->limit ?? 15), 200);

        return BantuanResource::collection($query->paginate($limit));
    }

    public function download(Request $request): StreamedResponse
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        $filename = 'laporan-bantuan-gizi-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['No', 'NIK', 'Nama', 'Usia', 'RW', 'Periode', 'Status', 'Skor']);

            $no = 1;
            $query->chunk(200, function ($items) use ($handle, &$no) {
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $no++,
                        $item->lansia?->nik,
                        $item->lansia?->nama,
                        $item->lansia?->usia,
                        $item->lansia?->rw,
                        "{$item->periode_bulan}/{$item->periode_tahun}",
                        $item->status_penerima,
                        $item->skor_ranking,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
