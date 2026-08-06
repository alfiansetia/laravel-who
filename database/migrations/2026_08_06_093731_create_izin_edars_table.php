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
        Schema::create('izin_edars', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->default('other')->index(); // AKD, AKL, PKD, PKL, other

            // 1. Kolom Umum (Ada di AKD, AKL, PKD, PKL)
            $table->string('nomor_izin_edar')->unique();
            $table->date('tgl_terbit')->nullable();
            $table->date('tgl_exp')->nullable();
            $table->string('merk')->index();
            $table->string('jenis_produk')->nullable();
            $table->string('pendaftar')->index();
            $table->text('alamat_pendaftar')->nullable();
            $table->string('pabrik')->nullable();
            $table->text('alamat_pabrik')->nullable();

            // 2. Kolom Khusus (Hanya ada di AKD & AKL) -> Nullable
            $table->string('sub_kategori')->nullable();
            $table->string('kelompok_produk')->nullable();
            $table->string('tipe')->nullable();
            $table->string('kelas', 10)->nullable();
            $table->string('kelas_resiko')->nullable();
            $table->text('pabrik2')->nullable();

            $table->timestamps();

            // Index gabungan untuk kecepatan query filter
            $table->index(['kategori', 'merk']);
            $table->index(['kategori', 'pendaftar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_edars');
    }
};
