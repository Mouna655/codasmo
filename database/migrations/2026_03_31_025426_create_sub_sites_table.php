<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sub_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            // IMM: WB LS, WB HS, EB LS, EB MS, EB HS
            // TCM: LS, HS, MS  |  BEK: LS, HS
            // GPK/JBG/TIS/ITMG: satu sub-site masing-masing
            $table->string('name');
            $table->boolean('is_primary')->default(false);
            // is_primary = true → baris yang menyimpan coal_winning & rom_stock
            $table->string('chart_color', 30)->default('#1B2A8A');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['site_id', 'code']);
        });
    }
    public function down(): void { Schema::dropIfExists('sub_sites'); }
};