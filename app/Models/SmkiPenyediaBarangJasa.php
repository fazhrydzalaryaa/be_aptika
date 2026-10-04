<?php

namespace App\Models;

use App\Traits\BelongsToBidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmkiPenyediaBarangJasa extends Model
{
    use BelongsToBidang, SoftDeletes;

    protected $table = 'smki_penyedia_barang_jasas';

    protected $fillable = [
        'bidang_id',
        'user_id',
        'nama_perusahaan',
        'alamat',
        'no_kontrak',
        'kategori_ruang_lingkup_id',
        'ruang_lingkup_id',
        'contact_person',
        'no_telp',
        'berita_acara',
    ];

    public function kategoriRuangLingkup(): BelongsTo
    {
        return $this->belongsTo(SmkiKategoriRuangLingkup::class, 'kategori_ruang_lingkup_id');
    }

    public function ruangLingkup(): BelongsTo
    {
        return $this->belongsTo(SmkiRuangLingkup::class, 'ruang_lingkup_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
