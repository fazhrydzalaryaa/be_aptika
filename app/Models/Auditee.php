<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditee extends Model
{
    use HasFactory;

    protected $table = 'auditee';
    protected $primaryKey = 'id_auditee';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_bidang_auditee',
        'id_lokasi_auditee',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangAuditee::class, 'id_bidang_auditee', 'id_bidang_auditee');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiAuditee::class, 'id_lokasi_auditee', 'id_lokasi_auditee');
    }

    public function detailAudits(): HasMany
    {
        return $this->hasMany(DetailAudit::class, 'id_auditee', 'id_auditee');
    }
}
