<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiDetailTemuan extends Model
{
    use HasFactory;

    protected $table = 'smki_detail_temuans';
    protected $primaryKey = 'id_detail_temuan';

    protected $fillable = [
        'id_laporan_audit',
        'tanggal_audit',
        'id_kategori',
        'kategori_temuan',
        'klausul_annex',
        'deskripsi_temuan',
        'rekomendasi',
    ];

    protected $casts = [
        'tanggal_audit' => 'date:Y-m-d',
    ];

    public function laporanAudit()
    {
        return $this->belongsTo(SmkiLaporanAudit::class, 'id_laporan_audit', 'id_laporan_audit');
    }

    public function kategoriTemuan()
    {
        return $this->belongsTo(SmkiKategoriTemuan::class, 'id_kategori', 'id_kategori');
    }
}
