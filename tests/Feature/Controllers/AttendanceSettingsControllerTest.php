<?php

namespace Modules\Yjsoft\Attendance\Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class AttendanceSettingsControllerTest extends ModuleTestCase
{
    private string $baseUrl = '/api/modules/yjsoft-attendance/admin/settings';

    /**
     * 비관리자 접근 시 403
     */
    public function test_settings_requires_admin(): void
    {
        $user = User::factory()->create();
        // 일반 사용자로 관리자 API 접근

        $response = $this->actingAs($user, 'sanctum')
            ->getJson($this->baseUrl);

        $response->assertStatus(403);
    }

    /**
     * 전체 설정 반환
     */
    public function test_settings_index_returns_all_settings(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson($this->baseUrl);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'general',
                'bonus',
                'time_limit',
                'random_point',
                'greetings',
            ],
        ]);
    }

    /**
     * 설정 저장 후 200 반환
     */
    public function test_settings_update_saves_successfully(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson($this->baseUrl, [
                'bonus' => [
                    'base_point'           => 20,
                    'weekly_streak_point'  => 100,
                    'monthly_streak_point' => 200,
                    'yearly_streak_point'  => 2000,
                    'rank1_point'          => 200,
                    'rank2_point'          => 100,
                    'rank3_point'          => 50,
                ],
                'general' => [
                    'auto_attend' => true,
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * 보너스 포인트 음수 불가
     */
    public function test_settings_update_validates_bonus_points(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson($this->baseUrl, [
                'bonus' => [
                    'base_point' => -10,
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * 시간 범위 유효성 (0~23시, 0~59분)
     */
    public function test_settings_update_validates_time_range(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson($this->baseUrl, [
                'time_limit' => [
                    'start_hour'   => 25,  // 0~23 범위 초과
                    'start_minute' => 60,  // 0~59 범위 초과
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * 확률 1~100 범위
     */
    public function test_settings_update_validates_probability(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson($this->baseUrl, [
                'random_point' => [
                    'probability' => 150,  // 1~100 범위 초과
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * 헬퍼: 관리자 사용자 생성
     */
    private function createAdmin(): User
    {
        $admin = User::factory()->create();

        // G7 코어의 관리자 권한 부여
        if (method_exists($admin, 'assignRole')) {
            // Spatie Permission 방식
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => 'admin'],
                    ['guard_name' => 'sanctum']
                );
                $admin->assignRole($role);
            }
        }

        // G7 코어의 admin 체크 방식에 맞게 처리
        if (DB::getSchemaBuilder()->hasTable('roles')) {
            $role = DB::table('roles')->where('name', 'admin')->first();
            if ($role) {
                DB::table('role_user')->insertOrIgnore([
                    'role_id' => $role->id,
                    'user_id' => $admin->id,
                ]);
            }
        }

        // admin 속성이 있는 경우
        if (DB::getSchemaBuilder()->hasColumn('users', 'is_admin')) {
            $admin->update(['is_admin' => true]);
        }

        // yjsoft-attendance.admin.settings permission 부여
        if (method_exists($admin, 'givePermissionTo')) {
            try {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => 'yjsoft-attendance.admin.settings'],
                    ['guard_name' => 'sanctum']
                );
                $admin->givePermissionTo($permission);
            } catch (\Exception $e) {
                // 권한 시스템 미설정 시 무시
            }
        }

        return $admin;
    }
}
