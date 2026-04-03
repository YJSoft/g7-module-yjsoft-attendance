<?php

namespace Modules\Yjsoft\Attendance\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * 오늘 출석 기록 조회
     */
    public function findTodayByUser(int $userId): ?AttendanceRecord
    {
        return AttendanceRecord::byUser($userId)
            ->where('attend_date', Carbon::today()->toDateString())
            ->first();
    }

    /**
     * 출석 기록 생성
     */
    public function createRecord(array $data): AttendanceRecord
    {
        return AttendanceRecord::create($data);
    }

    /**
     * 월별 출석 목록
     */
    public function getMonthlyRecords(int $userId, int $year, int $month): Collection
    {
        return AttendanceRecord::byUser($userId)
            ->byMonth($year, $month)
            ->orderBy('attend_date')
            ->get();
    }

    /**
     * 오늘 자신의 출석 순위
     */
    public function getTodayRank(int $userId): ?int
    {
        $record = AttendanceRecord::byUser($userId)
            ->where('attend_date', Carbon::today()->toDateString())
            ->first();

        return $record?->daily_rank;
    }

    /**
     * 오늘 출석자 수
     */
    public function getTodayCount(): int
    {
        return AttendanceRecord::where('attend_date', Carbon::today()->toDateString())
            ->count();
    }

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayList(int $perPage): LengthAwarePaginator
    {
        return AttendanceRecord::where('attend_date', Carbon::today()->toDateString())
            ->with('user')
            ->orderBy('daily_rank')
            ->paginate($perPage);
    }

    /**
     * 유저 총 출석일 수
     */
    public function getUserTotalCount(int $userId): int
    {
        return AttendanceRecord::byUser($userId)->count();
    }
}
