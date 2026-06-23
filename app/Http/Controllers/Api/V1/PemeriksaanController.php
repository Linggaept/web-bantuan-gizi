<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemeriksaanResource;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Services\HealthClassifier;
use App\Services\PeriodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PemeriksaanController extends Controller
{
    public function index(int $lansia): AnonymousResourceCollection
    {
        $model = Lansia::findOrFail($lansia);

        return PemeriksaanResource::collection(
            $model->pemeriksaan()->orderByDesc('tanggal_periksa')->get()
        );
    }

    public function store(Request $request, int $lansia): JsonResponse
    {
        $lansiaModel = Lansia::findOrFail($lansia);

        $validated = $request->validate([
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'catatan' => ['nullable', 'string'],
        ]);

        $periode = PeriodeService::current();

        $exists = PemeriksaanKesehatan::where('lansia_id', $lansia)
            ->where('periode_bulan', $periode['bulan'])
            ->where('periode_tahun', $periode['tahun'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Sudah ada pemeriksaan untuk periode ini',
            ], 422);
        }

        $hasilPeriksa = HealthClassifier::classify(
            $validated['berat_badan'] ?? null,
            $lansiaModel->tinggi_badan,
            $validated['tekanan_darah'] ?? null
        );

        $data = array_merge($validated, [
            'lansia_id' => $lansia,
            'tanggal_periksa' => now()->toDateString(),
            'hasil_periksa' => $hasilPeriksa,
            'periode_bulan' => $periode['bulan'],
            'periode_tahun' => $periode['tahun'],
        ]);

        $periksa = PemeriksaanKesehatan::create($data);

        return (new PemeriksaanResource($periksa))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $pemeriksaan): JsonResponse
    {
        $model = PemeriksaanKesehatan::findOrFail($pemeriksaan);

        return (new PemeriksaanResource($model))->response();
    }

    public function update(Request $request, int $pemeriksaan): JsonResponse
    {
        $model = PemeriksaanKesehatan::findOrFail($pemeriksaan);

        $validated = $request->validate([
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'catatan' => ['nullable', 'string'],
        ]);

        $lansiaModel = Lansia::findOrFail($model->lansia_id);

        $hasilPeriksa = HealthClassifier::classify(
            $validated['berat_badan'] ?? $model->berat_badan,
            $lansiaModel->tinggi_badan,
            $validated['tekanan_darah'] ?? $model->tekanan_darah
        );

        $validated['hasil_periksa'] = $hasilPeriksa;

        $model->update($validated);

        return (new PemeriksaanResource($model))->response();
    }

    public function monitoring(int $lansia): JsonResponse
    {
        Lansia::findOrFail($lansia);

        $records = PemeriksaanKesehatan::where('lansia_id', $lansia)
            ->orderBy('periode_tahun')
            ->orderBy('periode_bulan')
            ->get();

        $result = [];
        $prevHasil = null;

        foreach ($records as $item) {
            $hasilPeriksa = $item->hasil_periksa;

            if ($prevHasil === null) {
                $trend = 'tetap';
            } elseif ($hasilPeriksa === 'sehat' && $prevHasil === 'sakit') {
                $trend = 'membaik';
            } elseif ($hasilPeriksa === 'sakit' && $prevHasil === 'sehat') {
                $trend = 'menurun';
            } else {
                $trend = 'tetap';
            }

            $result[] = [
                'periode_bulan' => $item->periode_bulan,
                'periode_tahun' => $item->periode_tahun,
                'label' => PeriodeService::label($item->periode_bulan, $item->periode_tahun),
                'tanggal_periksa' => $item->tanggal_periksa?->toDateString(),
                'berat_badan' => $item->berat_badan,
                'tekanan_darah' => $item->tekanan_darah,
                'hasil_periksa' => $hasilPeriksa,
                'catatan' => $item->catatan,
                'trend' => $trend,
            ];

            $prevHasil = $hasilPeriksa;
        }

        return response()->json(['data' => $result]);
    }
}
