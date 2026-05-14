<?php

namespace App\Models;

use Database\Factories\PendataanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lansia_id', 'user_id', 'status_verifikasi', 'verified_by', 'verified_at', 'tanggal_input'])]
class Pendataan extends Model
{
    /** @use HasFactory<PendataanFactory> */
    use HasFactory;

    protected $primaryKey = 'pendataan_id';

    protected function casts(): array
    {
        return [
            'tanggal_input' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
