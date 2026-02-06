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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('MeetingID')->constrained('meeting', 'id')->onDelete('cascade');
            $table->enum('AttandanceSTATUS', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->date('AttandanceDATE');
            $table->time('AttandanceTIME')->nullable();
            $table->string('AbsentREASON')->nullable();
            $table->string('AttandanceLOCATION')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};