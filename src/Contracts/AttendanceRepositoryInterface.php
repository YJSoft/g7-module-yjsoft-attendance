<?php

namespace Modules\Yjsoft\Attendance\Contracts;

use Modules\Yjsoft\Attendance\Models\AttendanceRecord;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttendanceRepositoryInterface
{
    /**
     * 오늘 출석 기록 조회
     */
    public function findTodayByUser(int $userId): ?AttendanceRecord;

    /**
     * 출석 기록 생성
     */
    public function createRecord(array $data): AttendanceRecord;

    /**
     * 월별 출석 목록
     */
    public function getMonthlyRecords(int $userId, int $year, int $month): Collection;

    /**
     * 오늘 자신의 출석 순위
     */
    public function getTodayRank(int $userId): ?int;

    /**
     * 오늘 출석자 수
     */
    public function getTodayCount(): int;

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayList(int $perPage): LengthAwarePaginator;

    /**
     * 유저 총 출석일 수
     */
    public function getUserTotalCount(int $userId): int;
}
