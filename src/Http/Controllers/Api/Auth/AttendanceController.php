<?php

namespace Modules\Yjsoft\Attendance\Http\Controllers\Api\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\AuthBaseController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Yjsoft\Attendance\Exceptions\AlreadyAttendedException;
use Modules\Yjsoft\Attendance\Exceptions\AttendanceTimeNotAllowedException;
use Modules\Yjsoft\Attendance\Http\Requests\StoreAttendanceRequest;
use Modules\Yjsoft\Attendance\Http\Resources\AttendanceListResource;
use Modules\Yjsoft\Attendance\Http\Resources\AttendanceResource;
use Modules\Yjsoft\Attendance\Services\AttendanceService;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Contracts\AttendanceStreakRepositoryInterface;

class AttendanceController extends AuthBaseController
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceSettingsService $settingsService,
        private AttendanceRepositoryInterface $attendanceRepository,
        private AttendanceStreakRepositoryInterface $streakRepository
    ) {
        parent::__construct();
    }

    /**
     * 출석 처리
     */
    public function attend(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $greeting = $request->validated()['greeting'] ?? '';

            $record = $this->attendanceService->attend($userId, $greeting);

            return ResponseHelper::success(
                'yjsoft-attendance::messages.attend_success',
                new AttendanceResource($record)
            );
        } catch (AlreadyAttendedException $e) {
            return ResponseHelper::error(
                'yjsoft-attendance::messages.already_attended',
                409
            );
        } catch (AttendanceTimeNotAllowedException $e) {
            return ResponseHelper::forbidden(
                'yjsoft-attendance::messages.time_not_allowed'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'yjsoft-attendance::messages.attend_failed',
                500
            );
        }
    }

    /**
     * 오늘의 출석 상태 + 이번 달 달력 데이터
     */
    public function status(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $today = Carbon::today();

        $todayRecord = $this->attendanceRepository->findTodayByUser($userId);
        $monthlyRecords = $this->attendanceRepository->getMonthlyRecords(
            $userId,
            $today->year,
            $today->month
        );
        $totalCount = $this->attendanceRepository->getUserTotalCount($userId);
        $todayRank = $this->attendanceRepository->getTodayRank($userId);
        $streaks = $this->streakRepository->getUserStreaks($userId);

        $frontendSettings = $this->settingsService->getFrontendSettings();

        return ResponseHelper::success('messages.success', [
            'is_attended_today' => $todayRecord !== null,
            'today_record'      => $todayRecord ? new AttendanceResource($todayRecord) : null,
            'monthly_records'   => $monthlyRecords->map(fn ($r) => [
                'date'      => $r->attend_date->toDateString(),
                'attended'  => true,
            ]),
            'total_count'       => $totalCount,
            'streaks'           => $streaks->map(fn ($s) => [
                'type'           => $s->streak_type->value,
                'label'          => $s->streak_type->label(),
                'current_streak' => $s->current_streak,
                'is_completed'   => $s->is_completed,
                'period_start'   => $s->period_start->toDateString(),
                'period_end'     => $s->period_end->toDateString(),
            ]),
            'today_rank'        => $todayRank,
            'settings'          => $frontendSettings,
        ]);
    }

    /**
     * 오늘의 출석자 목록 (페이지네이션)
     */
    public function list(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);

        $paginator = $this->attendanceRepository->getTodayList($perPage);

        return ResponseHelper::success('messages.success', [
            'data' => AttendanceListResource::collection($paginator->items()),
            'pagination' => [
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * 기본 인삿말 목록에서 랜덤 1개 반환
     */
    public function randomGreeting(): JsonResponse
    {
        $greetings = $this->settingsService->getSetting('greetings.list', []);

        $greeting = ! empty($greetings)
            ? $greetings[array_rand($greetings)]
            : '';

        return ResponseHelper::success('messages.success', [
            'greeting' => $greeting,
        ]);
    }

    /**
     * 프론트엔드 공개 설정 반환
     */
    public function publicSettings(): JsonResponse
    {
        $settings = $this->settingsService->getFrontendSettings();

        return ResponseHelper::success('messages.success', $settings);
    }
}
