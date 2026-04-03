# 1단계: 모듈 기반 구조 설계

> **참고 문서**
> - [module-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md)
> - [module-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md)
> - [module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)
> - [permissions.md](https://github.com/gnuboard/g7/blob/main/docs/extension/permissions.md)
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 1.1 모듈 식별자 및 네이밍

| 항목 | 값 |
|------|-----|
| 디렉토리명 | `yjsoft-attendance` |
| 네임스페이스 | `Modules\Yjsoft\Attendance\` |
| Composer 패키지명 | `modules/yjsoft-attendance` |
| 라우트 접두사 | `yjsoft-attendance.` |
| 권한 접두사 | `yjsoft-attendance.` |

---

## 1.2 디렉토리 구조

```
modules/_bundled/yjsoft-attendance/
├── module.json                          # 메타데이터 SSoT
├── module.php                           # AbstractModule 구현
├── LICENSE                              # 라이선스 전문
├── composer.json                        # 오토로딩 설정
├── package.json                         # npm 패키지 (프론트엔드 에셋용)
├── vite.config.ts                       # Vite 빌드 설정
├── tsconfig.json                        # TypeScript 설정
├── config/
│   └── settings/
│       └── defaults.json                # 모듈 기본 설정값
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_attendance_records_table.php
│   │   ├── xxxx_create_attendance_streaks_table.php
│   │   └── xxxx_create_attendance_daily_ranks_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   ├── js/
│   │   ├── index.ts                     # 에셋 엔트리포인트
│   │   └── handlers/
│   │       └── autoAttendance.ts        # 자동출석 핸들러
│   ├── css/
│   │   └── main.css
│   ├── lang/
│   │   ├── ko.json                      # 프론트엔드 한국어
│   │   └── en.json                      # 프론트엔드 영어
│   ├── layouts/
│   │   ├── admin/
│   │   │   ├── admin_attendance_settings.json   # 설정 페이지
│   │   │   └── admin_attendance_skin.json       # 스킨 관리 페이지
│   │   └── user/
│   │       └── user_attendance_index.json       # 출석부 페이지
│   └── routes/
│       ├── admin.json                   # 관리자 라우트
│       └── user.json                    # 유저 라우트
└── src/
    ├── Contracts/
    │   ├── AttendanceRepositoryInterface.php
    │   ├── AttendanceStreakRepositoryInterface.php
    │   └── AttendanceSettingsServiceInterface.php
    ├── Http/
    │   ├── Controllers/
    │   │   └── Api/
    │   │       ├── Admin/
    │   │       │   └── AttendanceSettingsController.php
    │   │       └── Auth/
    │   │           └── AttendanceController.php
    │   ├── Requests/
    │   │   ├── StoreAttendanceRequest.php
    │   │   └── UpdateAttendanceSettingsRequest.php
    │   └── Resources/
    │       ├── AttendanceResource.php
    │       ├── AttendanceListResource.php
    │       └── AttendanceSettingsResource.php
    ├── Models/
    │   ├── AttendanceRecord.php
    │   ├── AttendanceStreak.php
    │   └── AttendanceDailyRank.php
    ├── Repositories/
    │   ├── AttendanceRepository.php
    │   └── AttendanceStreakRepository.php
    ├── Services/
    │   ├── AttendanceService.php
    │   ├── AttendanceStreakService.php
    │   ├── AttendanceRankService.php
    │   └── AttendanceSettingsService.php
    ├── lang/                            # 백엔드 다국어 파일 (src/ 하위 필수)
    │   ├── ko/
    │   │   └── messages.php
    │   └── en/
    │       └── messages.php
    └── routes/
        └── api.php
```

---

## 1.3 module.json

```json
{
  "name": {
    "ko": "출석부",
    "en": "Attendance"
  },
  "description": {
    "ko": "그누보드7용 출석체크 모듈. 개근 보너스, 순위 보너스, 랜덤 포인트 기능 제공.",
    "en": "Attendance check module for Gnuboard7. Provides streak bonuses, ranking bonuses, and random points."
  },
  "version": "1.0.0",
  "g7_version": ">=1.0.0",
  "license": "MIT",
  "github_url": "https://github.com/YJSoft/g7-module-yjsoft-attendance",
  "assets": {
    "js": "dist/js/module.iife.js",
    "css": "dist/css/module.css"
  },
  "loading": {
    "strategy": "global",
    "priority": 100
  }
}
```

---

## 1.4 Module.php (AbstractModule 구현)

`AbstractModule`을 상속하여 구현한다.  
참고: [module-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md)

### 정의할 메서드

#### `getRoles()`
출석부 모듈은 자체 역할을 별도로 정의하지 않는다.  
출석 허용/금지는 기존 코어 역할(Role) 식별자를 사용하며, 모듈 설정에서 운영자가 직접 지정한다.

#### `getPermissions()`

| 권한 식별자 | 설명 |
|------------|------|
| `yjsoft-attendance.attend` | 출석 실행 권한 |
| `yjsoft-attendance.admin.settings` | 출석부 설정 관리 권한 |
| `yjsoft-attendance.admin.view` | 출석 통계 조회 권한 |

`attend` 권한은 소유자 개념 없음 (role-only scope).  
`admin.*` 권한은 `admin` 역할에만 부여.

#### `getAdminMenus()`

```
출석부 관리 (slug: yjsoft-attendance)
├── 설정      → /admin/yjsoft-attendance/settings
└── 스킨 관리 → /admin/yjsoft-attendance/skin
```

#### `getHookListeners()`

- `AutoAttendanceListener` — 로그인 이벤트 수신 → 자동출석 처리 (설정에서 활성화 시)

---

## 1.5 composer.json

```json
{
  "name": "modules/yjsoft-attendance",
  "description": "Attendance module for Gnuboard7 by YJSoft",
  "type": "library",
  "license": "MIT",
  "authors": [
    {
      "name": "YJSoft",
      "email": "admin@yjsoft.net"
    }
  ],
  "require": {
    "php": "^8.2"
  },
  "autoload": {
    "psr-4": {
      "Modules\\Yjsoft\\Attendance\\": "src/"
    }
  }
}
```

> **규칙**: 루트 `composer.json`에 모듈 패키지를 추가하지 않는다.  
> 참고: [module-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md)

---

## 1.6 config/settings/defaults.json

모듈 설정의 기본값 및 프론트엔드 노출 제어.  
참고: [module-settings.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md)

```json
{
  "_meta": {
    "version": "1.0.0",
    "description": "출석부 모듈 설정",
    "categories": [
      "general",
      "bonus",
      "time_limit",
      "random_point",
      "greetings",
      "access_control"
    ]
  },
  "defaults": {
    "general": {
      "auto_attend": false
    },
    "bonus": {
      "base_point": 10,
      "weekly_streak_point": 50,
      "monthly_streak_point": 100,
      "yearly_streak_point": 1000,
      "rank1_point": 100,
      "rank2_point": 50,
      "rank3_point": 30
    },
    "time_limit": {
      "enabled": false,
      "start_hour": 0,
      "start_minute": 0,
      "end_hour": 23,
      "end_minute": 59
    },
    "random_point": {
      "enabled": false,
      "min_point": 1,
      "max_point": 100,
      "probability": 30
    },
    "greetings": {
      "list": [
        "안녕하세요",
        "출석합니다",
        "출첵!",
        "오늘도 출첵!",
        "좋은하루 되세요"
      ]
    },
    "access_control": {
      "mode": "whitelist",
      "roles": ["user"]
    }
  },
  "frontend_schema": {
    "general": {
      "expose": true,
      "fields": {
        "auto_attend": { "expose": true }
      }
    },
    "bonus": { "expose": true },
    "time_limit": { "expose": true },
    "random_point": { "expose": true },
    "greetings": { "expose": true },
    "access_control": { "expose": true }
  }
}
```

### 설정 카테고리 설명

| 카테고리 | 설명 |
|---------|------|
| `general` | 자동출석 사용 여부 |
| `bonus` | 기본 포인트, 개근 보너스, 순위 보너스 설정 |
| `time_limit` | 출석 가능 시간대 설정 |
| `random_point` | 랜덤 추가 포인트 설정 |
| `greetings` | 기본 인삿말 목록 |
| `access_control` | 출석 허용/금지 권한 설정 (화이트리스트/블랙리스트) |

---

## 1.7 다국어 파일 구조

> **참고 문서**: [module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)

### 핵심 원칙

| 구분 | 파일 경로 | 형식 | 사용처 |
|------|---------|------|------|
| 백엔드 | `src/lang/{locale}/*.php` | PHP 배열 | Laravel `__()` 함수 |
| 프론트엔드 | `resources/lang/{locale}.json` | JSON (중첩 객체) | 레이아웃 JSON `$t:` 문법 |

> **규칙**: 백엔드 다국어 파일은 반드시 `src/lang/` 디렉토리에 위치해야 한다.  
> TranslationServiceProvider가 이 경로에서 파일을 자동으로 로드한다.  
> 참고: [module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)

### 백엔드 (`src/lang/ko/messages.php`)

```php
<?php
return [
    'attend_success'         => '출석이 완료되었습니다.',
    'already_attended'       => '오늘 이미 출석하셨습니다.',
    'not_allowed'            => '출석 권한이 없습니다.',
    'time_not_allowed'       => '현재 출석 가능 시간이 아닙니다.',
    'settings_saved'         => '설정이 저장되었습니다.',
    'settings_fetch_success' => '설정을 불러왔습니다.',
    'streak' => [
        'weekly'  => '주간 개근',
        'monthly' => '월간 개근',
        'yearly'  => '연간 개근',
    ],
    // ...
];
```

백엔드에서 사용 시 **더블 콜론(`::`) 문법** 필수:

```php
// ✅ 올바른 사용 (더블 콜론 ::)
__('yjsoft-attendance::messages.attend_success');
__('yjsoft-attendance::messages.streak.weekly');

// ❌ 금지 (점 . 사용)
__('yjsoft-attendance.messages.attend_success');  // 작동하지 않음
```

> **규칙**: 예외 메시지 하드코딩 금지 → `__()` 함수 필수  
> 참고: [exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md)

### 프론트엔드 (`resources/lang/ko.json`)

> **규칙**: JSON 파일에는 `moduleIdentifier` 없이 순수 키만 작성한다.  
> 템플릿 서빙 시 시스템이 자동으로 `yjsoft-attendance`를 최상위 키로 병합한다.  
> **금지**: `{ "yjsoft-attendance": { ... } }` 형태로 직접 작성하지 않는다.  
> **형식**: 플랫 dot-notation 키(`"attendance.title"`) 사용 금지 → 중첩 객체 구조로 작성한다.

```json
{
  "attendance": {
    "title": "출석부",
    "attend_btn": "출석하기",
    "greeting_placeholder": "인삿말을 입력하세요",
    "already_done": "출석 완료",
    "streak": {
      "weekly": "주간 개근",
      "monthly": "월간 개근",
      "yearly": "연간 개근"
    },
    "rank": {
      "title": "출석 순위"
    }
  },
  "settings": {
    "title": "출석부 설정",
    "save": "설정 저장",
    "save_success": "설정이 저장되었습니다.",
    "delete": "삭제"
  }
}
```

레이아웃 JSON에서 사용 시 `moduleIdentifier`가 자동으로 앞에 붙는다:

```json
{ "text": "$t:yjsoft-attendance.attendance.title" }
{ "text": "$t:yjsoft-attendance.attendance.streak.weekly" }
{ "text": "$t:yjsoft-attendance.settings.save" }
```

### 파일이 커질 경우 — `$partial` 분리

JSON 파일이 500줄을 초과하면 `$partial` 디렉티브로 도메인별 분리:

```
resources/lang/
├── ko.json                          # 메인 (fragment 참조)
├── en.json
└── partial/
    ├── ko/
    │   ├── attendance.json          # 출석 관련 키
    │   └── settings.json           # 설정 관련 키
    └── en/
        ├── attendance.json
        └── settings.json
```

```json
// resources/lang/ko.json
{
  "attendance": { "$partial": "partial/ko/attendance.json" },
  "settings":   { "$partial": "partial/ko/settings.json" }
}
```

> 500줄 이하인 경우 단일 파일 유지 권장.  
> 참고: [module-i18n.md — $partial Fragment 시스템](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)

---

## 1.8 단계 완료 체크리스트

- [ ] `modules/_bundled/yjsoft-attendance/` 디렉토리 생성
- [ ] `module.json` 작성
- [ ] `LICENSE` 파일 추가
- [ ] `composer.json` 작성 (루트 composer.json 수정 금지)
- [ ] `module.php` (AbstractModule 상속) 작성
  - [ ] `getPermissions()` 정의
  - [ ] `getAdminMenus()` 정의
  - [ ] `getHookListeners()` 정의
- [ ] `config/settings/defaults.json` 작성
- [ ] `src/lang/ko/messages.php` 작성 (백엔드 다국어, `src/lang/` 경로 필수)
- [ ] `src/lang/en/messages.php` 작성
- [ ] `resources/lang/ko.json` 작성 (중첩 객체 구조, moduleIdentifier 없이 작성)
- [ ] `resources/lang/en.json` 작성
- [ ] `php artisan extension:update-autoload` 실행 확인
