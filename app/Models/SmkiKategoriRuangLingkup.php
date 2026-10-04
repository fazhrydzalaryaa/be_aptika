<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmkiKategoriRuangLingkup extends Model
{
    protected $table = 'smki_kategori_ruang_lingkups';

    protected $fillable = ['nama_kategori'];

    public function ruangLingkup(): HasMany
    {
        return $this->hasMany(SmkiRuangLingkup::class, 'kategori_ruang_lingkup_id');
    }
}
