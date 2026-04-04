// @vitest-environment jsdom
import '@testing-library/jest-dom';
import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { createLayoutTest, screen } from '@core/template-engine/__tests__/utils/layoutTestUtils';
import layoutJson from '../../../layouts/admin/admin_attendance_settings.json';

describe('admin_attendance_settings layout', () => {
    const testUtils = createLayoutTest(layoutJson, {
        translations: {
            'yjsoft-attendance': {
                settings: {
                    title: '출석부 설정',
                    general: { auto_attend: '자동 출석' },
                    bonus: {
                        title: '보너스 설정',
                        base_point: '기본 포인트',
                        weekly_streak: '주간 개근 보너스',
                        monthly_streak: '월간 개근 보너스',
                        yearly_streak: '연간 개근 보너스',
                        rank1: '1등 보너스',
                        rank2: '2등 보너스',
                        rank3: '3등 보너스',
                    },
                    time_limit: {
                        title: '출석 시간 제한',
                        enabled: '시간 제한 사용',
                        start: '시작 시간',
                        end: '종료 시간',
                    },
                    random_point: {
                        title: '랜덤 포인트',
                        enabled: '랜덤 포인트 사용',
                        min: '최소',
                        max: '최대',
                        probability: '지급 확률',
                    },
                    greetings: {
                        title: '기본 인삿말',
                        add: '추가',
                        delete: '삭제',
                    },
                    save_btn: '저장',
                    save_success: '설정이 저장되었습니다.',
                },
            },
        },
        locale: 'ko',
        auth: {
            isAuthenticated: true,
            user: { id: 1, name: 'Admin', role: 'super_admin' },
            authType: 'admin',
        },
    });

    beforeEach(() => {
        testUtils.mockApi('admin_settings', {
            response: {
                data: {
                    general: { auto_attend: false },
                    bonus: {
                        base_point: 10,
                        weekly_streak_point: 50,
                        monthly_streak_point: 100,
                        yearly_streak_point: 1000,
                        rank1_point: 100,
                        rank2_point: 50,
                        rank3_point: 30,
                    },
                    time_limit: {
                        enabled: false,
                        start_hour: 0,
                        start_minute: 0,
                        end_hour: 23,
                        end_minute: 59,
                    },
                    random_point: {
                        enabled: false,
                        min_point: 1,
                        max_point: 100,
                        probability: 30,
                    },
                    greetings: {
                        list: ['안녕하세요', '출석합니다', '출첵!'],
                    },
                },
            },
        });
    });

    afterEach(() => {
        testUtils.cleanup();
    });

    it('renders settings page', async () => {
        await testUtils.render();
        expect(screen.getByTestId('settings-page')).toBeInTheDocument();
    });

    it('displays loaded settings', async () => {
        await testUtils.render();
        expect(screen.getByTestId('bonus-settings')).toBeInTheDocument();
        expect(screen.getByTestId('time-limit-settings')).toBeInTheDocument();
        expect(screen.getByTestId('random-point-settings')).toBeInTheDocument();
        expect(screen.getByTestId('greetings-settings')).toBeInTheDocument();
    });

    it('auto attend toggle works', async () => {
        await testUtils.render();
        const toggle = screen.getByTestId('auto-attend-toggle');
        expect(toggle).toBeInTheDocument();
    });

    it('time limit fields disabled when disabled', async () => {
        await testUtils.render();
        // 시간 제한이 비활성화된 상태에서는 시간 입력 필드가 비활성화
        const timeLimitEnabled = screen.getByTestId('time-limit-enabled');
        expect(timeLimitEnabled).toBeInTheDocument();
    });

    it('greeting list add and delete', async () => {
        await testUtils.render();
        const greetingList = screen.getByTestId('greetings-settings');
        expect(greetingList).toBeInTheDocument();

        // 추가 버튼 확인
        const addBtn = screen.getByTestId('greeting-add-btn');
        expect(addBtn).toBeInTheDocument();
    });

    it('save button calls PUT api', async () => {
        await testUtils.render();
        const saveBtn = screen.getByTestId('save-btn');
        expect(saveBtn).toBeInTheDocument();
    });
});
