<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmkiLaporanAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'smki_laporan_audits';
    protected $primaryKey = 'id_laporan_audit';

    protected $fillable = [
        'nomor_laporan',
        'temuan_major',
        'temuan_minor',
        'ofi',
        'id_unit_kerja',
        'nama_unit_kerja',
        'id_auditor',
        'auditor',
        'id_auditee',
        'auditee',
        'tanggal_audit',
        'id_kategori',
        'kategori',
        'klausul_annex',
        'latar_belakang',
        'tujuan',
        'ruang_lingkup',
        'id_status',
        'status',
        'bidang_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal_audit' => 'date:Y-m-d',
        'temuan_major'  => 'integer',
        'temuan_minor'  => 'integer',
        'ofi'           => 'integer',
    ];

    public function unitKerja()
    {
        return $this->belongsTo(SmkiUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function kategoriTemuan()
    {
        return $this->belongsTo(SmkiKategoriTemuan::class, 'id_kategori', 'id_kategori');
    }

    public function detailTemuans()
    {
        return $this->hasMany(SmkiDetailTemuan::class, 'id_laporan_audit', 'id_laporan_audit');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
