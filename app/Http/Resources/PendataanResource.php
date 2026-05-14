<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendataanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pendataan_id' => $this->pendataan_id,
            'lansia_id' => $this->lansia_id,
            'user_id' => $this->user_id,
            'status_verifikasi' => $this->status_verifikasi,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'tanggal_input' => $this->tanggal_input?->toDateString(),
            'lansia' => new LansiaResource($this->whenLoaded('lansia')),
        ];
    }
}
