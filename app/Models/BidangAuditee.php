<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BidangAuditee extends Model
{
    use HasFactory;

    protected $table = 'bidang_auditee';
    protected $primaryKey = 'id_bidang_auditee';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'bidang_auditee',
    ];

    public function auditees(): HasMany
    {
        return $this->hasMany(Auditee::class, 'id_bidang_auditee', 'id_bidang_auditee');
    }
}
