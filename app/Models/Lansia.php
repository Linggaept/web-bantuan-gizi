<?php

namespace App\Models;

use Database\Factories\LansiaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lansia extends Model
{
    /** @use HasFactory<LansiaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'lansia';

    protected $primaryKey = 'lansia_id';

    protected $fillable = ['nik', 'nama', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'rt', 'rw', 'foto_ktp', 'created_by'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanKesehatan::class, 'lansia_id', 'lansia_id');
    }

    public function pendataan()
    {
        return $this->hasMany(Pendataan::class, 'lansia_id', 'lansia_id');
    }

    public function bantuanGizi()
    {
        return $this->hasMany(BantuanGizi::class, 'lansia_id', 'lansia_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUsiaAttribute(): int
    {
        return $this->tanggal_lahir->age;
    }
}
