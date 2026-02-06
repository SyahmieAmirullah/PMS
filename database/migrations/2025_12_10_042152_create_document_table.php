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
        Schema::create('document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('PhaseID')->constrained('phase', 'id')->onDelete('cascade');
            $table->string('DocumentNAME');
            $table->date('DocumentDATE');
            $table->string('DocumentVERSION')->default('1.0');
            $table->string('DocumentPATH')->nullable(); // For file storage path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document');
    }
};