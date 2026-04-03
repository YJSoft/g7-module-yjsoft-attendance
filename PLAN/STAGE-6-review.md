# 6단계: 최종 검토 및 마무리

> **참고 문서**
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)
> - [AGENTS.md — 레이아웃 작성 체크리스트](https://github.com/gnuboard/g7/blob/main/AGENTS.md#레이아웃-작성-체크리스트)
> - [module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)
> - [service-provider.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-provider.md)

---

## 6.1 전체 구현 완료 체크리스트

### 1단계 — 모듈 기반 구조

- [ ] `module.json` 작성 완료
- [ ] `module.php` (AbstractModule) 작성 완료
  - [ ] `getPermissions()`: `attend`, `admin.settings`, `admin.view`
  - [ ] `getAdminMenus()`: 설정, 스킨 관리 메뉴
  - [ ] `getHookListeners()`: `AutoAttendanceListener` 등록
- [ ] `composer.json` 작성 (루트 composer.json 수정 금지 확인)
- [ ] `config/settings/defaults.json` 작성 (6개 카테고리)
- [ ] `LICENSE` 파일 포함
- [ ] 다국어 파일 완료 (`lang/ko/`, `lang/en/`, `resources/lang/ko.json`, `resources/lang/en.json`)

### 2단계 — 데이터베이스

- [ ] `StreakType` Enum 작성
- [ ] `AccessControlMode` Enum 작성
- [ ] 마이그레이션 3개 작성 완료
- [ ] 마이그레이션 실행 및 롤백 정상 동작 확인

### 3단계 — 백엔드

- [ ] Models (AttendanceRecord, AttendanceStreak, AttendanceDailyRank)
- [ ] Repository 인터페이스 + 구현체
- [ ] Service Provider에서 Repository 인터페이스 바인딩
- [ ] `AttendanceService`: 출석 처리, 권한 확인, 시간 제한, 포인트 지급
- [ ] `AttendanceStreakService`: 달력 기준 개근 처리
- [ ] `AttendanceRankService`: 순위 계산 및 기록
- [ ] `AttendanceSettingsService` (ModuleSettingsInterface 구현)
- [ ] Custom Exceptions (다국어 메시지 사용)
- [ ] `AutoAttendanceListener` 구현
- [ ] 컨트롤러 3개 (Auth 출석, Admin 설정, Admin 통계)
- [ ] FormRequests 2개
- [ ] API Resources (AttendanceResource, AttendanceListResource)
- [ ] API 라우트 (`src/routes/api.php`) — 모든 라우트 `name()` 지정

### 4단계 — 프론트엔드

- [ ] 유저 출석부 레이아웃 (`user_attendance_index.json`)
- [ ] 관리자 설정 레이아웃 (`admin_attendance_settings.json`)
- [ ] 관리자 스킨 관리 레이아웃 (`admin_attendance_skin.json`)
- [ ] 유저 라우트 (`resources/routes/user.json`)
- [ ] 관리자 라우트 (`resources/routes/admin.json`)
- [ ] 프론트엔드 에셋 (`resources/js/index.ts`, 시각 타이머 핸들러)
- [ ] Vite 빌드 설정 (`package.json`, `vite.config.ts`, `tsconfig.json`)

### 5단계 — 테스트

- [ ] 백엔드 Unit 테스트 전체 통과
- [ ] 백엔드 Feature 테스트 전체 통과
- [ ] 레이아웃 렌더링 테스트 전체 통과

---

## 6.2 AGENTS.md 레이아웃 작성 체크리스트

> 참고: [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

각 레이아웃 JSON 파일에 대해 아래 항목을 모두 확인한다:

- [ ] 레이아웃 구조가 `layout-json.md` 스키마와 일치하는가?
- [ ] 사용할 컴포넌트가 `components.md`에 정의되어 있는가?
- [ ] 컴포넌트 props가 `component-props.md`에 정의된 것만 사용하는가?
- [ ] 사용할 핸들러가 `actions.md`에 정의되어 있는가?
- [ ] 핸들러의 params 구조가 `actions-handlers.md`와 일치하는가?
- [ ] 데이터 바인딩 문법이 `data-binding.md`에 정의된 형식인가?
- [ ] 다크 모드 클래스가 `dark-mode.md` 규칙을 따르는가?
- [ ] 기존 유사 레이아웃에서 동일 패턴이 사용되고 있는가?

---

## 6.3 보안 검토

| 항목 | 확인 내용 |
|------|---------|
| 유저 페이지 API 분리 | `/api/modules/yjsoft-attendance/auth/*` 만 사용, `/api/admin/*` 또는 `../admin/*` 호출 없음 |
| 권한 확인 | 출석 권한 없는 사용자 → 403 반환 |
| 중복 출석 방지 | `UNIQUE(user_id, attend_date)` + 서비스 레이어 이중 확인 |
| 개근 보너스 중복 방지 | `bonus_paid` 플래그로 중복 지급 방지 |
| 입력값 검증 | FormRequest에서 모든 입력값 유효성 검사 |
| SQL Injection | Eloquent ORM 사용으로 방지 |
| XSS | 인삿말 입력 최대 255자, 프론트엔드에서 text 바인딩 사용 |

---

## 6.4 성능 검토

| 항목 | 고려 사항 |
|------|---------|
| 달력 데이터 조회 | 월별 인덱스(`attend_date`)로 쿼리 최적화 |
| 출석자 목록 | 페이지네이션 (기본 20개) |
| 개근 현황 | `attendance_streaks` 별도 테이블로 집계 쿼리 최소화 |
| 설정 캐싱 | `AttendanceSettingsService`에서 인스턴스 캐싱 (`$this->settings !== null` 패턴) |
| 자동출석 | 로그인 훅에서 동기 처리, 오류 발생 시 무음 실패 |

---

## 6.5 다국어 완성도 검토

> **규칙**: 모든 예외/응답 메시지는 `__()` 함수 필수. 하드코딩 금지.  
> 참고: [exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md)

### 백엔드 다국어 키 목록 (`src/lang/ko/messages.php`)

```php
return [
    // 출석 처리
    'attend_success'            => '출석이 완료되었습니다.',
    'already_attended'          => '오늘 이미 출석하셨습니다.',
    'time_not_allowed'          => '현재 출석 가능 시간이 아닙니다.',
    'attend_fetch_success'      => '출석 정보를 불러왔습니다.',
    'list_fetch_success'        => '출석자 목록을 불러왔습니다.',
    'greeting_fetch_success'    => '인삿말을 불러왔습니다.',

    // 설정
    'settings_fetch_success'    => '설정을 불러왔습니다.',
    'settings_save_success'     => '설정이 저장되었습니다.',
    'settings_save_failed'      => '설정 저장에 실패했습니다.',

    // 개근
    'streak.weekly'             => '주간 개근',
    'streak.monthly'            => '월간 개근',
    'streak.yearly'             => '연간 개근',
];
```

### 프론트엔드 다국어 키 목록 (`resources/lang/ko.json`)

모든 UI 텍스트는 `$t:yjsoft-attendance.xxx` 형태로 참조.

---

### 활성화 후 확인 사항

- [ ] DB에 레이아웃 등록 확인 (`template_layouts` 테이블)
- [ ] 관리자 메뉴에 "출석부 관리" 메뉴 표시 확인
- [ ] `/attendance` 유저 출석 페이지 접근 가능 확인
- [ ] `/admin/yjsoft-attendance/settings` 관리자 설정 페이지 접근 가능 확인
- [ ] 출석 API 호출 → 출석 기록 생성 확인
- [ ] 설정 저장 API 호출 → 설정 파일 저장 확인

---

## 6.7 단계 완료 체크리스트

- [ ] 전체 구현 완료 체크리스트(6.1) 모두 완료
- [ ] 레이아웃 작성 체크리스트(6.2) 모두 통과
- [ ] 보안 검토(6.3) 완료
- [ ] 성능 검토(6.4) 완료
- [ ] 다국어 완성도 검토(6.5) 완료 (하드코딩 메시지 없음)
- [ ] FORBIDDEN-PATTERNS.md의 금지 패턴이 코드에 없음 확인
- [ ] 코드 리뷰 완료
