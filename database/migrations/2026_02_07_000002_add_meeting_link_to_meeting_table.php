<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting', function (Blueprint $table) {
            if (!Schema::hasColumn('meeting', 'MeetingLINK')) {
                $table->string('MeetingLINK')->nullable()->after('MeetingTIME');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meeting', function (Blueprint $table) {
            if (Schema::hasColumn('meeting', 'MeetingLINK')) {
                $table->dropColumn('MeetingLINK');
            }
        });
    }
};
