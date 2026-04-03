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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('attend_date')->index();           // 출석 날짜 (YYYY-MM-DD)
            $table->time('attend_time');                    // 출석 시각
            $table->string('greeting', 255)->nullable();   // 인삿말
            $table->integer('base_point')->default(0);      // 기본 지급 포인트
            $table->integer('bonus_point')->default(0);     // 개근/순위 보너스 합계
            $table->integer('random_point')->default(0);    // 랜덤 추가 포인트
            $table->integer('daily_rank')->nullable();      // 오늘의 출석 순위
            $table->timestamps();

            // 동일 유저가 같은 날 중복 출석 방지
            $table->unique(['user_id', 'attend_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
