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
        Schema::create('attendance_daily_ranks', function (Blueprint $table) {
            $table->id();
            $table->date('rank_date')->index();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('rank');              // 1, 2, 3
            $table->integer('bonus_point');           // 해당 순위 보너스 포인트
            $table->boolean('bonus_paid')->default(false); // 보너스 지급 여부
            $table->timestamps();

            $table->unique(['rank_date', 'rank']);
            $table->index(['rank_date', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_daily_ranks');
    }
};
