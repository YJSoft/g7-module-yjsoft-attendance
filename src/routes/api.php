<?php

use Illuminate\Support\Facades\Route;

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

// 관리자 라우트
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // 설정 API (Stage 3에서 컨트롤러 구현)
});

// 인증 사용자 라우트
Route::prefix('auth')->middleware(['auth:sanctum'])->group(function () {
    // 출석 API (Stage 3에서 컨트롤러 구현)
});
