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
        // 1. Tabel Master: Bidang Auditee
        if (!Schema::hasTable('bidang_auditee')) {
            Schema::create('bidang_auditee', function (Blueprint $table) {
                $table->increments('id_bidang_auditee');
                $table->string('bidang_auditee', 255);
                $table->timestamps();
            });
        }

        // 2. Tabel Master: Lokasi Auditee
        if (!Schema::hasTable('lokasi_auditee')) {
            Schema::create('lokasi_auditee', function (Blueprint $table) {
                $table->increments('id_lokasi_auditee');
                $table->string('lokasi_auditee', 255);
                $table->timestamps();
            });
        }

        // 3. Tabel Auditee (Relasi Bidang & Lokasi)
        if (!Schema::hasTable('auditee')) {
            Schema::create('auditee', function (Blueprint $table) {
                $table->increments('id_auditee');
                $table->unsignedInteger('id_bidang_auditee');
                $table->unsignedInteger('id_lokasi_auditee');
                $table->timestamps();

                $table->foreign('id_bidang_auditee')
                    ->references('id_bidang_auditee')
                    ->on('bidang_auditee')
                    ->cascadeOnDelete();

                $table->foreign('id_lokasi_auditee')
                    ->references('id_lokasi_auditee')
                    ->on('lokasi_auditee')
                    ->cascadeOnDelete();
            });
        }

        // 4. Tabel Master: Auditor
        if (!Schema::hasTable('auditor')) {
            Schema::create('auditor', function (Blueprint $table) {
                $table->increments('id_auditor');
                $table->string('nama_auditor', 255);
                $table->string('nip_auditor', 100)->nullable();
                $table->timestamps();
            });
        }

        // 5. Tabel Utama: Detail Audit (Rencana Audit)
        if (!Schema::hasTable('detail_audit')) {
            Schema::create('detail_audit', function (Blueprint $table) {
                $table->increments('id_detail_audit');
                $table->unsignedInteger('id_auditor');
                $table->unsignedInteger('id_auditee');
                $table->text('kontrol_SMKI');
                $table->date('tanggal_audit');
                $table->string('kode_prosedur', 100)->nullable();
                $table->string('status', 50)->default('SCHEDULED'); // SCHEDULED, IN PROGRESS, PENDING, COMPLETED
                $table->text('catatan')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('id_auditor')
                    ->references('id_auditor')
                    ->on('auditor')
                    ->cascadeOnDelete();

                $table->foreign('id_auditee')
                    ->references('id_auditee')
                    ->on('auditee')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_audit');
        Schema::dropIfExists('auditee');
        Schema::dropIfExists('auditor');
        Schema::dropIfExists('lokasi_auditee');
        Schema::dropIfExists('bidang_auditee');
    }
};
