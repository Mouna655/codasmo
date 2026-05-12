<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up()
        {
            Schema::table('daily_productions', function (Blueprint $table) {
                $table->integer('version')->default(1)->after('id');
                $table->boolean('is_active')->default(true)->after('version');
            });
        }

        public function down()
        {
            Schema::table('daily_productions', function (Blueprint $table) {
                $table->dropColumn(['version', 'is_active']);
            });
        }
};
