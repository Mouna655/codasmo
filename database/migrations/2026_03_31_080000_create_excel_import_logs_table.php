<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('excel_import_logs', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('filename');
            $table->unsignedInteger('rows_imported')->default(0);
            $table->text('errors')->nullable();
            $table->enum('status', ['success','partial','failed'])->default('success');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('excel_import_logs'); }
};