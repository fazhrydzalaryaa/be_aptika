<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SmkiUnitKerja;
use App\Models\SmkiKategoriTemuan;
use App\Models\SmkiLaporanAudit;
use App\Models\SmkiDetailTemuan;

class SmkiLaporanAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Unit Kerja SMKI
        $unitKerjas = [
            'Sekretariat Diskominfo',
            'Bidang Aplikasi Informatika',
            'Bidang Informasi Komunikasi Publik (IKP)',
            'Bidang Persandian dan Keamanan Informasi',
            'Bidang Statistik',
        ];

        $unitMap = [];
        foreach ($unitKerjas as $name) {
            $uk = SmkiUnitKerja::firstOrCreate(['nama_unit_kerja' => $name]);
            $unitMap[$name] = $uk->id_unit_kerja;
        }

        // 2. Seed Kategori Temuan SMKI
        $kategoris = [
            'Major',
            'Minor',
            'OFI',
            'Akses Kontrol',
            'Keamanan Jaringan',
            'Pengelolaan Aset',
            'Kriptografi',
            'Keamanan Fisik',
            'Operasional & Prosedur',
        ];

        $katMap = [];
        foreach ($kategoris as $name) {
            $kt = SmkiKategoriTemuan::firstOrCreate(['nama_kategori' => $name]);
            $katMap[$name] = $kt->id_kategori;
        }

        // 3. Seed Mockup Data Reports (FR-006-001 s/d FR-006-005)
        $reports = [
            [
                'nomor_laporan'   => 'FR-006-001',
                'id_unit_kerja'   => $unitMap['Sekretariat Diskominfo'] ?? 1,
                'nama_unit_kerja' => 'Sekretariat Diskominfo',
                'auditor'         => 'Bambang Hariyanto',
                'auditee'         => 'Siska Amelia',
                'tanggal_audit'   => '2023-09-12',
                'kategori'        => 'Kategori',
                'status'          => 'Selesai',
                'temuan_major'    => 1,
                'temuan_minor'    => 3,
                'ofi'             => 5,
                'klausul_annex'   => 'Multi-Clause (Lihat Rincian)',
                'latar_belakang'  => 'Audit internal merupakan salah satu persyaratan ISO/IEC 27001:2022 yang harus dipenuhi oleh Dinas Komunikasi dan Informatika Provinsi Jawa Barat sebagai bentuk dari evaluasi kinerja sistem manajemen. Audit internal dimaksudkan untuk meninjau tingkat kesesuaian dan efektivitas penerapan Sistem Manajemen Keamanan Informasi (SMKI) yang telah diimplementasikan.',
                'tujuan'          => "1. Memeriksa kesesuaian atau ketidaksesuaian persyaratan standar.\n2. Memeriksa kesesuaian pencapaian tujuan keamanan informasi yang telah ditentukan.\n3. Menemukan peluang perbaikan dari proses implementasi SMKI sehingga tercapai perbaikan berkelanjutan.",
                'ruang_lingkup'   => 'Pelaksanaan audit internal SMKI Dinas Komunikasi dan Informatika Provinsi Jawa Barat dilakukan oleh Tim Audit Internal. Ruang lingkup audit mencakup Sekretariat Diskominfo dan sub-bagian terkait.',
                'details'         => [
                    [
                        'tanggal_audit'   => '2023-09-12',
                        'kategori_temuan' => 'Major',
                        'klausul_annex'   => 'A.5.15 Access Control',
                        'deskripsi_temuan'=> 'Ditemukan bahwa 12 akun mantan karyawan masih memiliki akses aktif ke database produksi (RDS-PROD-01) lebih dari 30 hari setelah pemutusan kontrak.',
                        'rekomendasi'     => 'Segera lakukan penonaktifan akun secara berkala dan integrasikan HRIS dengan IAM system.',
                    ],
                    [
                        'tanggal_audit'   => '2023-09-12',
                        'kategori_temuan' => 'Minor',
                        'klausul_annex'   => 'A.8.10 Information Deletion',
                        'deskripsi_temuan'=> 'Prosedur penghapusan media fisik harddisk rusak belum terdokumentasi dengan baik di ruang server B04.',
                        'rekomendasi'     => 'Gunakan formulir FR-014 untuk setiap kegiatan penghancuran media penyimpanan.',
                    ],
                    [
                        'tanggal_audit'   => '2023-09-12',
                        'kategori_temuan' => 'OFI',
                        'klausul_annex'   => 'A.12.1 Operational Procedures',
                        'deskripsi_temuan'=> 'Dokumentasi SOP backup data sudah ada namun perlu diperbarui untuk mencakup arsitektur cloud baru.',
                        'rekomendasi'     => 'Lakukan review SOP setiap 6 bulan sekali untuk menyesuaikan dengan perubahan infrastruktur.',
                    ],
                ],
            ],
            [
                'nomor_laporan'   => 'FR-006-002',
                'id_unit_kerja'   => $unitMap['Bidang Aplikasi Informatika'] ?? 2,
                'nama_unit_kerja' => 'Bidang Aptika',
                'auditor'         => 'Dedi Kurniawan',
                'auditee'         => 'Rahma Nur Febriyani',
                'tanggal_audit'   => '2023-09-14',
                'kategori'        => 'Kategori',
                'status'          => 'Sedang Ditinjau',
                'temuan_major'    => 2,
                'temuan_minor'    => 2,
                'ofi'             => 4,
                'klausul_annex'   => 'A.8.20 Network Security',
                'latar_belakang'  => 'Audit internal SMKI siklus tahun 2023 untuk Bidang Aplikasi Informatika.',
                'tujuan'          => 'Memastikan seluruh repositori aplikasi dan jaringan staging telah memenuhi standar keamanan informasi.',
                'ruang_lingkup'   => 'Layanan pengembangan aplikasi publik dan internal Diskominfo Jabar.',
                'details'         => [
                    [
                        'tanggal_audit'   => '2023-09-14',
                        'kategori_temuan' => 'Major',
                        'klausul_annex'   => 'A.8.20 Network Security',
                        'deskripsi_temuan'=> 'Port default database staging terekspos secara publik tanpa batasan IP whitelist.',
                        'rekomendasi'     => 'Terapkan VPN atau whitelist IP statis pada security group firewall AWS/GCP.',
                    ],
                ],
            ],
            [
                'nomor_laporan'   => 'FR-006-003',
                'id_unit_kerja'   => $unitMap['Bidang Informasi Komunikasi Publik (IKP)'] ?? 3,
                'nama_unit_kerja' => 'Bidang IKP',
                'auditor'         => 'Siti Aminah',
                'auditee'         => 'Agus Setiawan',
                'tanggal_audit'   => '2023-09-15',
                'kategori'        => 'Kategori',
                'status'          => 'Draft',
                'temuan_major'    => 0,
                'temuan_minor'    => 1,
                'ofi'             => 2,
                'klausul_annex'   => 'A.5.9 Inventory of Information and Other Associated Assets',
                'latar_belakang'  => 'Audit kepatuhan inventarisasi aset informasi pada Bidang IKP.',
                'tujuan'          => 'Memverifikasi kelengkapan data aset portal berita dan media sosial resmi Pemprov Jabar.',
                'ruang_lingkup'   => 'Server CMS portal dan akun pengelola informasi publik.',
                'details'         => [
                    [
                        'tanggal_audit'   => '2023-09-15',
                        'kategori_temuan' => 'Minor',
                        'klausul_annex'   => 'A.5.9 Asset Inventory',
                        'deskripsi_temuan'=> 'Daftar penanggung jawab akun media sosial dinas belum dimutakhirkan pasca rotasi staf.',
                        'rekomendasi'     => 'Perbarui surat penugasan admin portal dan reset kata sandi berkala.',
                    ],
                ],
            ],
            [
                'nomor_laporan'   => 'FR-006-004',
                'id_unit_kerja'   => $unitMap['Bidang Persandian dan Keamanan Informasi'] ?? 4,
                'nama_unit_kerja' => 'Bidang Persandian',
                'auditor'         => 'Budi Santoso',
                'auditee'         => 'Eka Putri',
                'tanggal_audit'   => '2023-09-18',
                'kategori'        => 'Kategori',
                'status'          => 'Sedang Ditinjau',
                'temuan_major'    => 1,
                'temuan_minor'    => 1,
                'ofi'             => 3,
                'klausul_annex'   => 'A.8.24 Use of Cryptography',
                'latar_belakang'  => 'Audit tata kelola sertifikat digital dan pengelolaan kunci kriptografi persandian.',
                'tujuan'          => 'Memastikan kepatuhan terhadap regulasi persandian dan sertifikat SSL/TLS Pemprov Jabar.',
                'ruang_lingkup'   => 'Infrastruktur Public Key Infrastructure (PKI) dan modul tanda tangan elektronik.',
                'details'         => [
                    [
                        'tanggal_audit'   => '2023-09-18',
                        'kategori_temuan' => 'Major',
                        'klausul_annex'   => 'A.8.24 Cryptography',
                        'deskripsi_temuan'=> 'Ditemukan dua sertifikat TLS wildcard yang akan kadaluarsa dalam waktu kurang dari 7 hari tanpa notifikasi otomatis.',
                        'rekomendasi'     => 'Konfigurasikan sistem auto-renewal menggunakan ACME certbot atau reminder bot Telegram.',
                    ],
                ],
            ],
            [
                'nomor_laporan'   => 'FR-006-005',
                'id_unit_kerja'   => $unitMap['Bidang Statistik'] ?? 5,
                'nama_unit_kerja' => 'Bidang Statistik',
                'auditor'         => 'Ani Wijaya',
                'auditee'         => 'Rian Hidayat',
                'tanggal_audit'   => '2023-09-20',
                'kategori'        => 'Kategori',
                'status'          => 'Selesai',
                'temuan_major'    => 0,
                'temuan_minor'    => 2,
                'ofi'             => 1,
                'klausul_annex'   => 'A.7.4 Physical Security Monitoring',
                'latar_belakang'  => 'Audit fasilitas pemrosesan data statistik sektoral Jawa Barat.',
                'tujuan'          => 'Meninjau kontrol akses fisik dan penanganan data survei statistik sensitif.',
                'ruang_lingkup'   => 'Ruang analisis data statistik dan server penyimpanan dataset agregat.',
                'details'         => [
                    [
                        'tanggal_audit'   => '2023-09-20',
                        'kategori_temuan' => 'Minor',
                        'klausul_annex'   => 'A.7.4 Physical Security Monitoring',
                        'deskripsi_temuan'=> 'Kamera CCTV di lorong server statistik mengalami gangguan rekaman selama 2 hari.',
                        'rekomendasi'     => 'Lakukan pemeliharaan rutin NVR CCTV dan pasang sensor alarm gangguan daya.',
                    ],
                ],
            ],
        ];

        foreach ($reports as $r) {
            $details = $r['details'];
            unset($r['details']);

            $lap = SmkiLaporanAudit::updateOrCreate(
                ['nomor_laporan' => $r['nomor_laporan']],
                $r
            );

            SmkiDetailTemuan::where('id_laporan_audit', $lap->id_laporan_audit)->delete();

            foreach ($details as $d) {
                SmkiDetailTemuan::create([
                    'id_laporan_audit' => $lap->id_laporan_audit,
                    'tanggal_audit'    => $d['tanggal_audit'] ?? $lap->tanggal_audit,
                    'kategori_temuan'  => $d['kategori_temuan'] ?? 'Major',
                    'klausul_annex'    => $d['klausul_annex'] ?? '',
                    'deskripsi_temuan' => $d['deskripsi_temuan'] ?? '',
                    'rekomendasi'      => $d['rekomendasi'] ?? '',
                ]);
            }
        }
    }
}
