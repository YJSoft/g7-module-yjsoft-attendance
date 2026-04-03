# 3단계: 백엔드 핵심 구현 (Models / Repository / Service)

> **참고 문서**
> - [service-repository.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md)
> - [controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)
> - [validation.md](https://github.com/gnuboard/g7/blob/main/docs/backend/validation.md)
> - [exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md)
> - [enum.md](https://github.com/gnuboard/g7/blob/main/docs/backend/enum.md)
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 3.1 Models

### AttendanceRecord 모델

```
경로: src/Models/AttendanceRecord.php
```

- `fillable`: `user_id`, `attend_date`, `attend_time`, `greeting`, `base_point`, `bonus_point`, `random_point`, `daily_rank`
- `casts`: `attend_date` → `date`, `is_attended` (가상 속성)
- Scope: `scopeByUser($query, $userId)`, `scopeByMonth($query, $year, $month)`

### AttendanceStreak 모델

```
경로: src/Models/AttendanceStreak.php
```

- `fillable`: `user_id`, `streak_type`, `period_start`, `period_end`, `current_streak`, `is_completed`, `bonus_paid`
- `casts`: `streak_type` → `StreakType` (Enum cast), `period_start` / `period_end` → `date`, `is_completed` / `bonus_paid` → `boolean`

### AttendanceDailyRank 모델

```
경로: src/Models/AttendanceDailyRank.php
```

- `fillable`: `rank_date`, `user_id`, `rank`, `bonus_point`, `bonus_paid`
- `casts`: `rank_date` → `date`, `bonus_paid` → `boolean`

---

## 3.2 Repository 인터페이스 및 구현

> **규칙**: RepositoryInterface 주입 필수. 구체 클래스 직접 주입 금지.  
> 참고: [service-repository.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md)

### AttendanceRepositoryInterface

```
경로: src/Contracts/AttendanceRepositoryInterface.php
```

| 메서드 | 설명 |
|--------|------|
| `findTodayByUser(int $userId): ?AttendanceRecord` | 오늘 출석 기록 조회 |
| `createRecord(array $data): AttendanceRecord` | 출석 기록 생성 |
| `getMonthlyRecords(int $userId, int $year, int $month): Collection` | 월별 출석 목록 |
| `getTodayRank(int $userId): ?int` | 오늘 자신의 출석 순위 |
| `getTodayCount(): int` | 오늘 출석자 수 |
| `getTodayList(int $perPage): LengthAwarePaginator` | 오늘 출석 목록 (페이지네이션) |
| `getUserTotalCount(int $userId): int` | 유저 총 출석일 수 |

### AttendanceStreakRepositoryInterface

```
경로: src/Contracts/AttendanceStreakRepositoryInterface.php
```

| 메서드 | 설명 |
|--------|------|
| `findCurrentStreak(int $userId, StreakType $type): ?AttendanceStreak` | 현재 기간의 개근 기록 조회 |
| `upsertStreak(int $userId, StreakType $type, array $data): AttendanceStreak` | 개근 기록 생성 또는 갱신 |
| `getUserStreaks(int $userId): Collection` | 유저의 전체 개근 현황 |
| `markBonusPaid(int $streakId): void` | 보너스 지급 완료 표시 |

---

## 3.3 AttendanceService (핵심 비즈니스 로직)

```
경로: src/Services/AttendanceService.php
```

의존성 주입: `AttendanceRepositoryInterface`, `AttendanceStreakService`, `AttendanceRankService`, `AttendanceSettingsService`

### attend(int $userId, string $greeting): AttendanceRecord

출석 처리의 핵심 메서드. 다음 순서로 처리한다:

1. **중복 출석 확인**: `findTodayByUser()` → 이미 출석이면 예외 발생
2. **시간 제한 확인**: `checkTimeLimit()` — 현재 시각이 출석 가능 시간대인지 확인
3. **기본 포인트 결정**: 설정의 `base_point` 값 사용
4. **랜덤 포인트 결정**: 설정 `random_point.enabled`가 true이면 확률에 따라 랜덤 포인트 산정
5. **순위 결정**: `AttendanceRankService::getTodayRank($userId)` — 현재 몇 번째 출석인지 계산 (1~3위 보너스 대상 판별)
6. **개근 보너스 결정**: `AttendanceStreakService::calculateStreakBonus($userId)` — 오늘 출석으로 개근이 달성되는지 미리 계산
7. **출석 기록 저장**: `createRecord()`
8. **개근 현황 업데이트**: `AttendanceStreakService::updateStreaks($userId, $today)`
9. **순위 기록 갱신**: `AttendanceRankService::updateDailyRank($userId, $record)`
10. **포인트 지급**: 코어 포인트 API 또는 훅을 통해 총 포인트(기본 + 개근보너스 + 순위보너스 + 랜덤) 지급
11. **결과 반환**: 생성된 `AttendanceRecord` 반환

### checkTimeLimit(): void

- `time_limit.enabled`가 false면 통과
- 현재 서버 시각이 `start_hour:start_minute` ~ `end_hour:end_minute` 범위 내인지 확인
- 범위 밖이면 `AttendanceTimeNotAllowedException` 발생

---

## 3.4 AttendanceStreakService

```
경로: src/Services/AttendanceStreakService.php
```

### updateStreaks(int $userId, Carbon $today): array

세 가지 개근 타입(weekly/monthly/yearly)을 각각 처리한다.  
각 타입에 대해:

1. `StreakType::getPeriod($today)`로 현재 기간의 시작/종료일 계산
2. 해당 기간의 streak 레코드 조회 또는 생성
3. 현재 기간 내 출석 일수 계산 (`attendance_records`에서 해당 기간 조회)
4. 기간 전체 일수와 출석 일수가 같으면 `is_completed = true`로 갱신
5. 신규 완료이고 `bonus_paid = false`이면 보너스 포인트 지급 후 `bonus_paid = true` 표시
6. 결과 반환 (어떤 개근이 달성되었는지)

### 개근 달성 조건

| 타입 | 기간 | 조건 |
|------|------|------|
| 주간 | 해당 주 월~일 | 기간 내 전일 출석 |
| 월간 | 해당 월 1일~말일 | 기간 내 전일 출석 |
| 연간 | 해당 연도 1/1~12/31 | 기간 내 전일 출석 |

> **핵심**: "3월 15일 ~ 4월 14일 연속 출석"은 3월 개근, 4월 개근 어느 쪽도 해당하지 않는다.  
> 3월은 1~14일 결석이 있으므로 3월 개근 실패. 4월은 15일부터 시작했으므로 4월 개근 달성 불가.

---

## 3.5 AttendanceRankService

```
경로: src/Services/AttendanceRankService.php
```

### getTodayRank(int $userId): int

오늘 이 유저가 몇 번째로 출석하는지 반환 (1부터 시작).  
`attendance_records`에서 오늘 날짜의 기록 수 + 1.

### getRankBonus(int $rank): int

순위에 따른 보너스 포인트 반환:
- 1위: `bonus.rank1_point`
- 2위: `bonus.rank2_point`
- 3위: `bonus.rank3_point`
- 4위 이상: 0

### updateDailyRank(int $userId, AttendanceRecord $record): void

`rank <= 3`인 경우 `attendance_daily_ranks` 테이블에 저장 또는 갱신.

---

## 3.6 AttendanceSettingsService

```
경로: src/Services/AttendanceSettingsService.php
```

`ModuleSettingsInterface` 구현.

코어의 `module_setting('yjsoft-attendance', ...)` 헬퍼 함수가 이 서비스를 자동으로 사용하도록 `ModuleSettingsInterface`를 구현한다.  
참고: [module-settings.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md)

---

## 3.7 Custom Exceptions

> **규칙**: 예외 메시지 하드코딩 금지 → `__()` 함수 필수  
> 참고: [exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md)

| 예외 클래스 | 발생 조건 | HTTP 상태 |
|------------|---------|----------|
| `AlreadyAttendedException` | 오늘 이미 출석 | 409 |
| `AttendanceNotAllowedException` | 권한 없음 | 403 |
| `AttendanceTimeNotAllowedException` | 출석 가능 시간 아님 | 403 |

각 예외의 메시지는 `__('yjsoft-attendance::messages.xxx')` 형태로 다국어 처리한다.

---

## 3.8 단계 완료 체크리스트

- [ ] `AttendanceRecord` 모델 작성
- [ ] `AttendanceStreak` 모델 작성 (StreakType Enum cast 포함)
- [ ] `AttendanceDailyRank` 모델 작성
- [ ] `AttendanceRepositoryInterface` 정의
- [ ] `AttendanceStreakRepositoryInterface` 정의
- [ ] `AttendanceRepository` 구현
- [ ] `AttendanceStreakRepository` 구현
- [ ] `AttendanceSettingsService` (`ModuleSettingsInterface`) 구현
- [ ] `AttendanceStreakService` 구현
  - [ ] 개근 달성 조건 로직 (달력 기준 확인)
- [ ] `AttendanceRankService` 구현
- [ ] `AttendanceService` 구현
  - [ ] 권한 확인 로직
  - [ ] 시간 제한 확인 로직
  - [ ] 포인트 지급 로직
- [ ] Custom Exceptions 작성 (다국어 키 사용)
- [ ] Service Provider에서 Repository 바인딩 등록
