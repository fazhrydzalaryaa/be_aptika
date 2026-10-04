<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisPermohonan extends Model
{
    protected $table = 'master_jenis_permohonan';

    protected $primaryKey = 'id_jenis_permohonan';

    protected $fillable = ['nama_jenis'];
}
