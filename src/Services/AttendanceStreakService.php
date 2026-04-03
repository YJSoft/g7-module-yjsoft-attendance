<?php

namespace Modules\Yjsoft\Attendance\Services;

use Carbon\Carbon;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Contracts\AttendanceStreakRepositoryInterface;
use Modules\Yjsoft\Attendance\Enums\StreakType;

/**
 * 개근(연속 출석) 서비스
 *
 * 달력 기준으로 주간/월간/연간 개근을 관리한다.
 * 핵심: "연속 N일"이 아닌, 기간 전체 출석 여부로 판정.
 */
class AttendanceStreakService
{
    public function __construct(
        private AttendanceStreakRepositoryInterface $streakRepository,
        private AttendanceRepositoryInterface $attendanceRepository,
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 세 가지 개근 타입(weekly/monthly/yearly)을 각각 처리
     *
     * @return array 달성된 개근 정보 배열
     */
    public function updateStreaks(int $userId, Carbon $today): array
    {
        $results = [];

        foreach (StreakType::cases() as $type) {
            $results[$type->value] = $this->processStreak($userId, $type, $today);
        }

        return $results;
    }

    /**
     * 오늘 출석으로 어떤 개근 보너스가 달성되는지 미리 계산
     */
    public function calculateStreakBonus(int $userId): int
    {
        $totalBonus = 0;
        $today = Carbon::today();

        foreach (StreakType::cases() as $type) {
            $period = $type->getPeriod($today);
            $periodStart = Carbon::parse($period['start']);
            $periodEnd = Carbon::parse($period['end']);
            $totalDays = $periodStart->diffInDays($periodEnd) + 1;

            // 현재 기간 내 출석 일수 (오늘 포함해서 계산)
            $attendedDays = $this->countAttendedDaysInPeriod($userId, $period['start'], $period['end']);
            // 오늘 출석을 아직 기록하기 전이라면 +1
            $attendedDays += 1;

            if ($attendedDays >= $totalDays) {
                $streak = $this->streakRepository->findCurrentStreak($userId, $type);
                // 아직 보너스를 받지 않았다면
                if (! $streak || ! $streak->bonus_paid) {
                    $totalBonus += $this->getStreakBonusPoint($type);
                }
            }
        }

        return $totalBonus;
    }

    /**
     * 개근 타입별 보너스 포인트 조회
     */
    private function getStreakBonusPoint(StreakType $type): int
    {
        return match ($type) {
            StreakType::Weekly  => (int) $this->settingsService->getSetting('bonus.weekly_streak_point', 0),
            StreakType::Monthly => (int) $this->settingsService->getSetting('bonus.monthly_streak_point', 0),
            StreakType::Yearly  => (int) $this->settingsService->getSetting('bonus.yearly_streak_point', 0),
        };
    }

    /**
     * 개별 개근 타입 처리
     */
    private function processStreak(int $userId, StreakType $type, Carbon $today): array
    {
        $period = $type->getPeriod($today);
        $periodStart = Carbon::parse($period['start']);
        $periodEnd = Carbon::parse($period['end']);
        $totalDays = $periodStart->diffInDays($periodEnd) + 1;

        // 해당 기간 내 출석 일수
        $attendedDays = $this->countAttendedDaysInPeriod($userId, $period['start'], $period['end']);

        // streak 레코드 갱신
        $streak = $this->streakRepository->upsertStreak($userId, $type, [
            'current_streak' => $attendedDays,
            'is_completed'   => $attendedDays >= $totalDays,
        ]);

        $bonusPaid = false;

        // 신규 완료이고 보너스 미지급이면 보너스 포인트 지급 표시
        if ($streak->is_completed && ! $streak->bonus_paid) {
            $this->streakRepository->markBonusPaid($streak->id);
            $bonusPaid = true;
        }

        return [
            'type'           => $type->value,
            'period_start'   => $period['start'],
            'period_end'     => $period['end'],
            'attended_days'  => $attendedDays,
            'total_days'     => $totalDays,
            'is_completed'   => $streak->is_completed,
            'bonus_paid'     => $bonusPaid,
            'bonus_point'    => $bonusPaid ? $this->getStreakBonusPoint($type) : 0,
        ];
    }

    /**
     * 특정 기간 내 출석 일수 조회
     */
    private function countAttendedDaysInPeriod(int $userId, string $startDate, string $endDate): int
    {
        return \Modules\Yjsoft\Attendance\Models\AttendanceRecord::where('user_id', $userId)
            ->whereBetween('attend_date', [$startDate, $endDate])
            ->count();
    }
}
