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
        Schema::create('meeting', function (Blueprint $table) {
            $table->id('MeetingID');
            $table->foreignId('ProjectID')->constrained('project', 'ProjectID')->onDelete('cascade');
            $table->string('MeetingTITLE');
            $table->text('MeetingOBJECTIVE')->nullable();
            $table->date('MeetingDATE');
            $table->time('MeetingTIME');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting');
    }
};