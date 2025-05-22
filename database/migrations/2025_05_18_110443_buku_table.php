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
        Schema::create('TabelBuku', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('penerbit');
            $table->string('tahun');
            $table->string('doi');
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('TabelBuku', function (Blueprint $table) {
            //
        });
    }
};
