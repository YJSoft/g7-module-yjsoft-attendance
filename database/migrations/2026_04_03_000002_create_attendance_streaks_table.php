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
        Schema::create('attendance_streaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('streak_type')
                ->comment('개근 타입: weekly, monthly, yearly');

            // 개근 중인 기간의 시작/종료 날짜
            $table->date('period_start');   // 예: 2026-03-01 (월간)
            $table->date('period_end');     // 예: 2026-03-31 (월간)

            // 현재 연속 출석 일수
            $table->integer('current_streak')->default(0);

            // 개근 달성 여부
            $table->boolean('is_completed')->default(false);

            // 개근 보너스 지급 여부 (중복 지급 방지)
            $table->boolean('bonus_paid')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'streak_type', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_streaks');
    }
};
