<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemeriksaanResource;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
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
        Lansia::findOrFail($lansia);

        $validated = $request->validate([
            'tanggal_periksa' => ['required', 'date'],
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'hasil_periksa' => ['required', 'in:baik,sedang,buruk'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['lansia_id'] = $lansia;
        $periksa = PemeriksaanKesehatan::create($validated);

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
            'tanggal_periksa' => ['sometimes', 'date'],
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'hasil_periksa' => ['sometimes', 'in:baik,sedang,buruk'],
            'catatan' => ['nullable', 'string'],
        ]);

        $model->update($validated);

        return (new PemeriksaanResource($model))->response();
    }
}
