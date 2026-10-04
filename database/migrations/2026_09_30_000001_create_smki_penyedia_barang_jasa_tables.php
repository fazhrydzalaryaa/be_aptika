<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Kategori Ruang Lingkup
        if (!Schema::hasTable('smki_kategori_ruang_lingkups')) {
            Schema::create('smki_kategori_ruang_lingkups', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori')->unique();
                $table->timestamps();
            });
        }

        // 2. Master Ruang Lingkup (anak dari Kategori)
        if (!Schema::hasTable('smki_ruang_lingkups')) {
            Schema::create('smki_ruang_lingkups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kategori_ruang_lingkup_id')->index();
                $table->string('nama_ruang_lingkup');
                $table->timestamps();

                $table->foreign('kategori_ruang_lingkup_id')
                    ->references('id')->on('smki_kategori_ruang_lingkups')
                    ->cascadeOnDelete();
            });
        }

        // 3. Tabel Utama Penyedia Barang/Jasa (FR-020)
        if (!Schema::hasTable('smki_penyedia_barang_jasas')) {
            Schema::create('smki_penyedia_barang_jasas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('bidang_id')->nullable()->index();

                $table->string('nama_perusahaan');
                $table->text('alamat');
                $table->string('no_kontrak')->nullable();
                $table->unsignedBigInteger('kategori_ruang_lingkup_id')->nullable()->index();
                $table->unsignedBigInteger('ruang_lingkup_id')->nullable()->index();
                $table->string('contact_person');
                $table->string('no_telp');
                $table->enum('berita_acara', ['Tersedia', 'Tidak Ada'])->default('Tidak Ada');

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                $table->foreign('kategori_ruang_lingkup_id')->references('id')->on('smki_kategori_ruang_lingkups')->nullOnDelete();
                $table->foreign('ruang_lingkup_id')->references('id')->on('smki_ruang_lingkups')->nullOnDelete();
            });
        }

        // 4. Seed master data Kategori & Ruang Lingkup (sumber: Data_Kategori_Ruang_Lingkup_Penyedia.xlsx)
        $kategoriData = [
            'Teknologi Informasi & Sistem Informasi',
            'Jaringan & Infrastruktur',
            'Perangkat Keras / Hardware',
            'Telekomunikasi',
            'Multimedia & Digital',
            'Konsultansi & Profesional',
            'Percetakan & Publikasi',
            'Pengadaan Barang Umum',
            'Jasa Pemeliharaan',
            'Event & Kegiatan',
        ];

        $ruangLingkupData = [
            'Teknologi Informasi & Sistem Informasi' => [
                'Pengembangan Aplikasi/Web',
                'Pengembangan Sistem Informasi',
                'Pengembangan Mobile Application',
                'Software Development',
                'Implementasi Sistem Informasi',
                'Pemeliharaan dan Pengembangan Aplikasi',
                'Konsultansi Teknologi Informasi',
                'Integrasi Sistem/API',
                'Database & Data Management',
                'Cloud Computing',
                'DevOps & Infrastruktur TI',
                'IT Support',
            ],
            'Jaringan & Infrastruktur' => [
                'Pengadaan dan Instalasi Jaringan Komputer',
                'Pemeliharaan Jaringan',
                'Infrastruktur Data Center',
                'Server & Storage',
                'Perangkat Jaringan',
                'Internet & Bandwidth',
                'Instalasi Fiber Optic',
                'CCTV & Sistem Keamanan',
                'Maintenance Infrastruktur TI',
            ],
            'Perangkat Keras / Hardware' => [
                'Pengadaan Komputer/Laptop',
                'Pengadaan Server',
                'Pengadaan Printer/Scanner',
                'Pengadaan Perangkat Jaringan',
                'Pengadaan Perangkat Telekomunikasi',
                'Pengadaan Perangkat Multimedia',
                'Pemeliharaan/Service Hardware',
            ],
            'Telekomunikasi' => [
                'Jasa Internet',
                'Jasa Telekomunikasi',
                'Jasa Data Center',
                'Jasa Hosting',
                'Jasa Domain',
                'Jasa Komunikasi Data',
                'Jasa Call Center/Contact Center',
            ],
            'Multimedia & Digital' => [
                'Produksi Video',
                'Produksi Film/Dokumentasi',
                'Fotografi',
                'Videografi',
                'Live Streaming',
                'Animasi',
                'Motion Graphic',
                'Desain Grafis',
                'Digital Content',
                'Digital Marketing',
                'Social Media Management',
            ],
            'Konsultansi & Profesional' => [
                'Konsultansi Manajemen',
                'Konsultansi Teknologi Informasi',
                'Konsultansi Sistem Informasi',
                'Konsultansi Perencanaan',
                'Jasa Tenaga Ahli',
                'Jasa Audit',
                'Jasa Pendampingan',
                'Jasa Assessment/Evaluasi',
                'Jasa Pelatihan & Narasumber',
            ],
            'Percetakan & Publikasi' => [
                'Percetakan Dokumen',
                'Percetakan Buku/Majalah',
                'Percetakan Banner/Baliho',
                'Digital Printing',
                'Advertising',
                'Publikasi dan Media',
                'Pengadaan Merchandise',
            ],
            'Pengadaan Barang Umum' => [
                'Alat Tulis Kantor (ATK)',
                'Perlengkapan Kantor',
                'Furniture/Perabot Kantor',
                'Elektronik',
                'Peralatan Rapat',
                'Peralatan Kebersihan',
                'Barang Cetakan',
            ],
            'Jasa Pemeliharaan' => [
                'Maintenance Gedung',
                'Maintenance AC',
                'Maintenance Listrik',
                'Maintenance Kendaraan',
                'Maintenance Peralatan Kantor',
                'Maintenance Peralatan Elektronik',
                'Cleaning Service',
                'Security',
            ],
            'Event & Kegiatan' => [
                'Event Organizer',
                'Jasa Pelaksanaan Kegiatan',
                'Jasa Penyewaan Gedung/Ruangan',
                'Jasa Penyewaan Peralatan',
                'Jasa Catering',
                'Jasa Dekorasi',
                'Jasa Dokumentasi Kegiatan',
            ],
        ];

        foreach ($kategoriData as $namaKategori) {
            $kategoriId = DB::table('smki_kategori_ruang_lingkups')->where('nama_kategori', $namaKategori)->value('id');
            if (!$kategoriId) {
                $kategoriId = DB::table('smki_kategori_ruang_lingkups')->insertGetId([
                    'nama_kategori' => $namaKategori,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            foreach ($ruangLingkupData[$namaKategori] as $namaRuangLingkup) {
                $exists = DB::table('smki_ruang_lingkups')
                    ->where('kategori_ruang_lingkup_id', $kategoriId)
                    ->where('nama_ruang_lingkup', $namaRuangLingkup)
                    ->exists();
                if (!$exists) {
                    DB::table('smki_ruang_lingkups')->insert([
                        'kategori_ruang_lingkup_id' => $kategoriId,
                        'nama_ruang_lingkup'        => $namaRuangLingkup,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smki_penyedia_barang_jasas');
        Schema::dropIfExists('smki_ruang_lingkups');
        Schema::dropIfExists('smki_kategori_ruang_lingkups');
    }
};
