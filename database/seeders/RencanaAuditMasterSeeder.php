<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RencanaAuditMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bidang Auditee (sesuai dengan bidangs yang ada)
        DB::table('bidang_auditee')->insertOrIgnore([
            ['bidang_auditee' => 'Sekretariat', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'Bidang E-Government', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'Bidang Aplikasi Informatika', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'Bidang Informasi dan Komunikasi Publik', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'Bidang Persandian dan Keamanan Informasi', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'Bidang Statistik', 'created_at' => now(), 'updated_at' => now()],
            ['bidang_auditee' => 'UPTD Pusat Layanan Digital', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Lokasi Auditee
        DB::table('lokasi_auditee')->insertOrIgnore([
            ['lokasi_auditee' => 'Kantor Pusat Jl. Diponegoro', 'created_at' => now(), 'updated_at' => now()],
            ['lokasi_auditee' => 'Gedung A Lantai 3', 'created_at' => now(), 'updated_at' => now()],
            ['lokasi_auditee' => 'Gedung B Lantai 2', 'created_at' => now(), 'updated_at' => now()],
            ['lokasi_auditee' => 'Data Center Bandung', 'created_at' => now(), 'updated_at' => now()],
            ['lokasi_auditee' => 'Ruang Server Backup', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Auditor
        DB::table('auditor')->insertOrIgnore([
            ['nama_auditor' => 'Dr. Budi Santoso', 'nip_auditor' => '197501011998021001', 'created_at' => now(), 'updated_at' => now()],
            ['nama_auditor' => 'Siti Nurhaliza', 'nip_auditor' => '198503152010012002', 'created_at' => now(), 'updated_at' => now()],
            ['nama_auditor' => 'Ahmad Wijaya', 'nip_auditor' => '198811202015011003', 'created_at' => now(), 'updated_at' => now()],
            ['nama_auditor' => 'Eka Putri', 'nip_auditor' => '199205122018022001', 'created_at' => now(), 'updated_at' => now()],
            ['nama_auditor' => 'Rudi Hartono', 'nip_auditor' => '198701201992031001', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Auditee (relasi bidang + lokasi)
        $bidangIds = DB::table('bidang_auditee')->pluck('id_bidang_auditee')->toArray();
        $lokasiIds = DB::table('lokasi_auditee')->pluck('id_lokasi_auditee')->toArray();

        if (!empty($bidangIds) && !empty($lokasiIds)) {
            DB::table('auditee')->insertOrIgnore([
                ['id_bidang_auditee' => $bidangIds[0], 'id_lokasi_auditee' => $lokasiIds[0], 'created_at' => now(), 'updated_at' => now()],
                ['id_bidang_auditee' => $bidangIds[1], 'id_lokasi_auditee' => $lokasiIds[1], 'created_at' => now(), 'updated_at' => now()],
                ['id_bidang_auditee' => $bidangIds[2], 'id_lokasi_auditee' => $lokasiIds[2], 'created_at' => now(), 'updated_at' => now()],
                ['id_bidang_auditee' => $bidangIds[3], 'id_lokasi_auditee' => $lokasiIds[3], 'created_at' => now(), 'updated_at' => now()],
                ['id_bidang_auditee' => $bidangIds[4], 'id_lokasi_auditee' => $lokasiIds[4], 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
