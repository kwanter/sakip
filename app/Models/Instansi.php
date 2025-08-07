<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instansi extends Model
{
    protected $fillable = [
        'kode_instansi',
        'nama_instansi',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_instansi',
        'nip_kepala',
        'status'
    ];

    /**
     * Relasi ke Program
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
