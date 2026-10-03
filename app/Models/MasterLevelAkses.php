<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLevelAkses extends Model
{
    protected $table = 'master_level_akses';

    protected $primaryKey = 'id_level_akses';

    protected $fillable = ['nama_level'];
}
