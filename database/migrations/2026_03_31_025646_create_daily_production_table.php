<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Pemetaan langsung dari sheet "Daily Production Report" Excel:
     *
     * Kolom B  → report_date
     * Kolom C  → site (via site_id FK)
     * Kolom D  → sub-site (via sub_site_id FK)
     * Kolom E  → fc_production_daily   (per sub-site)
     * Kolom F  → fc_production_mtd     (per sub-site)
     * Kolom G  → port_stock_yard_daily (per sub-site)
     * Kolom H  → port_stock_yard_mtd   (per sub-site)
     * Kolom I  → coal_winning_daily    (NULL kecuali primary sub-site)
     * Kolom J  → coal_winning_mtd      (NULL kecuali primary sub-site)
     * Kolom K  → rom_stock             (NULL kecuali primary sub-site)
     * Kolom L  → fc_percentage         (dihitung: sum(fc_mtd) / fc_plan per site)
     * Kolom M  → fc_plan               (per site, dari Excel)
     *
     * Catatan: nilai 0.0001 di Excel = placeholder nol. Dikonversi ke 0
     * oleh helper DailyProduction::zeroIfNoise()
     */
    public function up(): void {
        Schema::create('daily_productions', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_site_id')->constrained()->cascadeOnDelete();

            // FC Production (semua sub-site)
            $table->decimal('fc_production_daily', 15, 2)->default(0);
            $table->decimal('fc_production_mtd',   15, 2)->default(0);

            // Port Stock Yard (semua sub-site)
            $table->decimal('port_stock_yard_daily', 15, 2)->default(0);
            $table->decimal('port_stock_yard_mtd',   15, 2)->default(0);

            // Hanya primary sub-site
            $table->decimal('coal_winning_daily', 15, 2)->nullable();
            $table->decimal('coal_winning_mtd',   15, 2)->nullable();
            $table->decimal('rom_stock',          15, 2)->nullable();

            // FC Plan per site (dari Excel)
            $table->decimal('fc_plan', 15, 2)->default(0);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('input_at')->nullable();
            $table->timestamps();

            $table->unique(['report_date', 'site_id', 'sub_site_id'], 'dp_unique');
            $table->index(['report_date', 'site_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('daily_productions'); }
};