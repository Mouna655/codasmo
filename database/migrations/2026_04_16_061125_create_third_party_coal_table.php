<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('third_party_coal', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');      // 1–12
            $table->string('quality', 10);             // ICI 1, ICI 2, dst
            $table->string('shipper', 20);             // BBA, BBE, KMIA, dst
            $table->decimal('plan',   15, 2)->default(0);
            $table->decimal('actual', 15, 2)->default(0);
            $table->string('upload_batch', 36)->nullable(); // UUID per upload session
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu kombinasi year+month+quality+shipper per upload batch
            $table->index(['year', 'month']);
            $table->index(['quality']);
            $table->index(['shipper']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('third_party_coal');
    }
};