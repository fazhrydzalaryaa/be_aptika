<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlatAngkutan extends Model
{
    use HasFactory;

    protected $table = 'alat_angkutans';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];
}
