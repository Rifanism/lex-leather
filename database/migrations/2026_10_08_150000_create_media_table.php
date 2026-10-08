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
        // Vercel's filesystem is read-only, so FILESYSTEM_PUBLIC_DRIVER=db
        // points the public disk here: product/QRIS uploads are stored as
        // rows and served by GET /media/{path}. Data is base64 in a TEXT
        // column so SQLite (tests) and Postgres behave identically.
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->longText('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
