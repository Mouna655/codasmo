<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();  // IMM, TCM, BEK, GPK, JBG, TIS, ITMG
            $table->string('name');
            $table->boolean('is_parent')->default(false); // true = ITMG (baris total)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sites'); }
};