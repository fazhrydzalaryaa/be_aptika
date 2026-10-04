<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detail_audit';
    protected $primaryKey = 'id_detail_audit';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_auditor',
        'id_auditee',
        'kontrol_SMKI',
        'tanggal_audit',
        'kode_prosedur',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_audit' => 'date:Y-m-d',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    protected $appends = [
        'bidang_nama',
        'lokasi_nama',
        'auditor_nama',
        'auditor_nip',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Auditor::class, 'id_auditor', 'id_auditor');
    }

    public function auditee(): BelongsTo
    {
        return $this->belongsTo(Auditee::class, 'id_auditee', 'id_auditee');
    }

    public function getBidangNamaAttribute(): string
    {
        return $this->auditee?->bidang?->bidang_auditee ?? '-';
    }

    public function getLokasiNamaAttribute(): string
    {
        return $this->auditee?->lokasi?->lokasi_auditee ?? '-';
    }

    public function getAuditorNamaAttribute(): string
    {
        return $this->auditor?->nama_auditor ?? '-';
    }

    public function getAuditorNipAttribute(): ?string
    {
        return $this->auditor?->nip_auditor;
    }
}
