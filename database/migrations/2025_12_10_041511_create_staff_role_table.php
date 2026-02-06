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
        Schema::create('staff_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('StaffID')->constrained('staff', 'id')->onDelete('cascade');
            $table->string('RoleDESC');
            $table->string('RoleTYPE');
            $table->string('RolePRO')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_role');
    }
};