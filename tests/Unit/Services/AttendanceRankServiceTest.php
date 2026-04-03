<?php

namespace Modules\Yjsoft\Attendance\Tests\Unit\Services;

use Carbon\Carbon;
use Mockery;
use Modules\Yjsoft\Attendance\Models\AttendanceDailyRank;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;
use Modules\Yjsoft\Attendance\Services\AttendanceRankService;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class AttendanceRankServiceTest extends ModuleTestCase
{
    private AttendanceRankService $service;
    private $mockSettingsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSettingsService = Mockery::mock(AttendanceSettingsService::class);

        $this->service = new AttendanceRankService(
            $this->mockSettingsService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 첫 번째 출석자는 1위
     */
    public function test_first_attender_rank_1(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03'));

        // DB에 오늘 출석 기록 없음 → count = 0, rank = 1
        $rank = $this->service->getTodayRank(1);

        $this->assertEquals(1, $rank);
    }

    /**
     * 1위 보너스 포인트 반환
     */
    public function test_rank_bonus_for_rank1(): void
    {
        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.rank1_point', 0)
            ->once()
            ->andReturn(100);

        $bonus = $this->service->getRankBonus(1);

        $this->assertEquals(100, $bonus);
    }

    /**
     * 2위 보너스 포인트 반환
     */
    public function test_rank_bonus_for_rank2(): void
    {
        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.rank2_point', 0)
            ->once()
            ->andReturn(50);

        $bonus = $this->service->getRankBonus(2);

        $this->assertEquals(50, $bonus);
    }

    /**
     * 3위 보너스 포인트 반환
     */
    public function test_rank_bonus_for_rank3(): void
    {
        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.rank3_point', 0)
            ->once()
            ->andReturn(30);

        $bonus = $this->service->getRankBonus(3);

        $this->assertEquals(30, $bonus);
    }

    /**
     * 4위 이상은 순위 보너스 0
     */
    public function test_no_bonus_for_rank4_and_below(): void
    {
        $bonus4 = $this->service->getRankBonus(4);
        $bonus5 = $this->service->getRankBonus(5);
        $bonus100 = $this->service->getRankBonus(100);

        $this->assertEquals(0, $bonus4);
        $this->assertEquals(0, $bonus5);
        $this->assertEquals(0, $bonus100);
    }

    /**
     * 상위 3위까지만 attendance_daily_ranks에 기록
     */
    public function test_daily_rank_record_created_for_top3(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03'));

        $userId = 1;
        $today = Carbon::today()->toDateString();

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.rank1_point', 0)
            ->andReturn(100);

        // 1위 기록 생성
        $record1 = new AttendanceRecord([
            'user_id'     => $userId,
            'attend_date' => $today,
            'daily_rank'  => 1,
        ]);
        $record1->attend_date = Carbon::parse($today);

        $this->service->updateDailyRank($userId, $record1);

        $dailyRank = AttendanceDailyRank::where('rank_date', $today)
            ->where('rank', 1)
            ->first();

        $this->assertNotNull($dailyRank);
        $this->assertEquals($userId, $dailyRank->user_id);
        $this->assertEquals(100, $dailyRank->bonus_point);
        $this->assertTrue($dailyRank->bonus_paid);
    }

    /**
     * 4위 이상은 daily_ranks에 기록되지 않음
     */
    public function test_no_daily_rank_record_for_rank4_and_below(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03'));

        $userId = 1;
        $today = Carbon::today()->toDateString();

        // 4위 기록은 저장되지 않아야 함
        $record4 = new AttendanceRecord([
            'user_id'     => $userId,
            'attend_date' => $today,
            'daily_rank'  => 4,
        ]);
        $record4->attend_date = Carbon::parse($today);

        $this->service->updateDailyRank($userId, $record4);

        $dailyRank = AttendanceDailyRank::where('rank_date', $today)
            ->where('user_id', $userId)
            ->first();

        $this->assertNull($dailyRank);
    }

    /**
     * rank가 null인 경우에도 기록되지 않음
     */
    public function test_no_daily_rank_record_for_null_rank(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03'));

        $userId = 1;
        $today = Carbon::today()->toDateString();

        $record = new AttendanceRecord([
            'user_id'     => $userId,
            'attend_date' => $today,
            'daily_rank'  => null,
        ]);
        $record->attend_date = Carbon::parse($today);

        $this->service->updateDailyRank($userId, $record);

        $dailyRank = AttendanceDailyRank::where('rank_date', $today)
            ->where('user_id', $userId)
            ->first();

        $this->assertNull($dailyRank);
    }
}
