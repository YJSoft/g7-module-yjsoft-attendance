<?php

namespace Modules\Yjsoft\Attendance\Tests\Unit\Services;

use Carbon\Carbon;
use Mockery;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Exceptions\AlreadyAttendedException;
use Modules\Yjsoft\Attendance\Exceptions\AttendanceTimeNotAllowedException;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;
use Modules\Yjsoft\Attendance\Services\AttendanceRankService;
use Modules\Yjsoft\Attendance\Services\AttendanceService;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;
use Modules\Yjsoft\Attendance\Services\AttendanceStreakService;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class AttendanceServiceTest extends ModuleTestCase
{
    private AttendanceService $service;
    private $mockRepository;
    private $mockStreakService;
    private $mockRankService;
    private $mockSettingsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(AttendanceRepositoryInterface::class);
        $this->mockStreakService = Mockery::mock(AttendanceStreakService::class);
        $this->mockRankService = Mockery::mock(AttendanceRankService::class);
        $this->mockSettingsService = Mockery::mock(AttendanceSettingsService::class);

        $this->service = new AttendanceService(
            $this->mockRepository,
            $this->mockStreakService,
            $this->mockRankService,
            $this->mockSettingsService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 정상 출석 처리
     */
    public function test_attend_success(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03 10:00:00'));

        $userId = 1;
        $greeting = '안녕하세요';

        $this->mockRepository->shouldReceive('findTodayByUser')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.enabled', false)
            ->andReturn(false);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.base_point', 0)
            ->andReturn(10);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.enabled', false)
            ->andReturn(false);

        $this->mockRankService->shouldReceive('getTodayRank')
            ->with($userId)
            ->once()
            ->andReturn(1);

        $this->mockRankService->shouldReceive('getRankBonus')
            ->with(1)
            ->once()
            ->andReturn(100);

        $this->mockStreakService->shouldReceive('calculateStreakBonus')
            ->with($userId)
            ->once()
            ->andReturn(0);

        $record = new AttendanceRecord([
            'id'           => 1,
            'user_id'      => $userId,
            'attend_date'  => '2026-04-03',
            'attend_time'  => '10:00:00',
            'greeting'     => $greeting,
            'base_point'   => 10,
            'bonus_point'  => 100,
            'random_point' => 0,
            'daily_rank'   => 1,
        ]);
        $record->id = 1;

        $this->mockRepository->shouldReceive('createRecord')
            ->once()
            ->andReturn($record);

        $this->mockStreakService->shouldReceive('updateStreaks')
            ->once();

        $this->mockRankService->shouldReceive('updateDailyRank')
            ->once();

        $result = $this->service->attend($userId, $greeting);

        $this->assertInstanceOf(AttendanceRecord::class, $result);
        $this->assertEquals($userId, $result->user_id);

        Carbon::setTestNow();
    }

    /**
     * 오늘 이미 출석 시 AlreadyAttendedException 발생
     */
    public function test_attend_duplicate_throws(): void
    {
        $userId = 1;
        $existingRecord = new AttendanceRecord(['user_id' => $userId]);

        $this->mockRepository->shouldReceive('findTodayByUser')
            ->with($userId)
            ->once()
            ->andReturn($existingRecord);

        $this->expectException(AlreadyAttendedException::class);

        $this->service->attend($userId, '안녕하세요');
    }

    /**
     * 출석 가능 시간 외 → AttendanceTimeNotAllowedException
     */
    public function test_attend_time_not_allowed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03 23:30:00'));

        $userId = 1;

        $this->mockRepository->shouldReceive('findTodayByUser')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.enabled', false)
            ->andReturn(true);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.start_hour', 0)
            ->andReturn(9);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.start_minute', 0)
            ->andReturn(0);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.end_hour', 23)
            ->andReturn(18);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.end_minute', 59)
            ->andReturn(0);

        $this->expectException(AttendanceTimeNotAllowedException::class);

        $this->service->attend($userId, '안녕하세요');

        Carbon::setTestNow();
    }

    /**
     * 출석 가능 시간 내 → 정상 처리
     */
    public function test_attend_time_allowed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03 12:00:00'));

        $userId = 1;

        $this->mockRepository->shouldReceive('findTodayByUser')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.enabled', false)
            ->andReturn(true);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.start_hour', 0)
            ->andReturn(9);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.start_minute', 0)
            ->andReturn(0);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.end_hour', 23)
            ->andReturn(18);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.end_minute', 59)
            ->andReturn(0);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.base_point', 0)
            ->andReturn(10);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.enabled', false)
            ->andReturn(false);

        $this->mockRankService->shouldReceive('getTodayRank')
            ->once()
            ->andReturn(1);

        $this->mockRankService->shouldReceive('getRankBonus')
            ->once()
            ->andReturn(100);

        $this->mockStreakService->shouldReceive('calculateStreakBonus')
            ->once()
            ->andReturn(0);

        $record = new AttendanceRecord([
            'id'           => 1,
            'user_id'      => $userId,
            'attend_date'  => '2026-04-03',
            'attend_time'  => '12:00:00',
            'greeting'     => '안녕하세요',
            'base_point'   => 10,
            'bonus_point'  => 100,
            'random_point' => 0,
            'daily_rank'   => 1,
        ]);
        $record->id = 1;

        $this->mockRepository->shouldReceive('createRecord')
            ->once()
            ->andReturn($record);

        $this->mockStreakService->shouldReceive('updateStreaks')->once();
        $this->mockRankService->shouldReceive('updateDailyRank')->once();

        $result = $this->service->attend($userId, '안녕하세요');

        $this->assertInstanceOf(AttendanceRecord::class, $result);

        Carbon::setTestNow();
    }

    /**
     * 시간 제한 미사용 시 항상 허용
     */
    public function test_attend_no_time_limit(): void
    {
        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.enabled', false)
            ->andReturn(false);

        // checkTimeLimit은 예외 없이 통과해야 함
        $this->service->checkTimeLimit();
        $this->assertTrue(true);
    }

    /**
     * 랜덤 포인트 설정 시 확률에 따라 지급
     */
    public function test_attend_random_point_applied(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03 10:00:00'));

        $userId = 1;

        $this->mockRepository->shouldReceive('findTodayByUser')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('time_limit.enabled', false)
            ->andReturn(false);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('bonus.base_point', 0)
            ->andReturn(10);

        // 랜덤 포인트 사용, 확률 100%
        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.enabled', false)
            ->andReturn(true);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.probability', 0)
            ->andReturn(100);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.min_point', 1)
            ->andReturn(50);

        $this->mockSettingsService->shouldReceive('getSetting')
            ->with('random_point.max_point', 100)
            ->andReturn(50);

        $this->mockRankService->shouldReceive('getTodayRank')
            ->once()
            ->andReturn(1);

        $this->mockRankService->shouldReceive('getRankBonus')
            ->once()
            ->andReturn(0);

        $this->mockStreakService->shouldReceive('calculateStreakBonus')
            ->once()
            ->andReturn(0);

        $record = new AttendanceRecord([
            'id'           => 1,
            'user_id'      => $userId,
            'attend_date'  => '2026-04-03',
            'attend_time'  => '10:00:00',
            'greeting'     => '',
            'base_point'   => 10,
            'bonus_point'  => 0,
            'random_point' => 50,
            'daily_rank'   => 1,
        ]);
        $record->id = 1;

        $this->mockRepository->shouldReceive('createRecord')
            ->once()
            ->andReturn($record);

        $this->mockStreakService->shouldReceive('updateStreaks')->once();
        $this->mockRankService->shouldReceive('updateDailyRank')->once();

        $result = $this->service->attend($userId, '');

        $this->assertInstanceOf(AttendanceRecord::class, $result);

        Carbon::setTestNow();
    }
}
