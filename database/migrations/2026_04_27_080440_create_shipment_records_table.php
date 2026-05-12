<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Kolom kritis dari sheet Month 1-6:
     *   [55] Total      → total_tonnage
     *   [58] %          → pct_shipper (decimal 0.10 = 10%)
     *   [59] Status     → status
     *   [66] TS (AR)    → ts_ar
     *   [68] CV (AR)    → cv_ar
     *   [69] CV (NAR)   → cv_nar
     */
    public function up(): void {
        Schema::create('shipment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')
                  ->constrained('shipment_snapshots')
                  ->cascadeOnDelete();

            // Sheet asal: 1=Month1(April), 2=Month2(May), dst
            $table->unsignedTinyInteger('month_number');
            // Label tampil: 'April 2026', 'May 2026', dst
            $table->string('month_label', 30);
            // Tanggal aktual bulan dari Excel (col [2])
            $table->date('month_date')->nullable();

            // Data baris
            $table->unsignedSmallInteger('no_row');
            $table->string('company', 20)->nullable();
            $table->string('shipment_type', 30)->nullable();
            $table->string('vessel_name', 150)->nullable();
            $table->string('buyer', 200)->nullable();
            $table->string('end_user', 200)->nullable();
            $table->string('load_port', 30)->nullable();

            // Tanggal (DateTime → string display)
            $table->string('eta', 30)->nullable();
            $table->string('etb', 30)->nullable();
            $table->string('etd', 30)->nullable();

            // Metrics
            $table->decimal('total_tonnage', 14, 2)->default(0);
            $table->decimal('pct_shipper',   10, 8)->default(0);
            $table->string('status', 30)->nullable();

            // Quality
            $table->decimal('ts_ar',  8, 4)->nullable();
            $table->decimal('cv_ar',  10, 2)->nullable();
            $table->decimal('cv_nar', 10, 2)->nullable();

            $table->timestamps();

            $table->index(['snapshot_id', 'month_number', 'load_port']);
            $table->index(['snapshot_id', 'month_number', 'company']);
        });
    }
    public function down(): void { Schema::dropIfExists('shipment_records'); }
};