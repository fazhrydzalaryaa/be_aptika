<?php

namespace App\Models;

use App\Traits\BelongsToBidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Rekening;
use App\Models\SpdPeserta;
use App\Models\Pegawai; // <-- Tambahan untuk memanggil model Pegawai

class DetailPerjalanan extends Model
{
    use BelongsToBidang;
    protected $table = 'detail_perjalanan';

    protected $fillable = [
        'travel_code',
        'kegiatan',
        'sub_kegiatan',
        'tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'uang_harian',
        'rekening_id',
        'alat_angkutan',
        'deskripsi',
        'status',
        'bidang_id',
        'nomor_sp',    // <-- Tambahan baru
        'nomor_visum', // <-- Tambahan baru
        'ppk_id',      // <-- Tambahan baru
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
        'uang_harian' => 'decimal:2',
    ];

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(SpdPeserta::class, 'detail_perjalanan_id')->orderBy('id');
    }

    // <-- Tambahan relasi untuk PPK
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'ppk_id');
    }
}