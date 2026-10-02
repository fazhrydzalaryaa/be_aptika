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
        Schema::table('detail_perjalanan', function (Blueprint $table) {
            $table->string('nomor_sp')->nullable();
            $table->string('nomor_visum')->nullable();
            $table->unsignedBigInteger('ppk_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_perjalanan', function (Blueprint $table) {
            $table->dropColumn(['nomor_sp', 'nomor_visum', 'ppk_id']);
        });
    }
};