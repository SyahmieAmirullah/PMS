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
        Schema::create('task', function (Blueprint $table) {
            $table->id('TaskID');
            $table->foreignId('ProjectID')->constrained('project', 'ProjectID')->onDelete('cascade');
            $table->string('TaskNAME');
            $table->text('TaskDESC')->nullable();
            $table->date('TaskDUE')->nullable();
            $table->enum('TaskSTATUS', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task');
    }
};