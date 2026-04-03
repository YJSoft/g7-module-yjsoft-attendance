<?php

namespace Modules\Yjsoft\Attendance\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Exceptions\AlreadyAttendedException;
use Modules\Yjsoft\Attendance\Exceptions\AttendanceTimeNotAllowedException;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;

/**
 * 출석 핵심 비즈니스 로직 서비스
 *
 * 의존성: AttendanceRepositoryInterface, AttendanceStreakService,
 *         AttendanceRankService, AttendanceSettingsService
 */
class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository,
        private AttendanceStreakService $streakService,
        private AttendanceRankService $rankService,
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 출석 처리
     *
     * @throws AlreadyAttendedException
     * @throws AttendanceTimeNotAllowedException
     */
    public function attend(int $userId, string $greeting): AttendanceRecord
    {
        // 1. 중복 출석 확인
        $existing = $this->attendanceRepository->findTodayByUser($userId);
        if ($existing) {
            throw new AlreadyAttendedException();
        }

        // 2. 시간 제한 확인
        $this->checkTimeLimit();

        // 3. 기본 포인트 결정
        $basePoint = (int) $this->settingsService->getSetting('bonus.base_point', 0);

        // 4. 랜덤 포인트 결정
        $randomPoint = $this->calculateRandomPoint();

        // 5. 순위 결정
        $rank = $this->rankService->getTodayRank($userId);

        // 6. 순위 보너스
        $rankBonus = $this->rankService->getRankBonus($rank);

        // 7. 개근 보너스 미리 계산
        $streakBonus = $this->streakService->calculateStreakBonus($userId);

        // 총 보너스 (순위 + 개근)
        $totalBonus = $rankBonus + $streakBonus;

        // 8. 출석 기록 저장
        $record = $this->attendanceRepository->createRecord([
            'user_id'      => $userId,
            'attend_date'  => Carbon::today()->toDateString(),
            'attend_time'  => Carbon::now()->toTimeString(),
            'greeting'     => $greeting,
            'base_point'   => $basePoint,
            'bonus_point'  => $totalBonus,
            'random_point' => $randomPoint,
            'daily_rank'   => $rank,
        ]);

        // 9. 개근 현황 업데이트
        $this->streakService->updateStreaks($userId, Carbon::today());

        // 10. 순위 기록 갱신
        $this->rankService->updateDailyRank($userId, $record);

        // 11. 포인트 지급 (코어 포인트 API)
        $totalPoint = $basePoint + $totalBonus + $randomPoint;
        $this->awardPoints($userId, $totalPoint, $record);

        return $record;
    }

    /**
     * 출석 가능 시간대 확인
     *
     * @throws AttendanceTimeNotAllowedException
     */
    public function checkTimeLimit(): void
    {
        $enabled = (bool) $this->settingsService->getSetting('time_limit.enabled', false);
        if (! $enabled) {
            return;
        }

        $startHour = (int) $this->settingsService->getSetting('time_limit.start_hour', 0);
        $startMinute = (int) $this->settingsService->getSetting('time_limit.start_minute', 0);
        $endHour = (int) $this->settingsService->getSetting('time_limit.end_hour', 23);
        $endMinute = (int) $this->settingsService->getSetting('time_limit.end_minute', 59);

        $now = Carbon::now();
        $currentMinutes = $now->hour * 60 + $now->minute;
        $startMinutes = $startHour * 60 + $startMinute;
        $endMinutes = $endHour * 60 + $endMinute;

        if ($currentMinutes < $startMinutes || $currentMinutes > $endMinutes) {
            throw new AttendanceTimeNotAllowedException();
        }
    }

    /**
     * 랜덤 포인트 계산
     */
    private function calculateRandomPoint(): int
    {
        $enabled = (bool) $this->settingsService->getSetting('random_point.enabled', false);
        if (! $enabled) {
            return 0;
        }

        $probability = (int) $this->settingsService->getSetting('random_point.probability', 0);
        $roll = random_int(1, 100);

        if ($roll > $probability) {
            return 0;
        }

        $min = (int) $this->settingsService->getSetting('random_point.min_point', 1);
        $max = (int) $this->settingsService->getSetting('random_point.max_point', 100);

        return random_int($min, $max);
    }

    /**
     * 포인트 지급 (코어 포인트 시스템 연동)
     */
    private function awardPoints(int $userId, int $totalPoint, AttendanceRecord $record): void
    {
        if ($totalPoint <= 0) {
            return;
        }

        try {
            if (function_exists('point_insert')) {
                point_insert(
                    $userId,
                    $totalPoint,
                    __('yjsoft-attendance::messages.attend_success'),
                    'yjsoft-attendance',
                    $record->id
                );
            }
        } catch (\Exception $e) {
            Log::error('Attendance point award failed', [
                'user_id'     => $userId,
                'total_point' => $totalPoint,
                'record_id'   => $record->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
