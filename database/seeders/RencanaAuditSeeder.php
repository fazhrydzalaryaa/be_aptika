<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BidangAuditee;
use App\Models\LokasiAuditee;
use App\Models\Auditee;
use App\Models\Auditor;
use App\Models\DetailAudit;

class RencanaAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Bidang Auditee
        $bidangList = [
            'Bidang Infrastruktur & Teknologi',
            'Sekretariat Aptika',
            'Layanan Aplikasi Informatika',
            'Aplikasi Informatika (APTIKA)',
            'Bidang Penyelenggaraan Informatika',
            'Bidang Statistik & Persandian',
            'Bidang Informasi dan Komunikasi Publik (IKP)',
        ];

        $bidangMap = [];
        foreach ($bidangList as $name) {
            $b = BidangAuditee::firstOrCreate(['bidang_auditee' => $name]);
            $bidangMap[$name] = $b->id_bidang_auditee;
        }

        // 2. Seed Lokasi Auditee
        $lokasiList = [
            'Gedung A, Lt. 3',
            'Gedung B, Lt. 1',
            'Data Center, Lt. Dasar',
            'Ruang Server Gedung B Diskominfo',
            'Gedung A, Lantai 3, Ruang Rapat',
            'Gedung C, Lantai 2',
            'Command Center Lt. 2',
        ];

        $lokasiMap = [];
        foreach ($lokasiList as $name) {
            $l = LokasiAuditee::firstOrCreate(['lokasi_auditee' => $name]);
            $lokasiMap[$name] = $l->id_lokasi_auditee;
        }

        // 3. Seed Auditee (Pasangan Bidang & Lokasi)
        $auditeePairs = [
            ['Bidang Infrastruktur & Teknologi', 'Gedung A, Lt. 3'],
            ['Sekretariat Aptika', 'Gedung B, Lt. 1'],
            ['Layanan Aplikasi Informatika', 'Data Center, Lt. Dasar'],
            ['Aplikasi Informatika (APTIKA)', 'Ruang Server Gedung B Diskominfo'],
            ['Bidang Penyelenggaraan Informatika', 'Gedung A, Lantai 3, Ruang Rapat'],
            ['Bidang Statistik & Persandian', 'Gedung C, Lantai 2'],
            ['Bidang Informasi dan Komunikasi Publik (IKP)', 'Command Center Lt. 2'],
        ];

        $auditeeMap = [];
        foreach ($auditeePairs as $pair) {
            $bId = $bidangMap[$pair[0]];
            $lId = $lokasiMap[$pair[1]];
            $aud = Auditee::firstOrCreate([
                'id_bidang_auditee' => $bId,
                'id_lokasi_auditee' => $lId,
            ]);
            $key = $pair[0] . '|' . $pair[1];
            $auditeeMap[$key] = $aud->id_auditee;
        }

        // 4. Seed Auditor
        $auditors = [
            ['nama' => 'Heri Susanto, M.Kom', 'nip' => '19820415 200801 1 005'],
            ['nama' => 'Siti Aminah, S.T.', 'nip' => '19850912 201001 2 014'],
            ['nama' => 'Budi Raharjo', 'nip' => '19780321 200501 1 008'],
            ['nama' => 'Asep Junaedi, S.Kom.', 'nip' => '19890110 201402 1 002'],
            ['nama' => 'Dr. Ir. Hendra Saputra, M.T.', 'nip' => '19750518 200112 1 003'],
            ['nama' => 'Rina Marlina, S.Kom., M.T.', 'nip' => '19870314 201101 2 009'],
        ];

        $auditorMap = [];
        foreach ($auditors as $aud) {
            $a = Auditor::firstOrCreate(
                ['nama_auditor' => $aud['nama']],
                ['nip_auditor' => $aud['nip']]
            );
            $auditorMap[$aud['nama']] = $a->id_auditor;
        }

        // 5. Seed Detail Audit (Sample data matching mockups)
        $auditPlans = [
            [
                'kontrol' => 'Klausul 5.2 - Kebijakan Keamanan Informasi',
                'kode'    => 'SMKI-PROC-001',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Bidang Infrastruktur & Teknologi',
                'lokasi'  => 'Gedung A, Lt. 3',
                'tanggal' => '2024-06-12',
                'auditor' => 'Heri Susanto, M.Kom',
                'catatan' => 'Verifikasi ketersediaan dokumen kebijakan keamanan informasi versi terbaru.',
            ],
            [
                'kontrol' => 'Klausul 7.5 - Informasi Terdokumentasi',
                'kode'    => 'SMKI-PROC-002',
                'status'  => 'PENDING',
                'bidang'  => 'Sekretariat Aptika',
                'lokasi'  => 'Gedung B, Lt. 1',
                'tanggal' => '2024-06-15',
                'auditor' => 'Siti Aminah, S.T.',
                'catatan' => 'Menunggu konfirmasi auditee terkait ketersediaan ruangan dan arsip dokumen fisik.',
            ],
            [
                'kontrol' => 'A.12.1 - Prosedur Operasi Keamanan',
                'kode'    => 'SMKI-PROC-003',
                'status'  => 'IN PROGRESS',
                'bidang'  => 'Layanan Aplikasi Informatika',
                'lokasi'  => 'Data Center, Lt. Dasar',
                'tanggal' => '2024-06-20',
                'auditor' => 'Budi Raharjo',
                'catatan' => 'Audit sedang berjalan sesuai jadwal, evaluasi SOP backup dan pemeliharaan server.',
            ],
            [
                'kontrol' => 'A.5.1 - Kebijakan Keamanan Informasi & Prosedur Evaluasi Keamanan',
                'kode'    => 'SMKI-PROC-004',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Aplikasi Informatika (APTIKA)',
                'lokasi'  => 'Ruang Server Gedung B Diskominfo',
                'tanggal' => '2026-10-25',
                'auditor' => 'Asep Junaedi, S.Kom.',
                'catatan' => 'Pemeriksaan kepatuhan klausul standar ISO 27001:2022 kontrol organisasi.',
            ],
            [
                'kontrol' => 'A.9.2 - Pengelolaan Akses Pengguna & Otentikasi Terpusat',
                'kode'    => 'SMKI-PROC-005',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Bidang Penyelenggaraan Informatika',
                'lokasi'  => 'Gedung A, Lantai 3, Ruang Rapat',
                'tanggal' => '2024-05-20',
                'auditor' => 'Dr. Ir. Hendra Saputra, M.T.',
                'catatan' => 'Pemeriksaan log otentikasi LDAP dan Single Sign-On Pemprov Jabar.',
            ],
            [
                'kontrol' => 'Klausul 9.2 - Audit Internal & Tinjauan Manajemen',
                'kode'    => 'SMKI-PROC-006',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Bidang Statistik & Persandian',
                'lokasi'  => 'Gedung C, Lantai 2',
                'tanggal' => '2024-07-05',
                'auditor' => 'Heri Susanto, M.Kom',
                'catatan' => 'Pemeriksaan tindak lanjut temuan audit siklus sebelumnya.',
            ],
            [
                'kontrol' => 'A.13.1 - Manajemen Keamanan Jaringan Komunikasi',
                'kode'    => 'SMKI-PROC-007',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Bidang Infrastruktur & Teknologi',
                'lokasi'  => 'Gedung A, Lt. 3',
                'tanggal' => '2024-07-12',
                'auditor' => 'Budi Raharjo',
                'catatan' => 'Evaluasi konfigurasi firewall, segmentasi VLAN, dan VPN gateway.',
            ],
            [
                'kontrol' => 'A.8.1 - Tanggung Jawab Aset & Klasifikasi Informasi',
                'kode'    => 'SMKI-PROC-008',
                'status'  => 'SCHEDULED',
                'bidang'  => 'Bidang Informasi dan Komunikasi Publik (IKP)',
                'lokasi'  => 'Command Center Lt. 2',
                'tanggal' => '2024-07-18',
                'auditor' => 'Rina Marlina, S.Kom., M.T.',
                'catatan' => 'Verifikasi label aset informasi rahasia dan publikasi portal resmi.',
            ],
        ];

        foreach ($auditPlans as $plan) {
            $auditeeId = $auditeeMap[$plan['bidang'] . '|' . $plan['lokasi']];
            $auditorId = $auditorMap[$plan['auditor']];

            DetailAudit::firstOrCreate(
                [
                    'kontrol_SMKI'  => $plan['kontrol'],
                    'tanggal_audit' => $plan['tanggal'],
                ],
                [
                    'id_auditor'    => $auditorId,
                    'id_auditee'    => $auditeeId,
                    'kode_prosedur' => $plan['kode'],
                    'status'        => $plan['status'],
                    'catatan'       => $plan['catatan'],
                ]
            );
        }
    }
}
