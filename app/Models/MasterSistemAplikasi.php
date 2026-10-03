<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSistemAplikasi extends Model
{
    protected $table = 'master_sistem_aplikasi';

    protected $primaryKey = 'id_sistem_aplikasi';

    protected $fillable = ['nama_sistem', 'deskripsi'];
}
