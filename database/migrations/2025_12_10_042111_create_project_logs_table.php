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
        Schema::create('project_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ProjectID')->constrained('project', 'id')->onDelete('cascade');
            $table->time('ProjectTIME');
            $table->date('ProjectDATE');
            $table->string('PhaseBEFORE')->nullable();
            $table->string('PhaseCURRENT');
            $table->text('ChangeREASON')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_logs');
    }
};