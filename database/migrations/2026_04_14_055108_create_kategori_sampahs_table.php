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
        Schema::create('kategori_sampahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori'); // TAMBAHAN: Ini yang akan dicocokkan dengan AI
            $table->string('nama_jenis')->unique(); // Contoh: Plastik PET, Kardus
            $table->enum('tipe', ['organik', 'anorganik', 'b3']); // Kelompoknya
            $table->decimal('harga_per_kg', 10, 2);
            $table->string('satuan')->default('kg');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sampahs');
    }
};
