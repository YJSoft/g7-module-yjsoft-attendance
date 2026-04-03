# 4단계: 프론트엔드 — 관리자 설정 페이지

> **참고 문서**
> - [layout-json.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json.md)
> - [components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components.md)
> - [component-props.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/component-props.md)
> - [data-binding.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding.md)
> - [data-sources.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-sources.md)
> - [actions.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions.md)
> - [module-layouts.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md)
> - [module-settings.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md)
> - [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 4A.1 레이아웃 파일 목록

| 파일 | DB 등록명 | 설명 |
|------|----------|------|
| `resources/layouts/admin/admin_attendance_settings.json` | `yjsoft-attendance.admin_attendance_settings` | 출석부 설정 페이지 |
| `resources/layouts/admin/admin_attendance_skin.json` | `yjsoft-attendance.admin_attendance_skin` | 스킨 관리 페이지 |

> **규칙**: `layouts/admin/` 하위에 배치해야 Admin 템플릿에 등록됨.  
> 참고: [module-layouts.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md)

---

## 4A.2 admin_attendance_settings.json 기본 구조

```json
{
  "layout_name": "admin_attendance_settings",
  "version": "1.0.0",
  "extends": "_admin_base",
  "meta": {
    "title": "$t:yjsoft-attendance.settings.title",
    "auth_required": true
  },
  "data_sources": [ ... ],
  "state": {
    "isSaving": false,
    "form": {}
  },
  "slots": {
    "content": [ ... ]
  }
}
```

---

## 4A.3 data_sources 정의

```json
"data_sources": [
  {
    "id": "settings",
    "type": "api",
    "method": "GET",
    "endpoint": "/api/modules/yjsoft-attendance/admin/settings",
    "auto_fetch": true,
    "auth_required": true,
    "initLocal": "form",
    "refetchOnMount": true
  }
]
```

설정을 불러오면 `_local.form`에 자동 바인딩된다.  
저장 버튼은 `_local.form` 전체를 PUT으로 전송한다.

---

## 4A.4 설정 섹션 구성

### 섹션 1: 기본 설정 (general)

| 설정 | UI 컴포넌트 | 필드 키 |
|------|------------|--------|
| 자동출석 사용 여부 | Toggle / Switch | `form.general.auto_attend` |

자동출석 설명: 로그인 또는 자동 로그인 시 자동으로 출석을 처리. 인삿말은 빈 문자열로 처리됨.

### 섹션 2: 포인트 및 보너스 설정 (bonus)

| 설정 | UI 컴포넌트 | 필드 키 |
|------|------------|--------|
| 기본 출석 포인트 | NumberInput | `form.bonus.base_point` |
| 주간 개근 보너스 | NumberInput | `form.bonus.weekly_streak_point` |
| 월간 개근 보너스 | NumberInput | `form.bonus.monthly_streak_point` |
| 연간 개근 보너스 | NumberInput | `form.bonus.yearly_streak_point` |
| 1위 보너스 | NumberInput | `form.bonus.rank1_point` |
| 2위 보너스 | NumberInput | `form.bonus.rank2_point` |
| 3위 보너스 | NumberInput | `form.bonus.rank3_point` |

### 섹션 3: 출석 가능 시간 설정 (time_limit)

| 설정 | UI | 필드 키 |
|------|-----|--------|
| 시간 제한 사용 여부 | Toggle | `form.time_limit.enabled` |
| 시작 시간 (시) | NumberInput(0~23) | `form.time_limit.start_hour` |
| 시작 시간 (분) | NumberInput(0~59) | `form.time_limit.start_minute` |
| 종료 시간 (시) | NumberInput(0~23) | `form.time_limit.end_hour` |
| 종료 시간 (분) | NumberInput(0~59) | `form.time_limit.end_minute` |

시작/종료 입력 필드는 `form.time_limit.enabled`가 false이면 비활성화(`disabled` prop).

기본값: 전체 허용 (제한 없음, `enabled: false`).

### 섹션 4: 랜덤 포인트 설정 (random_point)

| 설정 | UI | 필드 키 |
|------|-----|--------|
| 랜덤 포인트 사용 여부 | Toggle | `form.random_point.enabled` |
| 최솟값 | NumberInput | `form.random_point.min_point` |
| 최댓값 | NumberInput | `form.random_point.max_point` |
| 지급 확률(%) | NumberInput(1~100) | `form.random_point.probability` |

`enabled`가 false이면 최솟값/최댓값/확률 입력 비활성화.

### 섹션 5: 기본 인삿말 설정 (greetings)

| 설정 | UI | 필드 키 |
|------|-----|--------|
| 인삿말 목록 | 동적 목록 (추가/삭제) | `form.greetings.list` |

- 각 인삿말은 텍스트 입력 + 삭제 버튼으로 구성
- 하단에 "추가" 버튼으로 새 항목 추가
- 기본값: `["안녕하세요", "출석합니다", "출첵!", "오늘도 출첵!", "좋은하루 되세요"]`
- 최소 1개 이상 유지

인삿말 목록 iteration:
```json
{
  "id": "greetings_list",
  "name": "Div",
  "type": "basic",
  "iteration": {
    "data": "{{_local.form?.greetings?.list ?? []}}",
    "item_var": "greeting_item",
    "index_var": "greeting_index"
  },
  "children": [
    {
      "id": "greeting_row",
      "name": "Div",
      "type": "basic",
      "props": { "className": "flex gap-2 mb-2" },
      "children": [
        {
          "id": "greeting_input",
          "name": "Input",
          "type": "basic",
          "props": {
            "value": "{{greeting_item ?? ''}}",
            "maxLength": 255
          },
          "actions": [
            {
              "event": "change",
              "handler": "setState",
              "params": {
                "target": "local",
                "key": "form.greetings.list",
                "value": "{{_local.form?.greetings?.list?.map((item, idx) => idx === greeting_index ? $event.target.value : item) ?? []}}"
              }
            }
          ]
        },
        {
          "id": "greeting_delete_btn",
          "name": "Button",
          "type": "basic",
          "props": {
            "text": "$t:yjsoft-attendance.settings.delete",
            "type": "button"
          },
          "actions": [
            {
              "event": "click",
              "handler": "setState",
              "params": {
                "target": "local",
                "key": "form.greetings.list",
                "value": "{{_local.form?.greetings?.list?.filter((_, idx) => idx !== greeting_index) ?? []}}"
              }
            }
          ]
        }
      ]
    }
  ]
}
```

> **규칙**: iteration에서 `item`, `index` 키 사용 금지. `item_var`, `index_var` 필수.  
> 참고: [AGENTS.md CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

### 섹션 6: 출석 권한 설정 (access_control)

| 설정 | UI | 필드 키 |
|------|-----|--------|
| 모드 | Radio 또는 Select | `form.access_control.mode` |
| 역할(Role) 목록 | 태그 입력 또는 체크박스 | `form.access_control.roles` |

- 모드: `whitelist` (허용 역할 지정) / `blacklist` (금지 역할 지정)
- 기본값: `whitelist` 모드, `["user"]` 역할 허용
- 역할 목록은 코어 API에서 조회하여 선택지로 제공

```json
{
  "id": "roles_data_source",
  "type": "api",
  "method": "GET",
  "endpoint": "/api/admin/roles",
  "auto_fetch": true,
  "auth_required": true
}
```

역할 목록은 Select 또는 다중 체크박스로 표시.  
`options` prop에 fallback 필수: `options={{roles_data?.data ?? []}}`

---

## 4A.5 저장 버튼 액션

```json
{
  "id": "save_btn",
  "name": "Button",
  "type": "basic",
  "props": {
    "text": "$t:yjsoft-attendance.settings.save",
    "type": "button",
    "disabled": "{{_local.isSaving}}"
  },
  "actions": [
    {
      "event": "click",
      "handler": "apiCall",
      "params": {
        "method": "PUT",
        "endpoint": "/api/modules/yjsoft-attendance/admin/settings",
        "body": "{{_local.form ?? {}}}",
        "onSuccess": [
          {
            "handler": "setState",
            "params": {
              "target": "local",
              "key": "isSaving",
              "value": false
            }
          },
          {
            "handler": "showToast",
            "params": {
              "message": "$t:yjsoft-attendance.settings.save_success",
              "type": "success"
            }
          }
        ]
      }
    }
  ]
}
```

---

## 4A.6 admin 템플릿 라우트 정의

```json
{
  "routes": [
    {
      "path": "/admin/yjsoft-attendance/settings",
      "layout_name": "yjsoft-attendance.admin_attendance_settings",
      "permissions": ["yjsoft-attendance.admin.settings"]
    },
    {
      "path": "/admin/yjsoft-attendance/skin",
      "layout_name": "yjsoft-attendance.admin_attendance_skin",
      "permissions": ["yjsoft-attendance.admin.view"]
    }
  ]
}
```

파일 위치: `resources/routes/admin.json`

---

## 4A.7 스킨 관리 페이지 (admin_attendance_skin.json)

스킨 관리 페이지는 출석부 유저 페이지의 레이아웃을 커스터마이징할 수 있는 기능.

기본 구성:
- 현재 활성 스킨 표시
- 스킨 목록 (기본 스킨만 제공)
- 스킨 미리보기 버튼

1.0.0 버전에서는 기본 스킨만 제공하고 커스텀 스킨 업로드 기능은 향후 버전으로 미룬다.

---

## 4A.8 단계 완료 체크리스트

- [ ] `resources/layouts/admin/admin_attendance_settings.json` 작성
  - [ ] `data_sources` 정의
  - [ ] 기본 설정 섹션 (자동출석)
  - [ ] 포인트/보너스 설정 섹션
  - [ ] 출석 가능 시간 설정 섹션
  - [ ] 랜덤 포인트 설정 섹션
  - [ ] 기본 인삿말 목록 편집 UI
  - [ ] 출석 권한 설정 섹션 (모드 + 역할 목록)
  - [ ] 저장 버튼 액션
- [ ] `resources/layouts/admin/admin_attendance_skin.json` 작성
- [ ] `resources/routes/admin.json` 작성
- [ ] 모든 데이터 바인딩 fallback(`??`) 적용 확인
- [ ] `options` prop fallback 적용 확인 (`options ?? []`)
- [ ] `Button` 컴포넌트에 `type="button"` 명시 확인 (Form 내부 submit 방지)
- [ ] `item`, `index` 대신 `item_var`, `index_var` 사용 확인
- [ ] 다크 모드 클래스 적용 확인
