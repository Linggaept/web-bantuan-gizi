<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendataanResource;
use App\Models\Pendataan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PendataanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PendataanResource::collection(
            Pendataan::with('lansia')->paginate(15)
        );
    }

    public function verifikasi(Request $request, int $pendataan): JsonResponse
    {
        $model = Pendataan::findOrFail($pendataan);

        $request->validate([
            'status_verifikasi' => ['required', 'in:terverifikasi,ditolak'],
        ]);

        $model->update([
            'status_verifikasi' => $request->status_verifikasi,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return (new PendataanResource($model))->response();
    }
}
