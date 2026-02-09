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
        Schema::table('task', function (Blueprint $table) {
            if (!Schema::hasColumn('task', 'StaffID')) {
                $table->foreignId('StaffID')
                    ->nullable()
                    ->after('ProjectID')
                    ->constrained('staff', 'id')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', function (Blueprint $table) {
            if (Schema::hasColumn('task', 'StaffID')) {
                $table->dropForeign(['StaffID']);
                $table->dropColumn('StaffID');
            }
        });
    }
};
