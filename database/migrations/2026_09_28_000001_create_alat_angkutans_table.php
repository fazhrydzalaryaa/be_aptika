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
        if (!Schema::hasTable('alat_angkutans')) {
            Schema::create('alat_angkutans', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100)->unique();
                $table->string('deskripsi', 255)->nullable();
                $table->timestamps();
            });

            // Seed default options
            $now = now();
            $defaults = [
                ['nama' => 'Kendaraan Dinas', 'deskripsi' => 'Mobil atau motor dinas operasional', 'created_at' => $now, 'updated_at' => $now],
                ['nama' => 'Pesawat Udara', 'deskripsi' => 'Transportasi udara', 'created_at' => $now, 'updated_at' => $now],
                ['nama' => 'Kereta Api', 'deskripsi' => 'Transportasi kereta api / KAI / Whoosh', 'created_at' => $now, 'updated_at' => $now],
                ['nama' => 'Kapal Laut', 'deskripsi' => 'Transportasi laut / penyeberangan feri', 'created_at' => $now, 'updated_at' => $now],
                ['nama' => 'Kendaraan Darat Lainnya', 'deskripsi' => 'Bus, travel, sewa kendaraan, dll', 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('alat_angkutans')->insert($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat_angkutans');
    }
};
