<?php

namespace Modules\Yjsoft\Attendance\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 출석부 모듈 테스트 베이스 클래스
 *
 * ModuleTestCase가 마이그레이션, 오토로드, ServiceProvider 등록을 자동 처리한다.
 * 모든 테스트 클래스는 이 클래스를 상속해야 한다 (Tests\TestCase 직접 상속 금지).
 */
abstract class ModuleTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * 모듈 서비스 프로바이더 등록 및 마이그레이션 경로 설정
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 모듈 ServiceProvider 등록
        $this->app->register(\Modules\Yjsoft\Attendance\Providers\AttendanceServiceProvider::class);

        // 모듈 마이그레이션 경로 등록
        $this->loadMigrationsFrom($this->getModuleMigrationPath());

        // 모듈 번역 파일 등록
        $this->app['translator']->addNamespace(
            'yjsoft-attendance',
            $this->getModulePath() . '/src/lang'
        );
    }

    /**
     * 모듈 루트 경로
     */
    protected function getModulePath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * 모듈 마이그레이션 경로
     */
    protected function getModuleMigrationPath(): string
    {
        return $this->getModulePath() . '/database/migrations';
    }
}
