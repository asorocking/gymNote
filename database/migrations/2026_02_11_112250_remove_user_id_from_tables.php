<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tracker_records', function (Blueprint $table) {
            // Если был внешний ключ (foreign key), сначала удаляем его
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        // Опционально: описываем как вернуть назад, если передумаем
        Schema::table('tracker_records', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
        });
    }
};
