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
        Schema::create('phase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ProjectID')->constrained('project', 'id')->onDelete('cascade');
            $table->string('PhaseNAME');
            $table->text('PhaseDESC')->nullable();
            $table->text('PhaseUPDATE')->nullable();
            $table->integer('PhaseORDER')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phase');
    }
};