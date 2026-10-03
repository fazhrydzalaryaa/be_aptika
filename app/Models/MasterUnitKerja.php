<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterUnitKerja extends Model
{
    protected $table = 'master_unit_kerja';

    protected $primaryKey = 'id_unit_kerja';

    protected $fillable = ['nama_unit'];
}
