# 출석부 모듈 (yjsoft-attendance)

[그누보드7](https://github.com/gnuboard/g7)용 출석체크 모듈입니다.

매일 출석 체크 기능과 함께 개근 보너스, 순위 보너스, 랜덤 포인트 등 다양한 포인트 보상 시스템을 제공합니다.

## 주요 기능

| 기능 | 설명 |
|------|------|
| **출석 체크** | 하루 1회 출석, DB 레벨 중복 방지 |
| **기본 포인트** | 출석 시 기본 포인트 지급 (관리자 설정) |
| **순위 보너스** | 1등·2등·3등 출석자에게 추가 보너스 |
| **개근 보너스** | 주간·월간·연간 개근 시 보너스 지급 |
| **랜덤 포인트** | 확률 기반 추가 포인트 (확률·범위 설정 가능) |
| **시간 제한** | 출석 가능 시간대 제한 |
| **자동 출석** | 로그인 시 자동 출석 처리 |
| **인삿말** | 기본 인삿말 목록에서 랜덤 제공 |
| **다국어** | 한국어, 영어 지원 |

## 화면 구성

### 사용자 화면 (`/attendance`)
- 출석 버튼 + 인삿말 입력
- 실시간 시각 표시
- 이번 달 출석 달력
- 개근 현황 (주간/월간/연간)
- 오늘의 출석 목록 (페이지네이션)

### 관리자 화면 (`/admin/attendance/settings`)
- 기본 포인트, 개근 보너스, 순위 보너스 설정
- 출석 시간 제한 설정
- 랜덤 포인트 확률/범위 설정
- 기본 인삿말 관리
- 자동 출석 on/off

## 요구 사항

- 그누보드7 `>=1.0.0`
- PHP `>=8.2`

## 설치

1. 이 저장소를 그누보드7 프로젝트의 `modules/yjsoft-attendance` 경로에 배치합니다.
2. 그누보드7 관리자 패널에서 모듈을 활성화합니다.
3. 마이그레이션이 자동으로 실행됩니다.

## 디렉토리 구조

```
├── config/settings/       # 모듈 기본 설정값
├── database/migrations/   # DB 마이그레이션 (3개 테이블)
├── resources/
│   ├── css/               # 스타일시트
│   ├── js/                # 프론트엔드 핸들러 + 테스트
│   ├── lang/              # 프론트엔드 다국어 (ko.json, en.json)
│   ├── layouts/           # 레이아웃 JSON (user, admin)
│   └── routes/            # 프론트엔드 라우트 정의
├── src/
│   ├── Contracts/         # Repository 인터페이스
│   ├── Enums/             # StreakType, AccessControlMode
│   ├── Exceptions/        # 커스텀 예외
│   ├── Http/              # Controllers, Requests, Resources
│   ├── Listeners/         # 자동 출석 리스너
│   ├── Models/            # Eloquent 모델
│   ├── Providers/         # ServiceProvider
│   ├── Repositories/      # Repository 구현체
│   ├── Services/          # 비즈니스 로직
│   ├── lang/              # 백엔드 다국어 (ko, en)
│   └── routes/            # API 라우트
├── tests/
│   ├── Unit/              # 서비스, Enum 단위 테스트
│   └── Feature/           # 컨트롤러 통합 테스트
├── module.json            # 모듈 메타정보
├── module.php             # 모듈 클래스 (권한, 메뉴, 훅)
└── CHANGELOG.md           # 변경 내역
```

## API 엔드포인트

> 모든 경로에는 `api/modules/yjsoft-attendance` 프리픽스가 자동 적용됩니다.

### 인증 사용자 (`/auth`)

| 메서드 | 경로 | 설명 |
|--------|------|------|
| `POST` | `/auth/attend` | 출석 처리 |
| `GET` | `/auth/status` | 출석 현황 (달력, 개근, 순위) |
| `GET` | `/auth/list` | 오늘의 출석 목록 |
| `GET` | `/auth/random-greeting` | 랜덤 인삿말 |
| `GET` | `/auth/settings` | 공개 설정 |

### 관리자 (`/admin`)

| 메서드 | 경로 | 설명 |
|--------|------|------|
| `GET` | `/admin/settings` | 전체 설정 조회 |
| `PUT` | `/admin/settings` | 설정 저장 |
| `GET` | `/admin/stats` | 출석 통계 |

## 테스트

### 백엔드 (PHPUnit)

G7 코어 프로젝트 내에서 실행합니다:

```bash
php vendor/bin/phpunit modules/yjsoft-attendance/tests
```

### 프론트엔드 (Vitest)

모듈 디렉토리에서 실행합니다:

```bash
npm install
npm run test:run
```

## 라이선스

[MIT License](LICENSE)

