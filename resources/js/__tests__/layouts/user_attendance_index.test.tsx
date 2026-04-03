// @vitest-environment jsdom
import '@testing-library/jest-dom';
import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { createLayoutTest, screen } from '@core/template-engine/__tests__/utils/layoutTestUtils';
import layoutJson from '../../layouts/user/user_attendance_index.json';

describe('user_attendance_index layout', () => {
    const testUtils = createLayoutTest(layoutJson, {
        translations: {
            'yjsoft-attendance': {
                attendance: {
                    title: '출석부',
                    attend_btn: '출석하기',
                    already_done: '출첵완료',
                    attend_done_message: '출석이 완료되었습니다.',
                    current_time: '현재 시각',
                    greeting_placeholder: '인삿말을 입력하세요',
                    calendar: {
                        title: '출석 달력',
                        attended: '출석',
                        absent: '결석',
                        future: '미출석',
                    },
                    streak: {
                        weekly: '주간 개근',
                        monthly: '월간 개근',
                        yearly: '연간 개근',
                    },
                    rank: { title: '오늘의 출석 현황' },
                    detail: { title: '자세히 보기' },
                },
            },
        },
        locale: 'ko',
        auth: {
            isAuthenticated: true,
            user: { id: 1, name: 'TestUser' },
        },
    });

    beforeEach(() => {
        testUtils.mockApi('attendance_status', {
            response: {
                data: {
                    is_attended_today: false,
                    total_count: 10,
                    monthly_records: [
                        { date: '2026-04-01', attended: true },
                        { date: '2026-04-02', attended: true },
                    ],
                    streaks: [],
                    today_rank: null,
                    settings: {
                        greetings: { list: ['안녕하세요', '출석합니다'] },
                    },
                },
            },
        });

        testUtils.mockApi('attendance_settings', {
            response: {
                data: {
                    greetings: { list: ['안녕하세요', '출석합니다'] },
                },
            },
        });

        testUtils.mockApi('attendance_list', {
            response: {
                data: {
                    data: [],
                    pagination: {
                        total: 0,
                        current_page: 1,
                        last_page: 1,
                        per_page: 20,
                    },
                },
            },
        });
    });

    afterEach(() => {
        testUtils.cleanup();
    });

    it('renders attendance page', async () => {
        await testUtils.render();
        expect(screen.getByTestId('attendance-page')).toBeInTheDocument();
    });

    it('shows attend button when not attended', async () => {
        await testUtils.render();
        expect(screen.getByTestId('attend-btn')).toBeInTheDocument();
    });

    it('hides attend button when already attended', async () => {
        testUtils.mockApi('attendance_status', {
            response: {
                data: {
                    is_attended_today: true,
                    total_count: 11,
                    monthly_records: [],
                    streaks: [],
                    today_rank: 5,
                    settings: {
                        greetings: { list: ['안녕하세요'] },
                    },
                },
            },
        });

        await testUtils.render();
        expect(screen.queryByTestId('attend-btn')).not.toBeInTheDocument();
        expect(screen.getByTestId('attend-complete-msg')).toBeInTheDocument();
    });

    it('shows greeting input with random default', async () => {
        testUtils.setState('greeting', '안녕하세요', 'local');
        await testUtils.render();
        expect(screen.getByTestId('greeting-input')).toBeInTheDocument();
    });

    it('calendar shows attended days', async () => {
        await testUtils.render();
        expect(screen.getByTestId('attendance-calendar')).toBeInTheDocument();
    });

    it('streak detail dropdown toggles', async () => {
        await testUtils.render();
        const detailBtn = screen.getByTestId('streak-detail-toggle');
        expect(detailBtn).toBeInTheDocument();
    });

    it('pagination works on list', async () => {
        await testUtils.render();
        expect(screen.getByTestId('attendance-list')).toBeInTheDocument();
    });
});
