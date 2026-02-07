<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance', 'StaffID')) {
                $table->foreignId('StaffID')->after('MeetingID')->constrained('staff', 'id')->onDelete('cascade');
                $table->unique(['MeetingID', 'StaffID']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (Schema::hasColumn('attendance', 'StaffID')) {
                $table->dropUnique(['MeetingID', 'StaffID']);
                $table->dropConstrainedForeignId('StaffID');
            }
        });
    }
};
