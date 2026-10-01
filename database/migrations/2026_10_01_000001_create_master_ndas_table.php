<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('master_ndas')) {
            Schema::create('master_ndas', function (Blueprint $table) {
                $table->id();
                $table->string('judul', 255);
                $table->string('nama_pihak_pertama', 255);
                $table->string('nip_pihak_pertama', 100)->nullable();
                $table->string('jabatan_pihak_pertama', 255)->nullable();
                $table->string('instansi_pihak_pertama', 255)->default('Dinas Komunikasi dan Informatika Provinsi Jawa Barat');
                $table->text('klausul_perjanjian')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default NDA
            $now = now();
            DB::table('master_ndas')->insert([
                [
                    'judul' => 'Perjanjian Kerahasiaan (NDA) Peserta Magang / PKL',
                    'nama_pihak_pertama' => 'Dian Istanti, S.Sos, MAP',
                    'nip_pihak_pertama' => '19690519 199803 2 001',
                    'jabatan_pihak_pertama' => 'Kepala Bidang Aplikasi Informatika',
                    'instansi_pihak_pertama' => 'Dinas Komunikasi dan Informatika Provinsi Jawa Barat',
                    'klausul_perjanjian' => 'Menjaga seluruh kerahasiaan data, source code aplikasi, arsitektur sistem, kredensial, dan dokumen teknis Pemerintah Provinsi Jawa Barat selama dan sesudah masa magang.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'judul' => 'Perjanjian Kerahasiaan (NDA) Tenaga Ahli / Konsultan IT',
                    'nama_pihak_pertama' => 'Dian Istanti, S.Sos, MAP',
                    'nip_pihak_pertama' => '19690519 199803 2 001',
                    'jabatan_pihak_pertama' => 'Kepala Bidang Aplikasi Informatika',
                    'instansi_pihak_pertama' => 'Dinas Komunikasi dan Informatika Provinsi Jawa Barat',
                    'klausul_perjanjian' => 'Kewajiban menjaga kerahasiaan kode sumber, skema database, API key, dan konfigurasi server dalam proyek pengembangan aplikasi Pemdaprov Jabar.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ndas');
    }
};

