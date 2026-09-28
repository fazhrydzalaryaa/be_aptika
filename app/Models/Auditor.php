<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditor extends Model
{
    use HasFactory;

    protected $table = 'auditor';
    protected $primaryKey = 'id_auditor';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_auditor',
        'nip_auditor',
    ];

    public function detailAudits(): HasMany
    {
        return $this->hasMany(DetailAudit::class, 'id_auditor', 'id_auditor');
    }
}
