<?php

namespace Modules\Yjsoft\Attendance\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Yjsoft\Attendance\Contracts\AttendanceStreakRepositoryInterface;
use Modules\Yjsoft\Attendance\Enums\StreakType;
use Modules\Yjsoft\Attendance\Models\AttendanceStreak;

class AttendanceStreakRepository implements AttendanceStreakRepositoryInterface
{
    /**
     * 현재 기간의 개근 기록 조회
     */
    public function findCurrentStreak(int $userId, StreakType $type): ?AttendanceStreak
    {
        $period = $type->getPeriod(Carbon::today());

        return AttendanceStreak::where('user_id', $userId)
            ->where('streak_type', $type->value)
            ->where('period_start', $period['start'])
            ->first();
    }

    /**
     * 개근 기록 생성 또는 갱신
     */
    public function upsertStreak(int $userId, StreakType $type, array $data): AttendanceStreak
    {
        $period = $type->getPeriod(Carbon::today());

        return AttendanceStreak::updateOrCreate(
            [
                'user_id'      => $userId,
                'streak_type'  => $type->value,
                'period_start' => $period['start'],
            ],
            array_merge([
                'period_end' => $period['end'],
            ], $data)
        );
    }

    /**
     * 유저의 전체 개근 현황 (현재 기간 기준)
     */
    public function getUserStreaks(int $userId): Collection
    {
        $streaks = collect();

        foreach (StreakType::cases() as $type) {
            $streak = $this->findCurrentStreak($userId, $type);
            if ($streak) {
                $streaks->push($streak);
            }
        }

        return $streaks;
    }

    /**
     * 보너스 지급 완료 표시
     */
    public function markBonusPaid(int $streakId): void
    {
        AttendanceStreak::where('id', $streakId)
            ->update(['bonus_paid' => true]);
    }
}
