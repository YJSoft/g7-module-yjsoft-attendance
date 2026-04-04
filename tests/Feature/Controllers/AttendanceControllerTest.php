<?php

namespace Modules\Yjsoft\Attendance\Tests\Feature\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Yjsoft\Attendance\Models\AttendanceRecord;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class AttendanceControllerTest extends ModuleTestCase
{
    private string $baseUrl = '/api/modules/yjsoft-attendance/auth';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-04-03 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 미인증 접근 시 401
     */
    public function test_attend_requires_auth(): void
    {
        $response = $this->postJson("{$this->baseUrl}/attend", [
            'greeting' => '안녕하세요',
        ]);

        $response->assertStatus(401);
    }

    /**
     * 정상 출석 시 200 반환
     */
    public function test_attend_success_returns_200(): void
    {
        $user = $this->createUserWithPermission();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("{$this->baseUrl}/attend", [
                'greeting' => '안녕하세요',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * 오늘 이미 출석 시 409 반환
     */
    public function test_attend_duplicate_returns_409(): void
    {
        $user = $this->createUserWithPermission();

        // 먼저 1회 출석
        AttendanceRecord::create([
            'user_id'     => $user->id,
            'attend_date' => Carbon::today()->toDateString(),
            'attend_time' => '09:00:00',
            'greeting'    => '안녕하세요',
            'base_point'  => 10,
            'daily_rank'  => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("{$this->baseUrl}/attend", [
                'greeting' => '출석합니다',
            ]);

        $response->assertStatus(409);
    }

    /**
     * yjsoft-attendance.attendance.attend permission 없는 사용자 → permission 미들웨어가 403 반환
     */
    public function test_attend_not_allowed_returns_403(): void
    {
        $user = User::factory()->create();
        // permission 부여 없이 접근

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("{$this->baseUrl}/attend", [
                'greeting' => '안녕하세요',
            ]);

        $response->assertStatus(403);
    }

    /**
     * 시간 외 출석 시도 403 반환
     */
    public function test_attend_time_not_allowed_returns_403(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-03 23:30:00'));

        $user = $this->createUserWithPermission();

        // 시간 제한 설정: 9:00 ~ 18:00
        $this->setTimeLimitSettings(true, 9, 0, 18, 0);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("{$this->baseUrl}/attend", [
                'greeting' => '안녕하세요',
            ]);

        $response->assertStatus(403);
    }

    /**
     * status API가 이번 달 달력 데이터 반환
     */
    public function test_status_returns_monthly_calendar(): void
    {
        $user = $this->createUserWithPermission();

        // 출석 데이터 생성
        AttendanceRecord::create([
            'user_id'     => $user->id,
            'attend_date' => '2026-04-01',
            'attend_time' => '10:00:00',
            'greeting'    => '안녕하세요',
            'base_point'  => 10,
            'daily_rank'  => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("{$this->baseUrl}/status");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'is_attended_today',
                'total_count',
                'monthly_records',
                'streaks',
                'today_rank',
            ],
        ]);
    }

    /**
     * 목록 API가 페이지네이션 결과 반환
     */
    public function test_list_returns_paginated_result(): void
    {
        $user = $this->createUserWithPermission();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("{$this->baseUrl}/list");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'data',
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ],
        ]);
    }

    /**
     * 랜덤 인삿말 API가 문자열 반환
     */
    public function test_random_greeting_returns_string(): void
    {
        $user = $this->createUserWithPermission();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("{$this->baseUrl}/random-greeting");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => ['greeting'],
        ]);
    }

    /**
     * 헬퍼: permission을 가진 사용자 생성
     */
    private function createUserWithPermission(): User
    {
        $user = User::factory()->create();

        // permission 미들웨어가 존재하는 경우 해당 권한 부여
        if (method_exists($user, 'givePermissionTo')) {
            $user->givePermissionTo('yjsoft-attendance.attendance.attend');
        } elseif (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => 'yjsoft-attendance.attendance.attend'],
                ['guard_name' => 'sanctum']
            );
            $user->givePermissionTo($permission);
        } else {
            // G7 코어의 권한 시스템에 맞게 설정
            // 권한 시스템이 없는 경우 role 기반으로 처리
            if (DB::getSchemaBuilder()->hasTable('roles')) {
                $role = DB::table('roles')->where('name', 'user')->first();
                if ($role) {
                    DB::table('role_user')->insert([
                        'role_id' => $role->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        return $user;
    }

    /**
     * 헬퍼: 시간 제한 설정 저장
     */
    private function setTimeLimitSettings(
        bool $enabled,
        int $startHour,
        int $startMinute,
        int $endHour,
        int $endMinute
    ): void {
        $settingsService = app(\Modules\Yjsoft\Attendance\Services\AttendanceSettingsService::class);
        $settingsService->saveSettings([
            'time_limit' => [
                'enabled'      => $enabled,
                'start_hour'   => $startHour,
                'start_minute' => $startMinute,
                'end_hour'     => $endHour,
                'end_minute'   => $endMinute,
            ],
        ]);
    }
}
