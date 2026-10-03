<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiAuditee extends Model
{
    use HasFactory;

    protected $table = 'lokasi_auditee';
    protected $primaryKey = 'id_lokasi_auditee';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'lokasi_auditee',
    ];

    public function auditees(): HasMany
    {
        return $this->hasMany(Auditee::class, 'id_lokasi_auditee', 'id_lokasi_auditee');
    }
}
