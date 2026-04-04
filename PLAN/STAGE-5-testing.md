# 5단계: 테스트 작성

> **참고 문서**
> - [AGENTS.md — 테스트 프로토콜](https://github.com/gnuboard/g7/blob/main/AGENTS.md#테스트-프로토콜)
> - [AGENTS.md — 그누보드7 레이아웃 렌더링 테스트](https://github.com/gnuboard/g7/blob/main/AGENTS.md#그누보드7-레이아웃-렌더링-테스트)

---

## 5.1 테스트 원칙

> **규칙**:
> - 기능 구현 = 테스트 코드 작성 필수
> - 테스트 통과 = 작업 완료 (작성만으로 불충분)
> - 기존 테스트 있으면 변경사항 반영하여 수정 후 실행
> - 기능 구현 시 관련된 모든 계층(백엔드 + 프론트엔드 + 레이아웃 렌더링) 테스트 필수
> - **모듈 프론트엔드 테스트는 독립 `vitest.config.ts` 사용** (루트 config 포함 금지)
> - 레이아웃 테스트는 해당 레이아웃이 속한 확장 디렉토리에 작성

---

## 5.2 테스트 파일 위치

| 테스트 유형 | 파일 위치 |
|------------|---------|
| 모델 테스트 | `tests/Unit/Models/AttendanceRecordTest.php` |
| Enum 테스트 | `tests/Unit/Enums/StreakTypeTest.php` |
| Service 테스트 | `tests/Unit/Services/AttendanceServiceTest.php` |
| Service 테스트 | `tests/Unit/Services/AttendanceStreakServiceTest.php` |
| Service 테스트 | `tests/Unit/Services/AttendanceRankServiceTest.php` |
| Service 테스트 | `tests/Unit/Services/AttendanceSettingsServiceTest.php` |
| Controller 테스트 | `tests/Feature/Controllers/AttendanceControllerTest.php` |
| Controller 테스트 | `tests/Feature/Controllers/AttendanceSettingsControllerTest.php` |
| 레이아웃 테스트 | `resources/js/__tests__/layouts/user_attendance_index.test.tsx` |
| 레이아웃 테스트 | `resources/js/__tests__/layouts/admin_attendance_settings.test.tsx` |

---

## 5.3 백엔드 Unit 테스트

### StreakTypeTest

```
경로: tests/Unit/Enums/StreakTypeTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_weekly_period_calculation` | 주어진 날짜의 주간 기간(월~일) 올바른 계산 |
| `test_monthly_period_calculation` | 월 1일~말일 올바른 계산 |
| `test_yearly_period_calculation` | 연도 1/1~12/31 올바른 계산 |
| `test_cross_month_not_streak` | 월을 걸친 연속 출석은 해당 월 개근이 아님을 검증 |

### AttendanceServiceTest

```
경로: tests/Unit/Services/AttendanceServiceTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_attend_success` | 정상 출석 처리 |
| `test_attend_duplicate_throws` | 오늘 이미 출석 시 `AlreadyAttendedException` 발생 |
| `test_attend_time_not_allowed` | 출석 가능 시간 외 → `AttendanceTimeNotAllowedException` |
| `test_attend_time_allowed` | 출석 가능 시간 내 → 정상 처리 |
| `test_attend_no_time_limit` | 시간 제한 미사용 시 항상 허용 |
| `test_attend_random_point_applied` | 랜덤 포인트 설정 시 확률에 따라 지급 |

### AttendanceStreakServiceTest

```
경로: tests/Unit/Services/AttendanceStreakServiceTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_monthly_streak_completed_on_last_day` | 월의 마지막 날 출석으로 월간 개근 달성 |
| `test_monthly_streak_not_completed_mid_month_start` | 15일부터 시작한 경우 월간 개근 미달성 |
| `test_weekly_streak_completed` | 주간 전일 출석 시 주간 개근 달성 |
| `test_streak_bonus_paid_only_once` | 개근 보너스는 1회만 지급 (`bonus_paid` 중복 방지) |
| `test_yearly_streak_completed` | 연간 전일 출석 시 연간 개근 달성 |

### AttendanceRankServiceTest

```
경로: tests/Unit/Services/AttendanceRankServiceTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_first_attender_rank_1` | 첫 번째 출석자는 1위 |
| `test_rank_bonus_for_rank1` | 1위 보너스 포인트 반환 |
| `test_no_bonus_for_rank4_and_below` | 4위 이상은 순위 보너스 0 |
| `test_daily_rank_record_created_for_top3` | 상위 3위까지만 `attendance_daily_ranks`에 기록 |

---

## 5.4 백엔드 Feature 테스트

### AttendanceControllerTest

```
경로: tests/Feature/Controllers/AttendanceControllerTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_attend_requires_auth` | 미인증 접근 시 401 |
| `test_attend_success_returns_200` | 정상 출석 시 200 반환 |
| `test_attend_duplicate_returns_409` | 오늘 이미 출석 시 409 반환 |
| `test_attend_not_allowed_returns_403` | `yjsoft-attendance.attend` permission 없는 사용자 → permission 미들웨어가 403 반환 |
| `test_attend_time_not_allowed_returns_403` | 시간 외 출석 시도 403 반환 |
| `test_status_returns_monthly_calendar` | status API가 이번 달 달력 데이터 반환 |
| `test_list_returns_paginated_result` | 목록 API가 페이지네이션 결과 반환 |
| `test_random_greeting_returns_string` | 랜덤 인삿말 API가 문자열 반환 |

### AttendanceSettingsControllerTest

```
경로: tests/Feature/Controllers/AttendanceSettingsControllerTest.php
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `test_settings_requires_admin` | 비관리자 접근 시 403 |
| `test_settings_index_returns_all_settings` | 전체 설정 반환 |
| `test_settings_update_saves_successfully` | 설정 저장 후 200 반환 |
| `test_settings_update_validates_bonus_points` | 보너스 포인트 음수 불가 |
| `test_settings_update_validates_time_range` | 시간 범위 유효성 (0~23시, 0~59분) |
| `test_settings_update_validates_probability` | 확률 1~100 범위 |

---

## 5.5 레이아웃 렌더링 테스트 (Vitest)

> **규칙**:
> - 레이아웃 테스트는 해당 확장 디렉토리에 작성
> - 모듈 테스트: `modules/_bundled/{id}/resources/js/__tests__/layouts/`
> - 독립 `vitest.config.ts` 사용 (루트 config 포함 금지)
> - `createLayoutTest()` 유틸리티 사용
> - "인프라 부족" 이유로 레이아웃 테스트 건너뛰기 절대 금지

### user_attendance_index.test.tsx

```
경로: modules/_bundled/yjsoft-attendance/resources/js/__tests__/layouts/user_attendance_index.test.tsx
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `renders attendance page` | 기본 출석부 페이지 렌더링 확인 |
| `shows attend button when not attended` | 미출석 시 출석하기 버튼 표시 |
| `hides attend button when already attended` | 출석 완료 시 버튼 숨김, 완료 메시지 표시 |
| `shows greeting input with random default` | 인삿말 입력 필드에 랜덤 기본값 표시 |
| `calendar shows attended days` | 달력에서 출석일 시각적 구분 |
| `streak detail dropdown toggles` | 자세히 보기 버튼 클릭 시 드롭다운 토글 |
| `pagination works on list` | 목록 페이지네이션 동작 |

```typescript
import { createLayoutTest, screen } from '../utils/layoutTestUtils';
import layoutJson from '../../layouts/user/user_attendance_index.json';

describe('user_attendance_index layout', () => {
  const testUtils = createLayoutTest(layoutJson);

  beforeEach(() => {
    testUtils.mockApi('attendance_status', {
      response: {
        data: {
          is_attended_today: false,
          total_count: 10,
          monthly_records: {
            year: 2026,
            month: 4,
            days: [
              { day: 1, status: 'attended' },
              { day: 2, status: 'absent' },
              { day: 3, status: 'future' }
            ]
          },
          streaks: [],
          today_rank: null
        }
      }
    });

    testUtils.mockApi('attendance_settings', {
      response: {
        data: {
          greetings: { list: ['안녕하세요', '출석합니다'] }
        }
      }
    });

    testUtils.mockApi('attendance_list', {
      response: { data: { data: [], meta: { total: 0, current_page: 1, last_page: 1 } } }
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
        data: { is_attended_today: true, total_count: 11, monthly_records: { year: 2026, month: 4, days: [] }, streaks: [], today_rank: 5 }
      }
    });
    await testUtils.render();
    expect(screen.queryByTestId('attend-btn')).not.toBeInTheDocument();
    expect(screen.getByTestId('attend-complete-msg')).toBeInTheDocument();
  });
});
```

### admin_attendance_settings.test.tsx

```
경로: modules/_bundled/yjsoft-attendance/resources/js/__tests__/layouts/admin_attendance_settings.test.tsx
```

| 테스트 케이스 | 검증 내용 |
|-------------|---------|
| `renders settings page` | 설정 페이지 렌더링 확인 |
| `displays loaded settings` | 불러온 설정값이 UI에 반영됨 |
| `auto attend toggle works` | 자동출석 토글 동작 |
| `time limit fields disabled when disabled` | 시간 제한 미사용 시 시간 입력 비활성화 |
| `greeting list add and delete` | 인삿말 추가/삭제 동작 |
| `save button calls PUT api` | 저장 버튼 클릭 시 PUT API 호출 |

---

## 5.6 vitest.config.ts (모듈 독립)

```
경로: modules/_bundled/yjsoft-attendance/vitest.config.ts
```

```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./resources/js/__tests__/setup.ts'],
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './resources/js'),
    },
  },
});
```

> **규칙**: 루트 vitest.config.ts를 포함(include)하지 않는 독립 설정 파일 사용.

---

## 5.7 단계 완료 체크리스트

### 백엔드 테스트
- [x] `StreakTypeTest` 작성 및 통과
  - [x] 월간 개근 달력 기준 정확한 계산 검증
  - [x] 월을 걸친 연속 출석은 개근 미달성 검증
- [x] `AttendanceServiceTest` 작성 및 통과
  - [x] 중복 출석 방지 검증
  - [x] 시간 제한 로직 검증
- [x] `AttendanceStreakServiceTest` 작성 및 통과
- [x] `AttendanceRankServiceTest` 작성 및 통과
- [x] `AttendanceControllerTest` 작성 및 통과
  - [x] 인증 없이 접근 시 401 반환 검증
  - [x] 권한 없이 접근 시 403 반환 검증
- [x] `AttendanceSettingsControllerTest` 작성 및 통과
  - [x] 비관리자 접근 시 403 반환 검증

### 프론트엔드/레이아웃 테스트
- [x] `vitest.config.ts` (모듈 독립) 작성
- [x] `user_attendance_index.test.tsx` 작성 및 통과
- [x] `admin_attendance_settings.test.tsx` 작성 및 통과
