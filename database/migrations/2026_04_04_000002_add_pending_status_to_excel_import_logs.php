<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add 'pending' status to excel_import_logs enum
     * Error: Status column menggunakan enum dengan hanya ['success','partial','failed']
     * Solusi: Tambahkan 'pending' sebagai valid status saat initial upload
     */
    public function up(): void {
        // MySQL: Mengubah enum type dengan membuat column baru, copy data, drop lama, rename
        // Atau lebih simple: modify table dengan procedure
        
        if (DB::getDriverName() === 'mysql') {
            // For MySQL, we need to modify the enum
            DB::statement("ALTER TABLE excel_import_logs MODIFY status ENUM('pending', 'success', 'partial', 'failed') DEFAULT 'success'");
        } else {
            // For other databases, drop and recreate (if needed)
            Schema::table('excel_import_logs', function (Blueprint $table) {
                // SQLite, PostgreSQL handling if needed
            });
        }
    }

    public function down(): void {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE excel_import_logs MODIFY status ENUM('success', 'partial', 'failed') DEFAULT 'success'");
        }
    }
};
