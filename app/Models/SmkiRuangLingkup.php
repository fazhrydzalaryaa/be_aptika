<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmkiRuangLingkup extends Model
{
    protected $table = 'smki_ruang_lingkups';

    protected $fillable = ['kategori_ruang_lingkup_id', 'nama_ruang_lingkup'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(SmkiKategoriRuangLingkup::class, 'kategori_ruang_lingkup_id');
    }
}
