<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemeriksaanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pemeriksaan_id' => $this->pemeriksaan_id,
            'lansia_id' => $this->lansia_id,
            'tanggal_periksa' => $this->tanggal_periksa?->toDateString(),
            'berat_badan' => $this->berat_badan,
            'tekanan_darah' => $this->tekanan_darah,
            'hasil_periksa' => $this->hasil_periksa,
            'periode_bulan' => $this->periode_bulan,
            'periode_tahun' => $this->periode_tahun,
            'catatan' => $this->catatan,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
