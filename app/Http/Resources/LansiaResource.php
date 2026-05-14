<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LansiaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'lansia_id' => $this->lansia_id,
            'nik' => $this->nik,
            'nama' => $this->nama,
            'tanggal_lahir' => $this->tanggal_lahir?->toDateString(),
            'usia' => $this->usia,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'foto_ktp' => $this->foto_ktp ? asset('storage/'.$this->foto_ktp) : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
