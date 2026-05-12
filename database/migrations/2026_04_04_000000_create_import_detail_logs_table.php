<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Audit trail untuk setiap error yang terjadi saat import
     * Menyimpan detail error per baris untuk debugging dan reporting
     */
    public function up(): void {
        Schema::create('import_detail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')
                  ->constrained('excel_import_logs')
                  ->cascadeOnDelete();
            
            // Info baris & lokasi error
            $table->unsignedInteger('row_number');
            $table->string('site_code')->nullable();
            $table->string('sub_site_code')->nullable();
            
            // Detail error
            $table->string('error_field')->nullable()
                  ->comment('Nama field yang error (site, sub_site, fc_daily, dll)');
            $table->text('error_message')
                  ->comment('Detail pesan error untuk debugging');
            $table->text('error_value')->nullable()
                  ->comment('Nilai yang disubmit untuk reference');
            
            // Meta
            $table->enum('error_type', [
                'format_error',      // Format file tidak sesuai
                'header_mismatch',   // Header kolom tidak match
                'not_found',         // Site/SubSite tidak ditemukan
                'validation_error',  // Validasi data gagal
                'type_error',        // Type casting gagal
                'business_logic',    // Business logic error
                'unknown'            // Error tidak terdidentifikasi
            ])->default('unknown');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Index untuk query cepat
            $table->index(['import_log_id', 'row_number']);
            $table->index(['import_log_id', 'error_type']);
            $table->index('created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('import_detail_logs');
    }
};
