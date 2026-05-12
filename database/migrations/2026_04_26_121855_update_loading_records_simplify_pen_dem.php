<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Perubahan:
     * - Hapus pw0-pw5, pw_act, dw0-dw5, dw_act (tidak lagi relevan)
     * - Tambah pen_value (dari kolom CC/PW2, index 80)
     * - Tambah dem_value (dari kolom CK/DW3, index 88)
     * - Nilai ini FIXED, tidak bergantung week — label minggu
     *   hanya untuk display (disimpan di loading_snapshots)
     */
    public function up(): void {
        Schema::table('loading_records', function (Blueprint $table) {
            // Hapus kolom pw/dw lama jika sudah ada
            $cols = ['pw0','pw1','pw2','pw3','pw4','pw5','pw_act',
                     'dw0','dw1','dw2','dw3','dw4','dw5','dw_act'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('loading_records', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('loading_records', function (Blueprint $table) {
            // Tambah kolom baru yang sederhana
            $table->decimal('pen_value', 12, 8)->nullable()->after('cv_ar');
            $table->decimal('dem_value', 12, 8)->nullable()->after('pen_value');
        });
    }

    public function down(): void {
        Schema::table('loading_records', function (Blueprint $table) {
            $table->dropColumn(['pen_value','dem_value']);
        });
    }
};