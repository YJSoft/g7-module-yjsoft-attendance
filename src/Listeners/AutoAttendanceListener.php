<?php

namespace Modules\Yjsoft\Attendance\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Yjsoft\Attendance\Services\AttendanceService;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;

/**
 * 자동출석 리스너
 *
 * 코어 로그인 이벤트를 수신하여 설정에 따라 자동 출석 처리.
 * module.php의 getHookListeners()에 등록되어 있음.
 */
class AutoAttendanceListener
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 로그인 이벤트 처리
     *
     * auto_attend 설정이 true인 경우에만 출석 처리.
     * 이미 출석했거나 오류 발생 시 조용히 실패 (로그만 기록).
     */
    public function handle(object $event): void
    {
        $autoAttend = (bool) $this->settingsService->getSetting('general.auto_attend', false);

        if (! $autoAttend) {
            return;
        }

        $userId = $event->user->id ?? null;
        if (! $userId) {
            return;
        }

        try {
            $this->attendanceService->attend($userId, '');
        } catch (\Exception $e) {
            Log::debug('Auto attendance skipped or failed', [
                'user_id' => $userId,
                'reason'  => $e->getMessage(),
            ]);
        }
    }
}
