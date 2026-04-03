<?php

namespace Modules\Yjsoft\Attendance\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\AdminBaseController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\Yjsoft\Attendance\Services\AttendanceService;

class AttendanceStatsController extends AdminBaseController
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {
        parent::__construct();
    }

    /**
     * 출석 통계 조회
     */
    public function index(): JsonResponse
    {
        $today = Carbon::today();

        $todayCount = $this->attendanceService->getTodayCount();

        return ResponseHelper::success('messages.success', [
            'today_count' => $todayCount,
            'date'        => $today->toDateString(),
        ]);
    }
}
