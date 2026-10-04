<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiKategoriTemuan extends Model
{
    use HasFactory;

    protected $table = 'smki_kategori_temuans';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
    ];

    public function detailTemuans()
    {
        return $this->hasMany(SmkiDetailTemuan::class, 'id_kategori', 'id_kategori');
    }
}
