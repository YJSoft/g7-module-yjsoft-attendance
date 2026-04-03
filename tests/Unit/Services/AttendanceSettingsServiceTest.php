<?php

namespace Modules\Yjsoft\Attendance\Tests\Unit\Services;

use Illuminate\Support\Facades\File;
use Modules\Yjsoft\Attendance\Services\AttendanceSettingsService;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class AttendanceSettingsServiceTest extends ModuleTestCase
{
    private AttendanceSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AttendanceSettingsService();
    }

    protected function tearDown(): void
    {
        // 테스트 후 저장된 설정 파일 삭제
        $storagePath = storage_path('app/modules/yjsoft-attendance/settings');
        if (File::isDirectory($storagePath)) {
            File::deleteDirectory($storagePath);
        }

        parent::tearDown();
    }

    /**
     * defaults.json 경로가 올바르게 반환되는지 확인
     */
    public function test_get_settings_defaults_path(): void
    {
        $path = $this->service->getSettingsDefaultsPath();

        $this->assertNotNull($path);
        $this->assertFileExists($path);
        $this->assertStringEndsWith('config/settings/defaults.json', $path);
    }

    /**
     * 기본 설정값이 올바르게 로드되는지 확인
     */
    public function test_get_all_settings_returns_defaults(): void
    {
        $settings = $this->service->getAllSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('general', $settings);
        $this->assertArrayHasKey('bonus', $settings);
        $this->assertArrayHasKey('time_limit', $settings);
        $this->assertArrayHasKey('random_point', $settings);
        $this->assertArrayHasKey('greetings', $settings);
    }

    /**
     * 단일 설정값 조회 (기본값)
     */
    public function test_get_setting_returns_default(): void
    {
        $basePoint = $this->service->getSetting('bonus.base_point', 0);

        $this->assertEquals(10, $basePoint);
    }

    /**
     * 설정값 저장 및 재조회
     */
    public function test_save_and_get_settings(): void
    {
        $settings = [
            'bonus' => [
                'base_point'          => 20,
                'weekly_streak_point' => 100,
                'monthly_streak_point' => 200,
                'yearly_streak_point' => 2000,
                'rank1_point'         => 200,
                'rank2_point'         => 100,
                'rank3_point'         => 50,
            ],
        ];

        $result = $this->service->saveSettings($settings);

        $this->assertTrue($result);

        // 새 서비스 인스턴스로 재조회 (캐시 우회)
        $freshService = new AttendanceSettingsService();
        $savedBasePoint = $freshService->getSetting('bonus.base_point', 0);

        $this->assertEquals(20, $savedBasePoint);
    }

    /**
     * 카테고리별 설정 조회
     */
    public function test_get_settings_by_category(): void
    {
        $bonusSettings = $this->service->getSettings('bonus');

        $this->assertIsArray($bonusSettings);
        $this->assertArrayHasKey('base_point', $bonusSettings);
        $this->assertArrayHasKey('rank1_point', $bonusSettings);
    }

    /**
     * 프론트엔드 공개 설정이 올바르게 반환되는지 확인
     */
    public function test_get_frontend_settings(): void
    {
        $frontendSettings = $this->service->getFrontendSettings();

        $this->assertIsArray($frontendSettings);
        // frontend_schema에서 expose: true인 카테고리가 반환됨
        $this->assertArrayHasKey('general', $frontendSettings);
        $this->assertArrayHasKey('bonus', $frontendSettings);
    }

    /**
     * 인스턴스 캐싱이 동작하는지 확인
     */
    public function test_settings_are_cached_in_instance(): void
    {
        $settings1 = $this->service->getAllSettings();
        $settings2 = $this->service->getAllSettings();

        // 같은 인스턴스에서 두 번 호출 시 동일한 결과
        $this->assertEquals($settings1, $settings2);
    }

    /**
     * setSetting으로 단일 값 변경
     */
    public function test_set_setting_saves_value(): void
    {
        $result = $this->service->setSetting('bonus.base_point', 30);

        $this->assertTrue($result);

        // 새 인스턴스로 확인
        $freshService = new AttendanceSettingsService();
        $this->assertEquals(30, $freshService->getSetting('bonus.base_point', 0));
    }

    /**
     * _meta로 시작하는 카테고리는 저장하지 않음
     */
    public function test_save_settings_ignores_meta(): void
    {
        $settings = [
            '_meta'   => ['should' => 'be ignored'],
            'general' => ['auto_attend' => true],
        ];

        $result = $this->service->saveSettings($settings);

        $this->assertTrue($result);

        $freshService = new AttendanceSettingsService();
        $this->assertTrue((bool) $freshService->getSetting('general.auto_attend', false));
    }
}
