# 4단계: 프론트엔드 — 유저 출석부 페이지

> **참고 문서**
> - [layout-json.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json.md)
> - [components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components.md)
> - [component-props.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/component-props.md)
> - [data-binding.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding.md)
> - [data-sources.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-sources.md)
> - [actions.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions.md)
> - [actions-handlers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-handlers.md)
> - [dark-mode.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/dark-mode.md)
> - [module-layouts.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md)
> - [AGENTS.md - CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 4U.1 레이아웃 파일 위치 및 네이밍

```
resources/layouts/user/user_attendance_index.json
```

DB 등록명: `yjsoft-attendance.user_attendance_index`  
라우트: `/attendance` (user 템플릿 라우트에서 정의)

> **규칙**: `layouts/user/` 하위에 배치해야 User 템플릿에 등록됨.  
> 참고: [module-layouts.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md)

---

## 4U.2 레이아웃 JSON 기본 구조

```json
{
  "layout_name": "user_attendance_index",
  "version": "1.0.0",
  "extends": "_user_base",
  "meta": {
    "title": "$t:yjsoft-attendance.attendance.title",
    "auth_required": true
  },
  "data_sources": [ ... ],
  "state": { ... },
  "slots": {
    "content": [ ... ]
  }
}
```

> **규칙**: `layout_name`은 접두사 없이 작성. 시스템이 자동으로 `yjsoft-attendance.user_attendance_index`로 변환.

---

## 4U.3 data_sources 정의

| ID | endpoint | 설명 | 자동 fetch |
|----|----------|------|-----------|
| `attendance_status` | `GET /api/modules/yjsoft-attendance/auth/status` | 출석 상태 + 달력 + 개근 현황 | true |
| `attendance_list` | `GET /api/modules/yjsoft-attendance/auth/list` | 오늘 출석자 목록 | true |
| `attendance_settings` | `GET /api/modules/yjsoft-attendance/auth/settings` | 공개 설정 (인삿말 목록 등) | true |

```json
"data_sources": [
  {
    "id": "attendance_status",
    "type": "api",
    "method": "GET",
    "endpoint": "/api/modules/yjsoft-attendance/auth/status",
    "auto_fetch": true,
    "auth_required": true,
    "initLocal": "statusData"
  },
  {
    "id": "attendance_list",
    "type": "api",
    "method": "GET",
    "endpoint": "/api/modules/yjsoft-attendance/auth/list",
    "auto_fetch": true,
    "auth_required": true,
    "params": {
      "page": "{{route.page ?? 1}}",
      "per_page": 20
    }
  },
  {
    "id": "attendance_settings",
    "type": "api",
    "method": "GET",
    "endpoint": "/api/modules/yjsoft-attendance/auth/settings",
    "auto_fetch": true,
    "auth_required": true
  }
]
```

> **금지**: 유저 페이지에서 `/api/admin/*` 또는 `/api/modules/yjsoft-attendance/admin/*` 호출 금지.

---

## 4U.4 state 정의

```json
"state": {
  "greeting": "",
  "isSubmitting": false,
  "showStreakDetail": false,
  "showRankDetail": false,
  "showAccessDetail": false,
  "currentMonth": "{{route.month ?? ''}}",
  "currentPage": 1,
  "currentTime": ""
}
```

---

## 4U.5 페이지 상단 — 현재 날짜 및 시각

참고 이미지: 상단 좌측에 `2026-04-01 17:30:22` 형태 실시간 표시

구현 방식:
- `currentTime` 상태값을 매 초마다 갱신하는 JavaScript 타이머 사용
- 모듈 에셋(`resources/js/index.ts`)에서 G7Core 커스텀 핸들러로 구현:
  - 페이지 마운트 시(`onMount`) 타이머 시작
  - `G7Core.state.setLocal('currentTime', formattedDateTime)` 호출
  - 언마운트 시 타이머 정리

```json
{
  "id": "clock_display",
  "name": "Span",
  "type": "basic",
  "props": {
    "text": "{{_local.currentTime ?? ''}}",
    "className": "font-mono text-sm bg-blue-600 text-white px-3 py-1 rounded dark:bg-blue-700"
  }
}
```

---

## 4U.6 상태 정보 헤더 영역

이미지 참고: 출석점수, 개근점수, 랭킹점수, 출석권한, 출석시간, 진행상태, 출석여부, 개근분류 등 격자 표시

### 기본 표시 항목

| 항목 | 데이터 소스 |
|------|-----------|
| 출석점수 | `{{attendance_status?.data?.total_count ?? 0}}` |
| 출석시간 | `{{attendance_status?.data?.is_attended_today ? '하루 통일' : '-'}}` (뱃지) |
| 진행상태 | 출석 가능 여부 (뱃지: 출석가능 / 마감 등) |
| 출석여부 | 오늘 출석 여부 (뱃지: 출첵완료 / 미출석) |
| 출석권한 | 현재 유저의 출석 권한 상태 (뱃지: 로그인 사용자 등) |

### 자세히 보기 드롭다운

"자세히 보기" 버튼 클릭 시 드롭다운으로 상세 정보 표시:

- 개근점수 자세히 보기: 주간/월간/연간 개근 현황, 현재 연속 출석일, 달성 여부
- 랭킹점수 자세히 보기: 오늘 순위, 순위별 보너스 포인트 정보
- 개근분류 자세히 보기: 개근 타입 설명

드롭다운 구현:
- `showStreakDetail`, `showRankDetail`, `showAccessDetail` 상태값으로 조건부 렌더링
- 버튼 클릭 시 `setState` 핸들러로 상태 토글

```json
{
  "id": "streak_detail_btn",
  "name": "Button",
  "type": "basic",
  "props": {
    "text": "$t:yjsoft-attendance.attendance.detail_btn",
    "type": "button",
    "className": "text-sm text-blue-600 underline dark:text-blue-400"
  },
  "actions": [
    {
      "event": "click",
      "handler": "setState",
      "params": {
        "target": "local",
        "key": "showStreakDetail",
        "value": "{{!_local.showStreakDetail}}"
      }
    }
  ]
}
```

---

## 4U.7 달력 영역

이미지 참고: 날짜별 출석(●)/결석(○)/미출석 구분, 이전달/다음달 이동

### 달력 데이터 구조

백엔드 `status` API가 반환하는 `monthly_records`:
```json
{
  "year": 2026,
  "month": 4,
  "days": [
    { "day": 1, "status": "attended" },
    { "day": 2, "status": "absent" },
    { "day": 3, "status": "future" }
  ]
}
```

### 달력 컴포넌트 구성

- 상단: 이전달 / 이번달 / 다음달 버튼 (navigate 핸들러로 URL 파라미터 변경)
- 날짜 행: 1~말일 반복 렌더링 (iteration 사용)
- 각 날짜에 상태에 따른 스타일 적용
  - `attended`: 파란색 숫자 + 아래 점(●)
  - `absent`: 붉은색 숫자 (빨간색은 출석 가능했으나 결석한 날)
  - `future`: 회색 숫자 + 미출석 표시
- 범례: ◎ 결석 / ● 출석 / ○ 미출석

> **규칙**: iteration 내 `item_var`, `index_var` 사용 (`item`, `index` 금지)  
> 참고: [AGENTS.md CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

```json
{
  "id": "calendar_days",
  "name": "Div",
  "type": "basic",
  "props": { "className": "flex flex-wrap gap-1" },
  "iteration": {
    "data": "{{attendance_status?.data?.monthly_records?.days ?? []}}",
    "item_var": "day_item",
    "index_var": "day_index"
  },
  "children": [
    {
      "id": "calendar_day_cell",
      "name": "Div",
      "type": "basic",
      "props": {
        "className": "w-8 h-8 flex items-center justify-center text-sm"
      },
      "children": [ ... ]
    }
  ]
}
```

---

## 4U.8 출석 처리 영역

### 출석 미완료 상태

- 인삿말 입력 필드 (기본값: 랜덤 인삿말)
  - 페이지 로드 시 `attendance_settings?.data?.greetings?.list`에서 랜덤 선택하여 `greeting` 상태 초기화
  - 페이지 접속마다 랜덤 기본값이 입력되어 있음
- 출석하기 버튼 (apiCall 핸들러)

```json
{
  "id": "attend_form",
  "name": "Form",
  "type": "basic",
  "if": "{{!attendance_status?.data?.is_attended_today}}",
  "children": [
    {
      "id": "greeting_input",
      "name": "Input",
      "type": "basic",
      "props": {
        "value": "{{_local.greeting ?? ''}}",
        "placeholder": "$t:yjsoft-attendance.attendance.greeting_placeholder",
        "maxLength": 255
      },
      "actions": [
        {
          "event": "change",
          "handler": "setState",
          "params": {
            "target": "local",
            "key": "greeting",
            "value": "$event.target.value"
          }
        }
      ]
    },
    {
      "id": "attend_btn",
      "name": "Button",
      "type": "basic",
      "props": {
        "text": "$t:yjsoft-attendance.attendance.attend_btn",
        "type": "button",
        "disabled": "{{_local.isSubmitting}}"
      },
      "actions": [
        {
          "event": "click",
          "handler": "apiCall",
          "params": {
            "method": "POST",
            "endpoint": "/api/modules/yjsoft-attendance/auth/attend",
            "body": {
              "greeting": "{{_local.greeting ?? ''}}"
            },
            "onSuccess": [
              {
                "handler": "refetch",
                "params": { "target": "attendance_status" }
              },
              {
                "handler": "refetch",
                "params": { "target": "attendance_list" }
              }
            ]
          }
        }
      ]
    }
  ]
}
```

### 출석 완료 상태

- 완료 메시지 표시: "출석이 완료되었습니다. 출석은 하루 1회만 참여하실 수 있습니다."
- `if: "{{attendance_status?.data?.is_attended_today}}"` 조건 적용

---

## 4U.9 출석자 목록 테이블

이미지 참고: 순위 / 출석시각 / 인삿말 / 별명 / 포인트 / 랜덤 포인트 / 개근 / 총 출석일

| 컬럼 | 데이터 바인딩 |
|------|-------------|
| 순위 | `{{item_var.daily_rank ?? '-'}}` |
| 출석시각 | `{{item_var.attend_time ?? ''}}` |
| 인삿말 | `{{item_var.greeting ?? ''}}` |
| 별명 | `{{item_var.nickname ?? ''}}` (프로필 이미지 포함) |
| 포인트 | `{{item_var.base_point ?? 0}}` |
| 랜덤 포인트 | `{{item_var.random_point ?? 0}}` (0이면 '꽝' 표시) |
| 개근 | `{{item_var.current_streak ?? 0}}` 일째 |
| 총 출석일 | `{{item_var.total_count ?? 0}}` 일 |

데이터 소스: `attendance_list`

페이지네이션: `attendance_list?.data?.meta` 사용

---

## 4U.10 user 템플릿 라우트 정의

```json
{
  "routes": [
    {
      "path": "/attendance",
      "layout_name": "yjsoft-attendance.user_attendance_index",
      "auth_required": true
    }
  ]
}
```

파일 위치: `resources/routes/user.json`

---

## 4U.11 fallback 및 바인딩 규칙

> **규칙**: 모든 데이터 바인딩에 fallback 필수.  
> `{{value}}` → `{{value ?? ''}}` 또는 `{{value ?? 0}}`  
> 참고: [AGENTS.md CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

> **규칙**: onSuccess 핸들러에서 `{{response.xxx}}` 사용 (`{{$response.xxx}}` 금지)

---

## 4U.12 단계 완료 체크리스트

- [ ] `resources/layouts/user/user_attendance_index.json` 작성
  - [ ] `data_sources` 정의 (유저용 Auth API만 사용)
  - [ ] 상단 현재 시각 표시 (JS 핸들러 연동)
  - [ ] 상태 헤더 영역 (출석점수, 진행상태, 출석여부, 출석권한)
  - [ ] 자세히 보기 드롭다운 (개근점수, 랭킹점수, 개근분류)
  - [ ] 달력 영역 (출석/결석/미출석 시각화, 이전/다음달 이동)
  - [ ] 출석 처리 영역 (인삿말 입력 + 출석하기 버튼)
  - [ ] 출석 완료 메시지 영역
  - [ ] 출석자 목록 테이블 (페이지네이션 포함)
- [ ] `resources/routes/user.json` 작성
- [ ] `resources/js/index.ts` — 현재 시각 타이머 핸들러 구현
- [ ] `resources/js/index.ts` — 랜덤 인삿말 초기화 로직
- [ ] `package.json`, `vite.config.ts`, `tsconfig.json` 작성
- [ ] 다크 모드 클래스 적용 확인 (`dark:` variant)
- [ ] 모든 데이터 바인딩 fallback(`??`) 적용 확인
- [ ] `item`, `index` 대신 `item_var`, `index_var` 사용 확인
- [ ] HTML 태그 직접 사용하지 않았는지 확인 (`Div`, `Button`, `Input` 등 컴포넌트 사용)
