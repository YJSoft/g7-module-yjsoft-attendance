<?php

use Illuminate\Support\Facades\Route;
use Modules\Yjsoft\Attendance\Http\Controllers\Api\Auth\AttendanceController;
use Modules\Yjsoft\Attendance\Http\Controllers\Api\Admin\AttendanceSettingsController;
use Modules\Yjsoft\Attendance\Http\Controllers\Api\Admin\AttendanceStatsController;

/*
|--------------------------------------------------------------------------
| YJSoft Attendance Module API Routes
|--------------------------------------------------------------------------
|
| 주의: ModuleRouteServiceProvider가 자동으로 prefix를 적용합니다.
| - URL prefix: 'api/modules/yjsoft-attendance'
| - Name prefix: 'api.modules.yjsoft-attendance.'
|
*/

// 인증 사용자 전용
Route::prefix('auth')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/attend', [AttendanceController::class, 'attend'])
            ->middleware('permission:yjsoft-attendance.attendance.attend')
            ->name('auth.attend');

        Route::get('/status', [AttendanceController::class, 'status'])
            ->name('auth.status');

        Route::get('/list', [AttendanceController::class, 'list'])
            ->name('auth.list');

        Route::get('/random-greeting', [AttendanceController::class, 'randomGreeting'])
            ->name('auth.random-greeting');

        Route::get('/settings', [AttendanceController::class, 'publicSettings'])
            ->name('auth.settings');
    });

// 관리자 전용
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('/settings', [AttendanceSettingsController::class, 'index'])
            ->middleware('permission:yjsoft-attendance.settings.read')
            ->name('admin.settings.index');

        Route::put('/settings', [AttendanceSettingsController::class, 'update'])
            ->middleware('permission:yjsoft-attendance.settings.update')
            ->name('admin.settings.update');

        Route::get('/stats', [AttendanceStatsController::class, 'index'])
            ->middleware('permission:yjsoft-attendance.stats.read')
            ->name('admin.stats.index');
    });
