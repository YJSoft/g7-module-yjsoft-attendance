<?php

namespace Modules\Yjsoft\Attendance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Yjsoft\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Yjsoft\Attendance\Contracts\AttendanceStreakRepositoryInterface;
use Modules\Yjsoft\Attendance\Repositories\AttendanceRepository;
use Modules\Yjsoft\Attendance\Repositories\AttendanceStreakRepository;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->registerRepositoryBindings();
    }

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Repository 인터페이스 — 구현체 바인딩
     */
    private function registerRepositoryBindings(): void
    {
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(AttendanceStreakRepositoryInterface::class, AttendanceStreakRepository::class);
    }
}
