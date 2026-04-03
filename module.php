<?php

namespace Modules\Yjsoft\Attendance;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    // getName(), getVersion(), getDescription()은 module.json에서 자동 파싱
    // 별도 오버라이드 불필요

    /**
     * 권한 목록
     *
     * - attend: 사용자 화면용 (type: user)
     * - admin.*: 관리자 화면용 (type: admin)
     */
    public function getPermissions(): array
    {
        return [
            [
                'identifier'  => 'yjsoft-attendance.attend',
                'name'        => ['ko' => '출석하기', 'en' => 'Check Attendance'],
                'description' => ['ko' => '출석 기능을 사용할 수 있는 권한', 'en' => 'Permission to use attendance feature'],
                'type'        => 'user',
                'roles'       => ['user'],
            ],
            [
                'identifier'  => 'yjsoft-attendance.admin.settings',
                'name'        => ['ko' => '출석부 설정 관리', 'en' => 'Manage Attendance Settings'],
                'description' => ['ko' => '출석부 설정을 변경할 수 있는 권한', 'en' => 'Permission to manage attendance settings'],
                'type'        => 'admin',
                'roles'       => ['admin'],
            ],
            [
                'identifier'  => 'yjsoft-attendance.admin.view',
                'name'        => ['ko' => '출석 통계 조회', 'en' => 'View Attendance Stats'],
                'description' => ['ko' => '출석 통계를 조회할 수 있는 권한', 'en' => 'Permission to view attendance statistics'],
                'type'        => 'admin',
                'roles'       => ['admin'],
            ],
        ];
    }

    /**
     * 관리자 메뉴 정의
     */
    public function getAdminMenus(): array
    {
        return [
            [
                'name' => [
                    'ko' => '출석부 관리',
                    'en' => 'Attendance Management',
                ],
                'slug'  => 'yjsoft-attendance',
                'url'   => '/admin/yjsoft-attendance',
                'icon'  => 'fa-calendar-check',
                'order' => 100,
                'children' => [
                    [
                        'name' => [
                            'ko' => '설정',
                            'en' => 'Settings',
                        ],
                        'slug'       => 'yjsoft-attendance-settings',
                        'url'        => '/admin/yjsoft-attendance/settings',
                        'icon'       => 'fa-cog',
                        'order'      => 1,
                        'permission' => 'yjsoft-attendance.admin.settings',
                    ],
                    [
                        'name' => [
                            'ko' => '스킨 관리',
                            'en' => 'Skin Management',
                        ],
                        'slug'       => 'yjsoft-attendance-skin',
                        'url'        => '/admin/yjsoft-attendance/skin',
                        'icon'       => 'fa-palette',
                        'order'      => 2,
                        'permission' => 'yjsoft-attendance.admin.settings',
                    ],
                ],
            ],
        ];
    }

    /**
     * 훅 리스너 목록
     */
    public function getHookListeners(): array
    {
        return [
            \Modules\Yjsoft\Attendance\Listeners\AutoAttendanceListener::class,
        ];
    }
}
