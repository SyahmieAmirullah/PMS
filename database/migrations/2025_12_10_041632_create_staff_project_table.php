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
        Schema::create('staff_project', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->foreignId('StaffID')->constrained('staff', 'StaffID')->onDelete('cascade');
            $table->foreignId('ProjectID')->constrained('project', 'ProjectID')->onDelete('cascade');
            $table->date('ProjectSTART')->nullable();
            $table->date('ProjectDUE')->nullable();
            $table->timestamps();

            // Make StaffID + ProjectID unique to prevent duplicates
            $table->unique(['StaffID', 'ProjectID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_project');
    }
};
