<?php

namespace Modules\Yjsoft\Attendance\Contracts;

use Modules\Yjsoft\Attendance\Models\AttendanceStreak;
use Modules\Yjsoft\Attendance\Enums\StreakType;
use Illuminate\Support\Collection;

interface AttendanceStreakRepositoryInterface
{
    /**
     * 현재 기간의 개근 기록 조회
     */
    public function findCurrentStreak(int $userId, StreakType $type): ?AttendanceStreak;

    /**
     * 개근 기록 생성 또는 갱신
     */
    public function upsertStreak(int $userId, StreakType $type, array $data): AttendanceStreak;

    /**
     * 유저의 전체 개근 현황
     */
    public function getUserStreaks(int $userId): Collection;

    /**
     * 보너스 지급 완료 표시
     */
    public function markBonusPaid(int $streakId): void;
}
