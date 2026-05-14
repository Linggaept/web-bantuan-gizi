<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BantuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bantuan_id' => $this->bantuan_id,
            'lansia_id' => $this->lansia_id,
            'periode_bulan' => $this->periode_bulan,
            'periode_tahun' => $this->periode_tahun,
            'skor_ranking' => $this->skor_ranking,
            'status_penerima' => $this->status_penerima,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'lansia' => new LansiaResource($this->whenLoaded('lansia')),
        ];
    }
}
