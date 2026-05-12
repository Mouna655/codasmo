<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Nilai FC Plan dari Excel:
        // IMM=480000, TCM=110000, BEK=450000, GPK=510000, JBG=0, TIS=200000
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('fc_plan', 15, 2)->default(0);
            $table->decimal('coal_winning_plan', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['site_id', 'year', 'month']);
        });
    }
    public function down(): void { Schema::dropIfExists('production_plans'); }
};