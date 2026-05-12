<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * week_number tidak lagi diperlukan karena data CC dan CK sudah fixed.
     * Label tetap ada di pen_week_label dan dem_week_label untuk display.
     */
    public function up(): void {
        Schema::table('loading_snapshots', function (Blueprint $table) {
            if (Schema::hasColumn('loading_snapshots', 'week_number')) {
                $table->dropColumn('week_number');
            }
        });
    }
    public function down(): void {
        Schema::table('loading_snapshots', function (Blueprint $table) {
            $table->unsignedTinyInteger('week_number')->default(2)->after('data_month_label');
        });
    }
};