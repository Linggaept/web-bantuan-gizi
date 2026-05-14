<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Http\Resources\LansiaResource;
use App\Models\Lansia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LansiaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lansia::query();

        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%'.$request->nama.'%');
        }

        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }

        if ($request->filled('kondisi_kesehatan')) {
            $query->whereHas('pemeriksaan', function ($q) use ($request) {
                $q->where('hasil_periksa', $request->kondisi_kesehatan)
                    ->whereIn('pemeriksaan_id', function ($sub) {
                        $sub->selectRaw('MAX(pemeriksaan_id)')
                            ->from('pemeriksaan_kesehatan')
                            ->groupBy('lansia_id');
                    });
            });
        }

        if ($request->filled('status_bantuan')) {
            $query->whereHas('bantuanGizi', function ($q) use ($request) {
                $q->where('status_penerima', $request->status_bantuan);
            });
        }

        return LansiaResource::collection($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'size:16', 'unique:lansia,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
        ]);

        $validated['created_by'] = $request->user()->id;

        $lansia = Lansia::create($validated);

        return (new LansiaResource($lansia))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);

        return (new LansiaResource($model))->response();
    }

    public function update(Request $request, int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);

        $validated = $request->validate([
            'nik' => ['sometimes', 'string', 'size:16', 'unique:lansia,nik,'.$model->lansia_id.',lansia_id'],
            'nama' => ['sometimes', 'string', 'max:255'],
            'tanggal_lahir' => ['sometimes', 'date'],
            'jenis_kelamin' => ['sometimes', 'in:L,P'],
            'alamat' => ['sometimes', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['sometimes', 'string', 'max:5'],
        ]);

        $model->update($validated);

        return (new LansiaResource($model))->response();
    }

    public function destroy(int $lansia): JsonResponse
    {
        Lansia::findOrFail($lansia)->delete();

        return response()->json(null, 204);
    }

    public function uploadFotoKtp(Request $request, int $lansia): JsonResponse
    {
        $request->validate([
            'foto_ktp' => ['required', 'image', 'max:2048'],
        ]);

        $model = Lansia::findOrFail($lansia);
        $path = $request->file('foto_ktp')->store('foto-ktp', 'public');
        $model->update(['foto_ktp' => $path]);

        return (new LansiaResource($model))->response();
    }

    public function statusBantuan(int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);
        $bantuan = $model->bantuanGizi()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->first();

        if (! $bantuan) {
            return response()->json(['data' => ['status_penerima' => null, 'lansia_id' => $lansia]]);
        }

        return (new BantuanResource($bantuan))->response();
    }
}
