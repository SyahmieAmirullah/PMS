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
            $table->time('ProjectTIME')->nullable()->change();
            $table->date('ProjectDATE')->nullable()->change();
            $table->string('PhaseCURRENT')->nullable()->change();
            $table->string('PhaseBEFORE')->nullable()->change();
            $table->text('ChangeREASON')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_logs', function (Blueprint $table) {
            $table->time('ProjectTIME')->nullable(false)->change();
            $table->date('ProjectDATE')->nullable(false)->change();
            $table->string('PhaseCURRENT')->nullable(false)->change();
            $table->string('PhaseBEFORE')->nullable()->change();
            $table->text('ChangeREASON')->nullable()->change();
        });
    }
};
