<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Setiap upload file Excel = satu snapshot.
     * Satu snapshot berisi data 6 bulan (Month 1-6).
     * Konsep time-travel sama seperti POA & Loading.
     */
    public function up(): void {
        Schema::create('shipment_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('upload_date')->index();
            $table->string('original_filename');
            $table->string('filename');

            // Label & status per snapshot
            $table->unsignedInteger('total_rows')->default(0);
            $table->enum('status', ['processing','success','partial','failed'])
                  ->default('processing');
            $table->text('error_message')->nullable();

            $table->foreignId('uploaded_by')
                  ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['upload_date', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('shipment_snapshots'); }
};