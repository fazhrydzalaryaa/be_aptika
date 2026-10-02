<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Unit Kerja SMKI
        if (!Schema::hasTable('smki_unit_kerjas')) {
            Schema::create('smki_unit_kerjas', function (Blueprint $table) {
                $table->id('id_unit_kerja');
                $table->string('nama_unit_kerja');
                $table->timestamps();
            });
        }

        // 2. Master Kategori Temuan SMKI
        if (!Schema::hasTable('smki_kategori_temuans')) {
            Schema::create('smki_kategori_temuans', function (Blueprint $table) {
                $table->id('id_kategori');
                $table->string('nama_kategori');
                $table->timestamps();
            });
        }

        // 3. Tabel Utama Laporan Audit SMKI (FR-006)
        if (!Schema::hasTable('smki_laporan_audits')) {
            Schema::create('smki_laporan_audits', function (Blueprint $table) {
                $table->id('id_laporan_audit');
                $table->string('nomor_laporan')->unique()->index();
                
                // Ringkasan Temuan
                $table->integer('temuan_major')->default(0);
                $table->integer('temuan_minor')->default(0);
                $table->integer('ofi')->default(0);

                // Relasi Unit Kerja
                $table->unsignedBigInteger('id_unit_kerja')->nullable()->index();
                $table->string('nama_unit_kerja')->nullable();

                // Auditor & Auditee
                $table->unsignedBigInteger('id_auditor')->nullable()->index();
                $table->string('auditor')->nullable();
                $table->unsignedBigInteger('id_auditee')->nullable()->index();
                $table->string('auditee')->nullable();

                // Tanggal Audit & Klasifikasi
                $table->date('tanggal_audit')->nullable();
                $table->unsignedBigInteger('id_kategori')->nullable()->index();
                $table->string('kategori')->nullable();
                $table->string('klausul_annex')->nullable();

                // Detail Dokumen ISO 27001
                $table->text('latar_belakang')->nullable();
                $table->text('tujuan')->nullable();
                $table->text('ruang_lingkup')->nullable();

                // Status
                $table->unsignedBigInteger('id_status')->nullable()->index();
                $table->string('status')->default('Draft')->index(); // Draft, Sedang Ditinjau, Selesai

                // Multi-tenancy & Relasi Pengguna
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->softDeletes();
                $table->timestamps();

                // Foreign Keys
                $table->foreign('id_unit_kerja')->references('id_unit_kerja')->on('smki_unit_kerjas')->nullOnDelete();
                $table->foreign('id_kategori')->references('id_kategori')->on('smki_kategori_temuans')->nullOnDelete();
                $table->foreign('bidang_id')->references('id')->on('bidangs')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 4. Tabel Rincian Temuan Audit (DetailTemuan)
        if (!Schema::hasTable('smki_detail_temuans')) {
            Schema::create('smki_detail_temuans', function (Blueprint $table) {
                $table->id('id_detail_temuan');
                $table->unsignedBigInteger('id_laporan_audit')->index();
                $table->date('tanggal_audit')->nullable();
                $table->unsignedBigInteger('id_kategori')->nullable()->index();
                $table->string('kategori_temuan')->nullable(); // Major, Minor, OFI
                $table->string('klausul_annex')->nullable(); // contoh: A.5.15 Access Control
                $table->text('deskripsi_temuan')->nullable();
                $table->text('rekomendasi')->nullable(); // Rekomendasi Tindak Lanjut

                $table->timestamps();

                $table->foreign('id_laporan_audit')
                    ->references('id_laporan_audit')
                    ->on('smki_laporan_audits')
                    ->onDelete('cascade');
                $table->foreign('id_kategori')
                    ->references('id_kategori')
                    ->on('smki_kategori_temuans')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smki_detail_temuans');
        Schema::dropIfExists('smki_laporan_audits');
        Schema::dropIfExists('smki_kategori_temuans');
        Schema::dropIfExists('smki_unit_kerjas');
    }
};
