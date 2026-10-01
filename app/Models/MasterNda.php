<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterNda extends Model
{
    use HasFactory;

    protected $table = 'master_ndas';

    protected $fillable = [
        'judul',
        'nama_pihak_pertama',
        'nip_pihak_pertama',
        'jabatan_pihak_pertama',
        'instansi_pihak_pertama',
        'klausul_perjanjian',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

