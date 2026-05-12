<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Satu baris = satu kombinasi snapshot + year + month + company + product.
     *
     * Pemetaan kolom Excel:
     *   Kolom E "Produk"   → product
     *   Kolom F "Outlook"  → outlook
     *   Kolom G "Actual"   → actual
     *   Kolom H "Previous" → previous
     *   Kolom I "Actual Plan" → actual_plan (opsional)
     */
    public function up(): void {
        Schema::create('poa_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('snapshot_id')
                  ->constrained('poa_snapshots')
                  ->cascadeOnDelete();

            $table->unsignedSmallInteger('year');

            // Nomor bulan 1–12 (lebih efisien untuk query & sort)
            $table->unsignedTinyInteger('month_number');
            // Nama bulan asli dari Excel ("January", "February", dst)
            $table->string('month_name', 20);

            $table->string('company', 20);  // IMM, TCM, BEK, GPK, TIS, JBG, NPR
            $table->string('product', 30);  // WB.LS, EB.HS, LS, HS, dst

            $table->decimal('outlook',  12, 4)->default(0);
            $table->decimal('actual',   12, 4)->default(0);
            $table->decimal('previous', 12, 4)->default(0);

            $table->timestamps();

            // Index untuk query chart data
            $table->index(['snapshot_id', 'company', 'year']);
            $table->index(['snapshot_id', 'year', 'month_number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('poa_records');
    }
};