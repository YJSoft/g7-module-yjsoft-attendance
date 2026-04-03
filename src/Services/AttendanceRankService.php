<?php

namespace Modules\Yjsoft\Attendance\Services;

use Carbon\Carbon;
use Modules\Yjsoft\Attendance\Models\AttendanceDailyRank;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;

/**
 * 출석 순위 서비스
 */
class AttendanceRankService
{
    public function __construct(
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 오늘 이 유저가 몇 번째로 출석하는지 반환 (1부터 시작)
     */
    public function getTodayRank(int $userId): int
    {
        $todayCount = AttendanceRecord::where('attend_date', Carbon::today()->toDateString())
            ->count();

        return $todayCount + 1;
    }

    /**
     * 순위에 따른 보너스 포인트 반환
     */
    public function getRankBonus(int $rank): int
    {
        return match ($rank) {
            1 => (int) $this->settingsService->getSetting('bonus.rank1_point', 0),
            2 => (int) $this->settingsService->getSetting('bonus.rank2_point', 0),
            3 => (int) $this->settingsService->getSetting('bonus.rank3_point', 0),
            default => 0,
        };
    }

    /**
     * rank <= 3인 경우 attendance_daily_ranks 테이블에 저장 또는 갱신
     */
    public function updateDailyRank(int $userId, AttendanceRecord $record): void
    {
        $rank = $record->daily_rank;

        if ($rank === null || $rank > 3) {
            return;
        }

        $bonusPoint = $this->getRankBonus($rank);

        AttendanceDailyRank::updateOrCreate(
            [
                'rank_date' => $record->attend_date->toDateString(),
                'rank'      => $rank,
            ],
            [
                'user_id'     => $userId,
                'bonus_point' => $bonusPoint,
                'bonus_paid'  => true,
            ]
        );
    }
}
