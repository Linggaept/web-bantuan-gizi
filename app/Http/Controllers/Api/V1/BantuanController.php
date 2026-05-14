<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Models\BantuanGizi;
use App\Services\RankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BantuanController extends Controller
{
    public function __construct(public RankingService $rankingService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        if ($request->filled('status_penerima')) {
            $query->where('status_penerima', $request->status_penerima);
        }

        return BantuanResource::collection($query->paginate(15));
    }

    public function setKuota(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kuota' => ['required', 'integer', 'min:1'],
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$validated['periode_bulan']}_{$validated['periode_tahun']}";
        cache()->put($key, $validated);

        return response()->json(['data' => $validated]);
    }

    public function getKuota(Request $request): JsonResponse
    {
        $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$request->periode_bulan}_{$request->periode_tahun}";
        $kuota = cache()->get($key);

        if (! $kuota) {
            return response()->json(['message' => 'Kuota belum diatur.'], 404);
        }

        return response()->json(['data' => $kuota]);
    }

    public function ranking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$validated['periode_bulan']}_{$validated['periode_tahun']}";
        $kuotaData = cache()->get($key);

        $kuota = $kuotaData['kuota'] ?? 999;

        $result = $this->rankingService->rank(
            $validated['periode_bulan'],
            $validated['periode_tahun'],
            $kuota
        );

        return response()->json(['data' => $result]);
    }

    public function approve(Request $request, int $bantuan): JsonResponse
    {
        $model = BantuanGizi::findOrFail($bantuan);

        $model->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return (new BantuanResource($model))->response();
    }
}
