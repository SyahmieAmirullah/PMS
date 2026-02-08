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
        Schema::table('project_logs', function (Blueprint $table) {
            $table->string('LogTITLE')->nullable();
            $table->text('LogDESC')->nullable();
            $table->string('LogTYPE')->nullable();
            $table->dateTime('LogDATE')->nullable();
            $table->foreignId('StaffID')->nullable()->constrained('staff', 'id')->nullOnDelete();
            $table->json('LogATTACHMENTS')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('StaffID');
            $table->dropColumn([
                'LogTITLE',
                'LogDESC',
                'LogTYPE',
                'LogDATE',
                'LogATTACHMENTS',
            ]);
        });
    }
};
