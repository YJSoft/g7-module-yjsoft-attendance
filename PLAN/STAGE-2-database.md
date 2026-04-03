# 2단계: 데이터베이스 설계

> **참고 문서**
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)
> - [service-repository.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md)
> - [enum.md](https://github.com/gnuboard/g7/blob/main/docs/backend/enum.md)

---

## 2.1 테이블 목록

| 테이블명 | 설명 |
|---------|------|
| `attendance_records` | 출석 기록 (날짜별 1인 1행) |
| `attendance_streaks` | 개근 현황 (주간/월간/연간 연속 출석 추적) |
| `attendance_daily_ranks` | 일별 순위 기록 (1~3위 보너스 산정용) |

---

## 2.2 `attendance_records` 테이블

출석 1회당 1행을 저장한다.

```php
Schema::create('attendance_records', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->index();
    $table->date('attend_date')->index();           // 출석 날짜 (YYYY-MM-DD)
    $table->time('attend_time');                    // 출석 시각
    $table->string('greeting', 255)->nullable();   // 인삿말
    $table->integer('base_point')->default(0);      // 기본 지급 포인트
    $table->integer('bonus_point')->default(0);     // 개근/순위 보너스 합계
    $table->integer('random_point')->default(0);    // 랜덤 추가 포인트
    $table->integer('daily_rank')->nullable();      // 오늘의 출석 순위
    $table->timestamps();

    // 동일 유저가 같은 날 중복 출석 방지
    $table->unique(['user_id', 'attend_date']);
});
```

### 컬럼 상세

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `user_id` | `bigint unsigned` | 그누보드7 유저 ID (FK 없음, 코어 users 테이블 참조) |
| `attend_date` | `date` | 출석 날짜. UNIQUE(user_id, attend_date)로 하루 1회 보장 |
| `attend_time` | `time` | 출석 시각 (HH:MM:SS) |
| `greeting` | `varchar(255)` | 사용자가 입력한 인삿말 |
| `base_point` | `int` | 출석 시 기본 지급 포인트 (설정에 따라 변동) |
| `bonus_point` | `int` | 개근 보너스 + 순위 보너스 합산값 (출석 시점에 결정) |
| `random_point` | `int` | 랜덤 추가 포인트 (0이면 미지급) |
| `daily_rank` | `int nullable` | 해당 날짜의 출석 순위 (1~3위만 기록, 나머지 NULL) |

> **설계 원칙**: `user_id`에 외래키(FK)를 걸지 않는다. 그누보드7 코어 users 테이블과의 강결합을 피하기 위해 소프트 참조 방식을 사용한다.

---

## 2.3 `attendance_streaks` 테이블

개근 현황을 추적한다. 주간/월간/연간 개근을 각각 관리한다.

```php
Schema::create('attendance_streaks', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->index();
    $table->enum('streak_type', ['weekly', 'monthly', 'yearly']);

    // 개근 중인 기간의 시작/종료 날짜
    $table->date('period_start');   // 예: 2026-03-01 (월간)
    $table->date('period_end');     // 예: 2026-03-31 (월간)

    // 현재 연속 출석 일수
    $table->integer('current_streak')->default(0);

    // 개근 달성 여부
    $table->boolean('is_completed')->default(false);

    // 개근 보너스 지급 여부 (중복 지급 방지)
    $table->boolean('bonus_paid')->default(false);

    $table->timestamps();

    $table->unique(['user_id', 'streak_type', 'period_start']);
});
```

### 개근 판정 로직 설명

개근은 **달력 단위**로 판정한다:
- **주간 개근**: ISO 8601 기준 해당 주의 월요일~일요일 전일 출석
- **월간 개근**: 해당 월의 1일~말일 전일 출석 (예: 3월 1일~31일)
- **연간 개근**: 해당 연도의 1월 1일~12월 31일 전일 출석

> **주의**: 3월 15일~4월 14일처럼 달을 걸쳐 연속 출석해도 월간 개근으로 인정하지 않는다.  
> 각 기간의 `period_start` ~ `period_end` 범위 내 전일 출석이어야 한다.

### streak_type 값

| 값 | 기간 | 예시 |
|-----|------|------|
| `weekly` | 월요일~일요일 | 2026-03-30 ~ 2026-04-05 |
| `monthly` | 1일~말일 | 2026-03-01 ~ 2026-03-31 |
| `yearly` | 1/1~12/31 | 2026-01-01 ~ 2026-12-31 |

> **규칙**: 상태/타입/분류 값은 Enum 필수 (PHP 8.1+ Backed Enum)  
> 참고: [enum.md](https://github.com/gnuboard/g7/blob/main/docs/backend/enum.md)

---

## 2.4 `attendance_daily_ranks` 테이블

일별 출석 순위 상위 3명의 기록을 별도 저장한다. 순위 보너스 지급 이력과 실시간 조회에 사용한다.

```php
Schema::create('attendance_daily_ranks', function (Blueprint $table) {
    $table->id();
    $table->date('rank_date')->index();
    $table->unsignedBigInteger('user_id');
    $table->tinyInteger('rank');              // 1, 2, 3
    $table->integer('bonus_point');           // 해당 순위 보너스 포인트
    $table->boolean('bonus_paid')->default(false); // 보너스 지급 여부
    $table->timestamps();

    $table->unique(['rank_date', 'rank']);
    $table->index(['rank_date', 'user_id']);
});
```

---

## 2.5 Enum 정의

### `StreakType` (PHP Backed Enum)

```php
<?php

namespace Modules\Yjsoft\Attendance\Enums;

enum StreakType: string
{
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::Weekly  => __('yjsoft-attendance::messages.streak.weekly'),
            self::Monthly => __('yjsoft-attendance::messages.streak.monthly'),
            self::Yearly  => __('yjsoft-attendance::messages.streak.yearly'),
        };
    }

    /**
     * 주어진 날짜가 속하는 기간의 시작/종료일 반환
     */
    public function getPeriod(\DateTimeInterface $date): array
    {
        return match($this) {
            self::Weekly  => $this->getWeekPeriod($date),
            self::Monthly => $this->getMonthPeriod($date),
            self::Yearly  => $this->getYearPeriod($date),
        };
    }

    private function getWeekPeriod(\DateTimeInterface $date): array
    {
        // ISO 8601: 월요일 시작
        $dayOfWeek = (int) date('N', $date->getTimestamp()); // 1=Mon, 7=Sun
        $monday = (clone \Carbon\Carbon::instance($date))->subDays($dayOfWeek - 1)->startOfDay();
        $sunday = (clone $monday)->addDays(6)->endOfDay();
        return ['start' => $monday->toDateString(), 'end' => $sunday->toDateString()];
    }

    private function getMonthPeriod(\DateTimeInterface $date): array
    {
        $carbon = \Carbon\Carbon::instance($date);
        return [
            'start' => $carbon->copy()->startOfMonth()->toDateString(),
            'end'   => $carbon->copy()->endOfMonth()->toDateString(),
        ];
    }

    private function getYearPeriod(\DateTimeInterface $date): array
    {
        $carbon = \Carbon\Carbon::instance($date);
        return [
            'start' => $carbon->copy()->startOfYear()->toDateString(),
            'end'   => $carbon->copy()->endOfYear()->toDateString(),
        ];
    }
}
```

---

## 2.6 마이그레이션 순서

1. `attendance_records` 테이블 생성
2. `attendance_streaks` 테이블 생성
3. `attendance_daily_ranks` 테이블 생성

마이그레이션 파일명 예시:
```
2026_04_03_000001_create_attendance_records_table.php
2026_04_03_000002_create_attendance_streaks_table.php
2026_04_03_000003_create_attendance_daily_ranks_table.php
```

---

## 2.7 인덱스 전략

| 테이블 | 인덱스 | 목적 |
|-------|--------|------|
| `attendance_records` | `(user_id, attend_date)` UNIQUE | 중복 출석 방지, 유저별 날짜 조회 |
| `attendance_records` | `attend_date` | 날짜별 전체 목록 조회 (순위 계산) |
| `attendance_streaks` | `(user_id, streak_type, period_start)` UNIQUE | 기간별 개근 기록 중복 방지 |
| `attendance_daily_ranks` | `(rank_date, rank)` UNIQUE | 날짜별 순위 중복 방지 |
| `attendance_daily_ranks` | `(rank_date, user_id)` | 유저의 날짜별 순위 조회 |

---

## 2.8 단계 완료 체크리스트

- [x] `StreakType` Enum 작성
- [x] `AccessControlMode` Enum 작성
- [x] `attendance_records` 마이그레이션 작성
- [x] `attendance_streaks` 마이그레이션 작성
- [x] `attendance_daily_ranks` 마이그레이션 작성
