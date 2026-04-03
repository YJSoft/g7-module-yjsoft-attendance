<?php

namespace Modules\Yjsoft\Attendance\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\AdminBaseController;
use Illuminate\Http\JsonResponse;
use Modules\Yjsoft\Attendance\Http\Requests\UpdateAttendanceSettingsRequest;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;

class AttendanceSettingsController extends AdminBaseController
{
    public function __construct(
        private AttendanceSettingsService $settingsService
    ) {
        parent::__construct();
    }

    /**
     * 전체 설정 반환
     */
    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getAllSettings();

        return ResponseHelper::success(
            'yjsoft-attendance::messages.settings_fetch_success',
            $settings
        );
    }

    /**
     * 설정 저장
     */
    public function update(UpdateAttendanceSettingsRequest $request): JsonResponse
    {
        $result = $this->settingsService->saveSettings($request->validated());

        if ($result) {
            $updatedSettings = $this->settingsService->getAllSettings();

            return ResponseHelper::success(
                'yjsoft-attendance::messages.settings_saved',
                $updatedSettings
            );
        }

        return ResponseHelper::error('messages.failed', 500);
    }
}
