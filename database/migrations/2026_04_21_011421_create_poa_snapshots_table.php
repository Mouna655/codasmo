<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Setiap upload Excel = satu snapshot.
     * Konsep: user bisa "time travel" ke snapshot tanggal tertentu.
     */
    public function up(): void {
        Schema::create('poa_snapshots', function (Blueprint $table) {
            $table->id();

            // Tanggal upload (misal: 2026-04-21) — diisi manual oleh operator
            $table->date('upload_date')->index();

            // Tahun data yang terbaca dari sheet Excel (2025, 2026, dst)
            $table->unsignedSmallInteger('data_year');

            $table->string('filename');
            $table->string('original_filename');
            $table->integer('total_rows')->default(0);

            // Status: processing | success | failed
            $table->enum('status', ['processing', 'success', 'failed'])->default('processing');
            $table->text('error_message')->nullable();

            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Index gabungan untuk query "snapshot terbaru sebelum tanggal X"
            $table->index(['upload_date', 'data_year', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('poa_snapshots');
    }
};