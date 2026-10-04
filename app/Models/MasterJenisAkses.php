<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisAkses extends Model
{
    protected $table = 'master_jenis_akses';

    protected $primaryKey = 'id_jenis_akses';

    protected $fillable = ['nama_akses'];
}
