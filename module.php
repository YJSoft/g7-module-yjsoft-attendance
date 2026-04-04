<?php

namespace Modules\Yjsoft\Attendance;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    // getName(), getVersion(), getDescription()은 module.json에서 자동 파싱
    // 별도 오버라이드 불필요

    /**
     * 모듈 권한 목록 반환 (계층형 구조, 다국어 지원)
     *
     * 구조: 모듈(1레벨) → 카테고리(2레벨) → 개별 권한(3레벨)
     * identifier는 자동 생성됨: {module}.{category}.{action}
     *
     * - yjsoft-attendance.attendance.attend: 사용자 출석 권한 (type: user)
     * - yjsoft-attendance.settings.read: 설정 조회 (type: admin)
     * - yjsoft-attendance.settings.update: 설정 수정 (type: admin)
     * - yjsoft-attendance.stats.read: 통계 조회 (type: admin)
     *
     * @return array<string, mixed>
     */
    public function getPermissions(): array
    {
        return [
            'name' => [
                'ko' => '출석부',
                'en' => 'Attendance',
            ],
            'description' => [
                'ko' => '출석부 모듈 권한',
                'en' => 'Attendance module permissions',
            ],
            'categories' => [
                // 출석 권한 (type: user)
                [
                    'identifier' => 'attendance',
                    'name' => [
                        'ko' => '출석',
                        'en' => 'Attendance',
                    ],
                    'description' => [
                        'ko' => '출석 기능 권한',
                        'en' => 'Attendance feature permissions',
                    ],
                    'permissions' => [
                        [
                            'action' => 'attend',
                            'name' => [
                                'ko' => '출석하기',
                                'en' => 'Check Attendance',
                            ],
                            'description' => [
                                'ko' => '출석 기능을 사용할 수 있는 권한',
                                'en' => 'Permission to use attendance feature',
                            ],
                            'type' => 'user',
                            'roles' => ['user'],
                        ],
                    ],
                ],
                // 설정 관리 권한 (type: admin)
                [
                    'identifier' => 'settings',
                    'name' => [
                        'ko' => '출석부 설정',
                        'en' => 'Attendance Settings',
                    ],
                    'description' => [
                        'ko' => '출석부 설정 관리 권한',
                        'en' => 'Attendance settings management permissions',
                    ],
                    'permissions' => [
                        [
                            'action' => 'read',
                            'name' => [
                                'ko' => '설정 조회',
                                'en' => 'View Settings',
                            ],
                            'description' => [
                                'ko' => '출석부 설정을 조회할 수 있는 권한',
                                'en' => 'Permission to view attendance settings',
                            ],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                        [
                            'action' => 'update',
                            'name' => [
                                'ko' => '설정 수정',
                                'en' => 'Update Settings',
                            ],
                            'description' => [
                                'ko' => '출석부 설정을 변경할 수 있는 권한',
                                'en' => 'Permission to manage attendance settings',
                            ],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                    ],
                ],
                // 통계 조회 권한 (type: admin)
                [
                    'identifier' => 'stats',
                    'name' => [
                        'ko' => '출석 통계',
                        'en' => 'Attendance Statistics',
                    ],
                    'description' => [
                        'ko' => '출석 통계 조회 권한',
                        'en' => 'Attendance statistics permissions',
                    ],
                    'permissions' => [
                        [
                            'action' => 'read',
                            'name' => [
                                'ko' => '통계 조회',
                                'en' => 'View Statistics',
                            ],
                            'description' => [
                                'ko' => '출석 통계를 조회할 수 있는 권한',
                                'en' => 'Permission to view attendance statistics',
                            ],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                    ],
                ],
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
                'url'   => '/admin/attendance',
                'icon'  => 'fa-calendar-check',
                'order' => 100,
                'children' => [
                    [
                        'name' => [
                            'ko' => '설정',
                            'en' => 'Settings',
                        ],
                        'slug'       => 'yjsoft-attendance-settings',
                        'url'        => '/admin/attendance/settings',
                        'icon'       => 'fa-cog',
                        'order'      => 1,
                        'permission' => 'yjsoft-attendance.settings.read',
                    ],
                    [
                        'name' => [
                            'ko' => '스킨 관리',
                            'en' => 'Skin Management',
                        ],
                        'slug'       => 'yjsoft-attendance-skin',
                        'url'        => '/admin/attendance/skin',
                        'icon'       => 'fa-palette',
                        'order'      => 2,
                        'permission' => 'yjsoft-attendance.settings.read',
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
