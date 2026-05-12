<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Pemetaan kolom Excel → DB:
     *
     *  [0]  no_row           → No.
     *  [1]  no_mahakam       → No Mahakam (0=BoCT, 1+=Mahakam urutan)
     *  [3]  company          → EBP, IMM, BEK, GPK
     *  [4]  shipment_type    → Vessel, Direct Shipment, Dump Truck
     *  [5]  vessel_name
     *  [7]  end_user
     *  [8]  load_port        → BoCT / Muara Berau / GPK Port
     *  [10] eta              → ETA S. string
     *  [12] etb              → ETB S. string
     *  [14] etd              → ETD S. string
     *  [61] total_tonnage
     *  [63] lay              → Lay S. string
     *  [65] can              → Can S. string
     *  [66] pct_shipper
     *  [67] status
     *  [74] ts_ar
     *  [76] cv_ar
     *  [78-83] pw0-pw5       → penalty per week
     *  [85-90] dw0-dw5       → demurrage per week
     *  [15-27] produk tonase
     */
    public function up(): void {
        Schema::create('loading_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('loading_snapshots')->cascadeOnDelete();

            // Identifikasi baris
            $table->unsignedSmallInteger('no_row');
            $table->unsignedSmallInteger('no_mahakam')->default(0);

            // Informasi shipment
            $table->string('company', 20);
            $table->string('shipment_type', 30);
            $table->string('vessel_name', 120)->nullable();
            $table->string('end_user', 150)->nullable();
            $table->string('load_port', 30);   // BoCT / Muara Berau / GPK Port

            // Tanggal (string formatted)
            $table->string('eta', 20)->nullable();
            $table->string('etb', 20)->nullable();
            $table->string('etd', 20)->nullable();
            $table->string('lay', 20)->nullable();
            $table->string('can', 20)->nullable();

            // Tonnage & status
            $table->decimal('total_tonnage', 14, 2)->default(0);
            $table->decimal('pct_shipper',   8, 6)->default(0);
            $table->string('status', 20);

            // Quality
            $table->decimal('ts_ar', 8, 4)->nullable();
            $table->decimal('cv_ar', 10, 2)->nullable();

            // Penalty W0-W5 dan Demurrage W0-W5
            $table->decimal('pw0', 10, 6)->nullable();
            $table->decimal('pw1', 10, 6)->nullable();
            $table->decimal('pw2', 10, 6)->nullable();
            $table->decimal('pw3', 10, 6)->nullable();
            $table->decimal('pw4', 10, 6)->nullable();
            $table->decimal('pw5', 10, 6)->nullable();
            $table->decimal('pw_act', 10, 6)->nullable();

            $table->decimal('dw0', 10, 6)->nullable();
            $table->decimal('dw1', 10, 6)->nullable();
            $table->decimal('dw2', 10, 6)->nullable();
            $table->decimal('dw3', 10, 6)->nullable();
            $table->decimal('dw4', 10, 6)->nullable();
            $table->decimal('dw5', 10, 6)->nullable();
            $table->decimal('dw_act', 10, 6)->nullable();

            // Produk tonase — untuk BoCT "Total by Product" pie chart
            // IMM
            $table->decimal('t_imm_wb_ls', 14, 2)->default(0);
            $table->decimal('t_imm_wb_hs', 14, 2)->default(0);
            $table->decimal('t_imm_eb_ls', 14, 2)->default(0);
            $table->decimal('t_imm_eb_ms', 14, 2)->default(0);
            $table->decimal('t_imm_eb_hs', 14, 2)->default(0);
            // TCM
            $table->decimal('t_tcm_ls', 14, 2)->default(0);
            $table->decimal('t_tcm_hs', 14, 2)->default(0);
            $table->decimal('t_tcm_ms', 14, 2)->default(0);
            // BEK
            $table->decimal('t_bek_ls', 14, 2)->default(0);
            $table->decimal('t_bek_hs', 14, 2)->default(0);
            // Single
            $table->decimal('t_jbg', 14, 2)->default(0);
            $table->decimal('t_gpk', 14, 2)->default(0);
            $table->decimal('t_tis', 14, 2)->default(0);

            $table->timestamps();
            $table->index(['snapshot_id','load_port','status']);
            $table->index(['snapshot_id','company']);
        });
    }
    public function down(): void { Schema::dropIfExists('loading_records'); }
};