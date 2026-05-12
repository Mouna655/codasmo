<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('loading_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('upload_date')->index();

            // Bulan data (April 2026 dst)
            $table->unsignedTinyInteger('data_month');   // 1-12
            $table->unsignedSmallInteger('data_year');
            $table->string('data_month_label', 30);      // "April 2026"

            // Week yang dipilih admin saat upload (untuk kolom Pen/Dem)
            $table->unsignedTinyInteger('week_number')->default(2); // W0-W5
            $table->string('pen_week_label', 30)->default('Pen. W2');
            $table->string('dem_week_label', 30)->default('Dem. W2');

            $table->string('filename');
            $table->string('original_filename');
            $table->unsignedInteger('total_rows')->default(0);

            $table->enum('status', ['processing','success','partial','failed'])->default('processing');
            $table->text('error_message')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['upload_date','data_month','data_year','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('loading_snapshots'); }
};