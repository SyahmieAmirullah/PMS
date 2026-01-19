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
        Schema::create('project', function (Blueprint $table) {
            $table->id('ProjectID');
            $table->string('ProjectNAME');
            $table->text('ProjectDESC')->nullable();
            $table->enum('ProjectSTATUS', ['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planning');
            $table->string('ClientNAME');
            $table->string('ClientPHONE')->nullable();
            $table->string('ClientEMAIL')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};