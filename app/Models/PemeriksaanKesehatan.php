<?php

namespace App\Models;

use Database\Factories\PemeriksaanKesehatanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lansia_id', 'tanggal_periksa', 'berat_badan', 'tekanan_darah', 'hasil_periksa', 'catatan'])]
class PemeriksaanKesehatan extends Model
{
    /** @use HasFactory<PemeriksaanKesehatanFactory> */
    use HasFactory;

    protected $primaryKey = 'pemeriksaan_id';

    protected function casts(): array
    {
        return [
            'tanggal_periksa' => 'date',
            'berat_badan' => 'float',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }
}
