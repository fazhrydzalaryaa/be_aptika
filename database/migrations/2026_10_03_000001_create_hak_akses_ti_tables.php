<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat skema Fitur Hak Akses TI (FR-018) sesuai ERD:
     * master_unit_kerja, master_jenis_permohonan, master_sistem_aplikasi,
     * master_level_akses, master_jenis_akses, hak_akses_ti, hak_akses_jenis.
     */
    public function up(): void
    {
        if (!Schema::hasTable('master_unit_kerja')) {
            Schema::create('master_unit_kerja', function (Blueprint $table) {
                $table->id('id_unit_kerja');
                $table->string('nama_unit');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('master_jenis_permohonan')) {
            Schema::create('master_jenis_permohonan', function (Blueprint $table) {
                $table->id('id_jenis_permohonan');
                $table->string('nama_jenis');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('master_sistem_aplikasi')) {
            Schema::create('master_sistem_aplikasi', function (Blueprint $table) {
                $table->id('id_sistem_aplikasi');
                $table->string('nama_sistem');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('master_level_akses')) {
            Schema::create('master_level_akses', function (Blueprint $table) {
                $table->id('id_level_akses');
                $table->string('nama_level');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('master_jenis_akses')) {
            Schema::create('master_jenis_akses', function (Blueprint $table) {
                $table->id('id_jenis_akses');
                $table->string('nama_akses');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hak_akses_ti')) {
            Schema::create('hak_akses_ti', function (Blueprint $table) {
                $table->id('id_hak_akses');
                $table->string('nomor_request')->unique();
                $table->string('nama_pemohon');
                $table->string('nip_id_pegawai')->nullable();
                $table->string('jabatan')->nullable();
                $table->string('email')->nullable();
                $table->string('kontak_person')->nullable();

                $table->unsignedBigInteger('id_unit_kerja')->nullable();
                $table->unsignedBigInteger('id_jenis_permohonan')->nullable();
                $table->unsignedBigInteger('id_sistem_aplikasi')->nullable();
                $table->unsignedBigInteger('id_level_akses')->nullable();

                $table->string('sifat_akses')->default('Permanen');
                $table->string('waktu_akses')->default('Jam Kerja');
                $table->string('waktu_akses_lainnya')->nullable();
                $table->date('masa_berlaku_mulai')->nullable();
                $table->date('masa_berlaku_selesai')->nullable();
                $table->text('keperluan')->nullable();
                $table->string('sistem_lainnya')->nullable();
                $table->json('modul_fitur')->nullable();
                $table->boolean('persetujuan_ketentuan')->default(false);
                $table->string('status_permohonan')->default('Menunggu');

                $table->unsignedBigInteger('bidang_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();

                $table->timestamps();

                $table->index('status_permohonan');
                $table->index('sifat_akses');
                $table->index('id_unit_kerja');
            });
        }

        if (!Schema::hasTable('hak_akses_jenis')) {
            Schema::create('hak_akses_jenis', function (Blueprint $table) {
                $table->id('id_hak_akses_jenis');
                $table->unsignedBigInteger('id_hak_akses');
                $table->unsignedBigInteger('id_jenis_akses');
                $table->timestamps();

                $table->index('id_hak_akses');
                $table->index('id_jenis_akses');
            });
        }

        $this->seedMasters();
    }

    private function seedMasters(): void
    {
        $now = now();

        $units = [
            'Sekretariat',
            'Bidang Aplikasi Informatika (APTIKA)',
            'Bidang E-Government',
            'Bidang Infrastruktur TIK',
            'Bidang Statistik dan Persandian',
            'Bidang Pengelolaan Informasi dan Komunikasi Publik',
            'UPTD',
            'Vendor / Pihak Ketiga',
        ];
        foreach ($units as $nama) {
            DB::table('master_unit_kerja')->updateOrInsert(
                ['nama_unit' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $jenisPermohonan = ['Baru', 'Ubah', 'Hapus', 'Perpanjangan'];
        foreach ($jenisPermohonan as $nama) {
            DB::table('master_jenis_permohonan')->updateOrInsert(
                ['nama_jenis' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $sistem = [
            ['nama_sistem' => 'Server Data Center (Root)', 'deskripsi' => 'Akses root pada server pusat data'],
            ['nama_sistem' => 'Database Produksi (Postgres)', 'deskripsi' => 'Akses basis data lingkungan produksi'],
            ['nama_sistem' => 'Log Management SIEM', 'deskripsi' => 'Akses perangkat monitoring & log keamanan'],
            ['nama_sistem' => 'Aplikasi E-Office v2', 'deskripsi' => 'Aplikasi persuratan elektronik'],
            ['nama_sistem' => 'VPN Gateway Jabar', 'deskripsi' => 'Akses jaringan privat virtual'],
            ['nama_sistem' => 'Core Router & Switch', 'deskripsi' => 'Perangkat jaringan inti'],
            ['nama_sistem' => 'Sistem Manajemen Aset TI', 'deskripsi' => 'Aplikasi inventarisasi aset'],
            ['nama_sistem' => 'SMKI Dashboard Pro', 'deskripsi' => 'Platform kepatuhan SMKI'],
        ];
        foreach ($sistem as $s) {
            DB::table('master_sistem_aplikasi')->updateOrInsert(
                ['nama_sistem' => $s['nama_sistem']],
                ['deskripsi' => $s['deskripsi'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $levels = ['Administrator', 'Developer / Power User', 'Operator', 'Verifikator', 'Viewer / Read-only', 'User'];
        foreach ($levels as $nama) {
            DB::table('master_level_akses')->updateOrInsert(
                ['nama_level' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $jenisAkses = ['OS (Admin)', 'Internet', 'Database', 'Aplikasi', 'Teleworking', 'Lainnya'];
        foreach ($jenisAkses as $nama) {
            DB::table('master_jenis_akses')->updateOrInsert(
                ['nama_akses' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hak_akses_jenis');
        Schema::dropIfExists('hak_akses_ti');
        Schema::dropIfExists('master_jenis_akses');
        Schema::dropIfExists('master_level_akses');
        Schema::dropIfExists('master_sistem_aplikasi');
        Schema::dropIfExists('master_jenis_permohonan');
        Schema::dropIfExists('master_unit_kerja');
    }
};
