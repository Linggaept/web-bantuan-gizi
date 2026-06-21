<?php

namespace App\Models;

use Database\Factories\BantuanGiziFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BantuanGizi extends Model
{
    /** @use HasFactory<BantuanGiziFactory> */
    use HasFactory;

    protected $table = 'bantuan_gizi';

    protected $primaryKey = 'bantuan_id';

    protected $fillable = ['lansia_id', 'periode_bulan', 'periode_tahun', 'skor_ranking', 'status_penerima', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'skor_ranking' => 'float',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
