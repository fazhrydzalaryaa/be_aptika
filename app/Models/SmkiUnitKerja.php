<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiUnitKerja extends Model
{
    use HasFactory;

    protected $table = 'smki_unit_kerjas';
    protected $primaryKey = 'id_unit_kerja';

    protected $fillable = [
        'nama_unit_kerja',
    ];

    public function laporanAudits()
    {
        return $this->hasMany(SmkiLaporanAudit::class, 'id_unit_kerja', 'id_unit_kerja');
    }
}
