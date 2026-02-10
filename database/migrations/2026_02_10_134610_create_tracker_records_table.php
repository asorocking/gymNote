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
        Schema::create('tracker_records', function (Blueprint $table) {
            $table->string('id')->primary(); // ID из JSON типа 1769499122344.7585
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mode')->index(); // gym, pressure, shop и т.д.
            $table->date('date_key')->index();
            $table->string('description')->nullable();
            $table->string('val1')->nullable();
            $table->string('val2')->nullable();
            $table->string('val3')->nullable();
            $table->string('weight')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_records');
    }
};
