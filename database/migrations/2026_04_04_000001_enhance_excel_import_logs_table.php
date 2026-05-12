<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Enhance excel_import_logs table untuk tracking error count
     * Backward compatible: keep errors column, add errors_count untuk quick query
     */
    public function up(): void {
        Schema::table('excel_import_logs', function (Blueprint $table) {
            // Tambah errors_count untuk quick aggregation
            $table->unsignedInteger('errors_count')
                  ->default(0)
                  ->after('rows_imported')
                  ->index();
            
            // Tambah file_type untuk membedakan CSV/Excel
            $table->enum('file_type', ['csv', 'xlsx', 'xls'])
                  ->default('xlsx')
                  ->after('filename');
        });
    }

    public function down(): void {
        Schema::table('excel_import_logs', function (Blueprint $table) {
            $table->dropIndex(['errors_count']);
            $table->dropColumn('errors_count', 'file_type');
        });
    }
};
