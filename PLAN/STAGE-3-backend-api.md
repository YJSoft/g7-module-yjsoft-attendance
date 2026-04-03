# 3단계: 백엔드 API 구현 (Controllers / Routes / Resources)

> **참고 문서**
> - [controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)
> - [routing.md](https://github.com/gnuboard/g7/blob/main/docs/backend/routing.md)
> - [validation.md](https://github.com/gnuboard/g7/blob/main/docs/backend/validation.md)
> - [response-helper.md](https://github.com/gnuboard/g7/blob/main/docs/backend/response-helper.md)
> - [api-resources.md](https://github.com/gnuboard/g7/blob/main/docs/backend/api-resources.md)
> - [module-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md)
> - [module-settings.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md)
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 3A.1 컨트롤러 계층

> **규칙**: 역할에 따른 Base 컨트롤러 상속 필수.  
> `AdminBaseController` / `AuthBaseController` / `PublicBaseController`  
> 참고: [controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)

### 컨트롤러 목록

| 파일 | Base | 역할 |
|------|------|------|
| `src/Http/Controllers/Api/Auth/AttendanceController.php` | `AuthBaseController` | 출석 실행, 출석 정보 조회 (인증 사용자) |
| `src/Http/Controllers/Api/Admin/AttendanceSettingsController.php` | `AdminBaseController` | 출석부 설정 관리 (관리자) |
| `src/Http/Controllers/Api/Admin/AttendanceStatsController.php` | `AdminBaseController` | 출석 통계 조회 (관리자) |

> **금지**: 유저 페이지에서 관리자 전용 API(`/api/admin/*`) 호출 금지  
> 유저용 출석 API는 `/api/modules/yjsoft-attendance/auth/*` 경로 사용

---

## 3A.2 AttendanceController (인증 사용자용)

```
경로: src/Http/Controllers/Api/Auth/AttendanceController.php
Base: AuthBaseController
```

### 메서드 목록

#### `attend(StoreAttendanceRequest $request): JsonResponse`

- 출석 처리
- `AttendanceService::attend($userId, $greeting)` 호출
- 성공: `AttendanceResource`와 함께 `success()` 반환
- 예외 처리:
  - `AlreadyAttendedException` → `error('yjsoft-attendance::messages.already_attended', 409)`
  - `AttendanceTimeNotAllowedException` → `forbidden('yjsoft-attendance::messages.time_not_allowed')`

#### `status(Request $request): JsonResponse`

- 오늘의 출석 상태 + 이번 달 달력 데이터 반환
- 반환 데이터:
  - `is_attended_today`: 오늘 출석 여부
  - `today_record`: 오늘 출석 기록 (출석한 경우)
  - `monthly_records`: 이번 달 출석/결석 날짜 배열
  - `total_count`: 총 출석 일수
  - `streaks`: 현재 개근 현황 (주간/월간/연간)
  - `today_rank`: 오늘 내 순위
  - `settings`: 프론트엔드 노출 설정 (인삿말 목록, 시간 제한 등)

#### `list(Request $request): JsonResponse`

- 오늘의 출석자 목록 (페이지네이션)
- 반환: `AttendanceListResource` 컬렉션
- 파라미터: `page`, `per_page`

#### `randomGreeting(): JsonResponse`

- 기본 인삿말 목록에서 랜덤 1개 반환
- 프론트엔드 페이지 접속 시마다 호출

#### `publicSettings(): JsonResponse`

- 프론트엔드에 노출할 설정 값 반환 (인삿말 목록, 자동출석 여부 등)
- `AttendanceSettingsService::getFrontendSettings()` 활용

---

## 3A.3 AttendanceSettingsController (관리자용)

```
경로: src/Http/Controllers/Api/Admin/AttendanceSettingsController.php
Base: AdminBaseController
```

#### `index(): JsonResponse`

- 전체 설정 반환
- `AttendanceSettingsService::getAllSettings()`

#### `update(UpdateAttendanceSettingsRequest $request): JsonResponse`

- 설정 저장
- `AttendanceSettingsService::saveSettings($request->validated())`

---

## 3A.4 FormRequest 클래스

> **규칙**: 검증은 FormRequest에서만. Service/Controller 인라인 검증 금지.  
> 참고: [validation.md](https://github.com/gnuboard/g7/blob/main/docs/backend/validation.md)

### StoreAttendanceRequest

```
경로: src/Http/Requests/StoreAttendanceRequest.php
```

| 필드 | 규칙 | 설명 |
|------|------|------|
| `greeting` | `nullable\|string\|max:255` | 인삿말 (비어 있어도 허용) |

> `authorize()` 메서드에서 권한 체크 금지.  
> 권한 체크는 라우트 미들웨어 또는 Service 레이어에서 수행.

### UpdateAttendanceSettingsRequest

```
경로: src/Http/Requests/UpdateAttendanceSettingsRequest.php
```

| 필드 | 규칙 |
|------|------|
| `general.auto_attend` | `sometimes\|boolean` |
| `bonus.base_point` | `sometimes\|integer\|min:0` |
| `bonus.weekly_streak_point` | `sometimes\|integer\|min:0` |
| `bonus.monthly_streak_point` | `sometimes\|integer\|min:0` |
| `bonus.yearly_streak_point` | `sometimes\|integer\|min:0` |
| `bonus.rank1_point` | `sometimes\|integer\|min:0` |
| `bonus.rank2_point` | `sometimes\|integer\|min:0` |
| `bonus.rank3_point` | `sometimes\|integer\|min:0` |
| `time_limit.enabled` | `sometimes\|boolean` |
| `time_limit.start_hour` | `sometimes\|integer\|between:0,23` |
| `time_limit.start_minute` | `sometimes\|integer\|between:0,59` |
| `time_limit.end_hour` | `sometimes\|integer\|between:0,23` |
| `time_limit.end_minute` | `sometimes\|integer\|between:0,59` |
| `random_point.enabled` | `sometimes\|boolean` |
| `random_point.min_point` | `sometimes\|integer\|min:1` |
| `random_point.max_point` | `sometimes\|integer\|min:1` |
| `random_point.probability` | `sometimes\|integer\|between:1,100` |
| `greetings.list` | `sometimes\|array\|min:1` |
| `greetings.list.*` | `string\|max:255` |

---

## 3A.5 API Resources

> **규칙**: `BaseApiResource` 상속 필수  
> 참고: [api-resources.md](https://github.com/gnuboard/g7/blob/main/docs/backend/api-resources.md)

### AttendanceResource

출석 기록 단건 변환. 반환 필드:
- `id`, `user_id`, `attend_date`, `attend_time`, `greeting`
- `base_point`, `bonus_point`, `random_point`, `total_point` (합산)
- `daily_rank`

### AttendanceListResource

출석 목록 행 변환. 반환 필드:
- `rank`, `attend_time`, `greeting`
- `nickname` (유저 닉네임), `profile_image`
- `base_point`, `random_point`, `total_point`
- `current_streak` (개근 일수), `total_count` (총 출석일)

---

## 3A.6 API 라우트 정의

```
경로: src/routes/api.php
```

> **규칙**: `ModuleRouteServiceProvider`가 URL prefix(`api/modules/yjsoft-attendance`)와 name prefix(`api.modules.yjsoft-attendance.`)를 **자동** 적용한다.  
> 라우트 파일에서는 **모듈 내부 이름만** 정의한다. prefix를 중복 작성하면 안 된다.  
> 참고: [module-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md)

### 인증 사용자 라우트

URL prefix 자동 적용: `/api/modules/yjsoft-attendance`  
Name prefix 자동 적용: `api.modules.yjsoft-attendance.`  
미들웨어: `['auth:sanctum']`

> **출석 권한 체크**: `/auth/attend` 라우트는 `permission:yjsoft-attendance.attend` 미들웨어를 추가로 적용한다.  
> `yjsoft-attendance.attend` permission이 부여된 Role(기본: `user`)을 가진 사용자만 출석할 수 있다.  
> permission이 없는 사용자는 미들웨어 레벨에서 **403**이 반환되며, 서비스 레이어는 호출되지 않는다.

| Method | URI (파일 내 정의) | 컨트롤러 메서드 | 파일 내 name() | 최종 라우트명 |
|--------|------------------|--------------|--------------|------------|
| POST | `/auth/attend` | `AttendanceController@attend` | `auth.attend` | `api.modules.yjsoft-attendance.auth.attend` |
| GET | `/auth/status` | `AttendanceController@status` | `auth.status` | `api.modules.yjsoft-attendance.auth.status` |
| GET | `/auth/list` | `AttendanceController@list` | `auth.list` | `api.modules.yjsoft-attendance.auth.list` |
| GET | `/auth/random-greeting` | `AttendanceController@randomGreeting` | `auth.random-greeting` | `api.modules.yjsoft-attendance.auth.random-greeting` |
| GET | `/auth/settings` | `AttendanceController@publicSettings` | `auth.settings` | `api.modules.yjsoft-attendance.auth.settings` |

### 관리자 라우트

URL prefix 자동 적용: `/api/modules/yjsoft-attendance`  
Name prefix 자동 적용: `api.modules.yjsoft-attendance.`  
미들웨어: `['auth:sanctum', 'admin']`

| Method | URI (파일 내 정의) | 컨트롤러 메서드 | 파일 내 name() | 최종 라우트명 |
|--------|------------------|--------------|--------------|------------|
| GET | `/admin/settings` | `AttendanceSettingsController@index` | `admin.settings.index` | `api.modules.yjsoft-attendance.admin.settings.index` |
| PUT | `/admin/settings` | `AttendanceSettingsController@update` | `admin.settings.update` | `api.modules.yjsoft-attendance.admin.settings.update` |
| GET | `/admin/stats` | `AttendanceStatsController@index` | `admin.stats.index` | `api.modules.yjsoft-attendance.admin.stats.index` |

> **금지**: `FormRequest::authorize()` 메서드에서 권한 체크 금지.  
> 권한 체크는 `permission` 미들웨어를 라우트에 체인하여 처리.

### 라우트 파일 예시 구조

```php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Attendance Module API Routes
|--------------------------------------------------------------------------
|
| 주의: ModuleRouteServiceProvider가 자동으로 prefix를 적용합니다.
| - URL prefix: 'api/modules/yjsoft-attendance'
| - Name prefix: 'api.modules.yjsoft-attendance.'
|
*/

// 인증 사용자 전용
Route::prefix('auth')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/attend', [AttendanceController::class, 'attend'])
            ->middleware('permission:yjsoft-attendance.attend')  // 출석 권한 체크
            ->name('auth.attend');          // 최종: api.modules.yjsoft-attendance.auth.attend

        Route::get('/status', [AttendanceController::class, 'status'])
            ->name('auth.status');

        Route::get('/list', [AttendanceController::class, 'list'])
            ->name('auth.list');

        Route::get('/random-greeting', [AttendanceController::class, 'randomGreeting'])
            ->name('auth.random-greeting');

        Route::get('/settings', [AttendanceController::class, 'publicSettings'])
            ->name('auth.settings');
    });

// 관리자 전용
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('/settings', [AttendanceSettingsController::class, 'index'])
            ->middleware('permission:yjsoft-attendance.admin.settings')
            ->name('admin.settings.index');  // 최종: api.modules.yjsoft-attendance.admin.settings.index

        Route::put('/settings', [AttendanceSettingsController::class, 'update'])
            ->middleware('permission:yjsoft-attendance.admin.settings')
            ->name('admin.settings.update');

        Route::get('/stats', [AttendanceStatsController::class, 'index'])
            ->middleware('permission:yjsoft-attendance.admin.view')
            ->name('admin.stats.index');
    });
```

> **금지**: `->name('api.modules.yjsoft-attendance.auth.attend')` 처럼 prefix를 직접 작성하면 이중 적용됨.  
> **금지**: `->name('yjsoft-attendance.auth.attend')` 처럼 모듈 식별자를 직접 포함하면 이중 적용됨.  
> **올바른 방법**: `->name('auth.attend')` — ModuleRouteServiceProvider가 자동으로 `api.modules.yjsoft-attendance.`를 앞에 붙임.  
> 참고: [module-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md)

---

## 3A.7 자동출석 Hook Listener

```
경로: src/Listeners/AutoAttendanceListener.php
```

- 코어 로그인 이벤트(훅)를 수신
- 설정 `general.auto_attend`가 `true`인 경우에만 동작
- `AttendanceService::attend($userId, '')` 호출
- 이미 출석했거나 오류 발생 시 조용히 실패 (예외를 삼키고 로그만 기록)
- 자동출석 시 인삿말은 빈 문자열로 처리

`module.php`의 `getHookListeners()`에 등록.

---

## 3A.8 단계 완료 체크리스트

- [x] `AttendanceController` (Auth) 구현
  - [x] `attend()` — 출석 처리 + 예외 처리
  - [x] `status()` — 오늘 출석 상태 + 달력 데이터
  - [x] `list()` — 오늘 출석자 목록 (페이지네이션)
  - [x] `randomGreeting()` — 랜덤 인삿말
  - [x] `publicSettings()` — 프론트 공개 설정
- [x] `AttendanceSettingsController` (Admin) 구현
- [x] `AttendanceStatsController` (Admin) 구현
- [x] `StoreAttendanceRequest` FormRequest 작성
- [x] `UpdateAttendanceSettingsRequest` FormRequest 작성
- [x] `AttendanceResource` 작성 (BaseApiResource 상속)
- [x] `AttendanceListResource` 작성
- [x] `src/routes/api.php` 작성
  - [x] 모든 라우트에 `name()` 지정 확인
  - [x] 관리자 라우트에 `permission` 미들웨어 체인 확인
- [x] `AutoAttendanceListener` 구현
- [x] `module.php`에 Hook Listener 등록
