<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance', 'AttandanceLAT')) {
                $table->decimal('AttandanceLAT', 10, 7)->nullable()->after('AttandanceLOCATION');
            }
            if (!Schema::hasColumn('attendance', 'AttandanceLNG')) {
                $table->decimal('AttandanceLNG', 10, 7)->nullable()->after('AttandanceLAT');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (Schema::hasColumn('attendance', 'AttandanceLNG')) {
                $table->dropColumn('AttandanceLNG');
            }
            if (Schema::hasColumn('attendance', 'AttandanceLAT')) {
                $table->dropColumn('AttandanceLAT');
            }
        });
    }
};
