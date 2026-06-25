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
        Schema::create('detail_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setoran_id')->constrained('setorans')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('kategori_sampahs');
            $table->decimal('berat', 8, 2);
            $table->decimal('subtotal', 12, 2);
            $table->string('path_foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_setorans');
    }
};
