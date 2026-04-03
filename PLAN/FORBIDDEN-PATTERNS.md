# 금지 패턴 레퍼런스 (FORBIDDEN PATTERNS)

> 이 문서는 개발 시 절대로 사용해서는 안 되는 패턴을 정리한 레퍼런스입니다.  
> 구현 전후로 반드시 확인하여 위반이 없도록 합니다.  
> **출처**: [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md) 및 하위 문서

---

## FP-1. 유저 페이지에서 관리자 API 호출 금지

> 출처: 문제 요구사항 [주의사항 2] / [controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 유저 레이아웃에서 `/api/admin/*` 호출 | 유저 레이아웃에서 `/api/modules/yjsoft-attendance/auth/*` 호출 |
| 유저 레이아웃에서 `/api/modules/yjsoft-attendance/admin/*` 호출 | Auth 컨트롤러의 `publicSettings()` 등 전용 엔드포인트 사용 |

**적용 범위**: `resources/layouts/user/` 하위 모든 레이아웃 JSON

---

## FP-2. API/핸들러 호출 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| `G7Core.actions.execute` | `G7Core.dispatch` |
| `G7Core.api.call` | `G7Core.dispatch({ handler: 'apiCall', ... })` |
| `handler: "api"` | `handler: "apiCall"` |
| `handler: "nav"` | `handler: "navigate"` |
| `handler: "setLocalState"` | `handler: "setState"` + `target: "local"` |
| `navigate` + `replace: true` (URL만 변경) | `handler: "replaceUrl"` |
| `{{handler()}}` (표현식에서 직접 호출) | `actions: [{ handler: "xxx" }]` |

---

## FP-3. 데이터 바인딩 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md) / [data-binding.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| `{{value}}` (fallback 없음) | `{{value ?? ''}}` 또는 `{{value ?? 0}}` |
| `{{products.data}}` | `{{products?.data?.data}}` (배열 경로 확인) |
| `{{error.data}}` | `{{error.errors}}` |
| `{{error.data?.errors ?? {}}}` | `{{error.errors}}` (`{}}}` 파서 모호성 회피) |
| `$value` (이벤트 값) | `$event.target.value` |
| `{{props.xxx}}` (Partial 내) | data_sources ID 직접 참조 |
| `{{$response.xxx}}` (onSuccess 내) | `{{response.xxx}}` ($ 접두사 없음) |
| `options={{options}}` | `options={{options ?? []}}` |

---

## FP-4. 반복 렌더링(iteration) 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| `"item"` (iteration 변수명) | `"item_var"` |
| `"index"` (iteration 인덱스명) | `"index_var"` |
| iteration 내 if 순서 무시 | `if`가 `iteration`보다 먼저 평가됨에 유의 |

---

## FP-5. 컴포넌트 사용 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md) / [components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| HTML 태그 직접 사용 (`<div>`, `<button>`, `<input>`, `<span>`, `<a>`) | 컴포넌트 사용 (`Div`, `Button`, `Input`, `Span`, `A`) |
| `Icon className="w-4 h-4"` | `Icon size="sm"` 또는 `className="text-sm"` |
| `Select valueKey/labelKey` | computed로 `{ value, label }` 변환 후 사용 |
| `Form` 내 `Button`에 `type` 없음 | `type="button"` 명시 (submit 방지) |
| 규정 문서에 없는 컴포넌트/props 사용 | `component-props.md` 확인 후 정의된 것만 사용 |
| `type: "conditional"` 조건부 렌더링 | `if` 속성 사용 |

---

## FP-6. 상태 관리 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 스냅샷 기반 setState | 함수형 업데이트 또는 `stateRef.current` |
| closeModal 후 setState | setState 후 closeModal (순서 중요) |
| await 후 캡처된 상태 사용 | await 후 `G7Core.state.getLocal()` 재조회 |
| setState params 키에 `{{}}` 사용 | 키는 정적 경로만, 배열 조작은 `.map()`/`.filter()` |

---

## FP-7. 백엔드 컨트롤러 패턴

> 출처: [controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 컨트롤러에 검증 로직 작성 | FormRequest에서 검증 |
| `FormRequest::authorize()`에서 권한 체크 | 라우트 `permission` 미들웨어에서 권한 체크 |
| Repository를 컨트롤러에 직접 주입 | Service를 주입하고 Service가 Repository 사용 |
| 구체 클래스(Repository 구현체) 직접 주입 | RepositoryInterface 주입 |
| `adminSuccess()`, `userSuccess()` 등 래퍼 메서드 | `success()`, `error()` 등 공통 메서드 사용 |
| `$e->getMessage()` 사용자에게 직접 노출 | 로깅 목적으로만 사용, 사용자에게는 다국어 키 |

---

## FP-8. 라우트 패턴

> 출처: [routing.md](https://github.com/gnuboard/g7/blob/main/docs/backend/routing.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 라우트에 `name()` 없음 | 모든 라우트에 `name()` 필수 |
| 인증 필요 미들웨어 전역 등록 | 라우트 그룹에 지정 |
| 관리자 라우트에 `admin` 미들웨어 없음 | `['auth:sanctum', 'admin']` 미들웨어 그룹 사용 |

---

## FP-9. 다국어/예외 패턴

> 출처: [exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 예외 메시지 하드코딩 (`throw new Exception('이미 출석했습니다.')`) | `__('yjsoft-attendance::messages.already_attended')` |
| 응답 메시지 하드코딩 | `__()` 함수로 다국어 키 사용 |

---

## FP-10. 모듈 구조 패턴

> 출처: [module-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 루트 `composer.json`에 모듈 패키지 추가 | 모듈의 자체 `composer.json`에만 정의 |
| `getName()`, `getVersion()`, `getDescription()` 하드코딩 오버라이드 | `module.json`에서 자동 파싱 (오버라이드 불필요) |
| `layouts/` 루트에 직접 레이아웃 파일 배치 | `layouts/admin/` 또는 `layouts/user/` 하위에 배치 |
| 레이아웃 content에서 `extends` 필드 누락 | `layoutData` 전체 저장 (extends 포함) |

---

## FP-11. 레이아웃 JSON 구현 규칙

> 출처: [AGENTS.md — 레이아웃 JSON 구현 규칙](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 지원 여부 미확인 후 기능 사용 | 규정 문서에서 지원 여부 확인 후 사용 |
| 추측/가정으로 구현 | 불확실하면 기존 레이아웃 패턴 참조 |
| 규정 문서에 없는 기능 사용 | 절대 사용 금지 |
| `Partial`에서 `computed`, `data_sources`, `modals`, `state` 사용 | Partial은 컴포넌트 치환만 수행 |

---

## FP-12. globalHeaders 패턴

> 출처: [AGENTS.md — CRITICAL RULES](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| `"globalHeaders": { "X-Key": "value" }` (객체 형태) | `"globalHeaders": [{ "pattern": "*", "headers": {...} }]` (배열 형태) |
| pattern 없이 헤더 정의 | pattern 필수 (`*`, `/api/shop/*` 등) |

---

## FP-14. 다국어(i18n) 패턴

> 출처: [module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| 백엔드 lang 파일을 `lang/ko/` (모듈 루트)에 배치 | `src/lang/ko/` 하위에 배치 (TranslationServiceProvider 자동 로드 경로) |
| `__('yjsoft-attendance.messages.key')` (점 . 사용) | `__('yjsoft-attendance::messages.key')` (더블 콜론 :: 필수) |
| 프론트엔드 JSON에 `{ "yjsoft-attendance": { ... } }` 형태로 작성 | moduleIdentifier 없이 순수 키만 작성 (시스템이 자동 병합) |
| 프론트엔드 JSON 키를 플랫 dot-notation으로 작성 (`"attendance.title": "..."`) | 중첩 객체 구조로 작성 (`{ "attendance": { "title": "..." } }`) |
| 500줄 초과 JSON을 단일 파일로 유지 | `$partial` 디렉티브로 도메인별 분리 |

> 출처: [AGENTS.md — 테스트 프로토콜](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

| ❌ 금지 | ✅ 올바른 방법 |
|--------|-------------|
| "인프라 부족"을 이유로 레이아웃 테스트 건너뜀 | `createLayoutTest()` 유틸리티 사용 (브라우저 불필요) |
| 모듈 테스트에서 루트 vitest.config.ts 포함 | 독립 `vitest.config.ts` 사용 |
| 기능 구현 후 테스트 미작성 | 기능 구현 = 테스트 코드 작성 필수 |

---

## 개근 로직 특수 주의사항

> 요구사항에서 명시된 핵심 비즈니스 규칙

| ❌ 잘못된 구현 | ✅ 올바른 구현 |
|--------------|-------------|
| 3월 15일~4월 14일 연속 출석을 3월 또는 4월 개근으로 처리 | 3월 1일~31일 전일 출석이어야 3월 개근 달성 |
| "연속 출석 일수"로 개근 판정 | **달력 기준 기간 전체 출석 여부**로 판정 |
| 기간 시작/종료를 동적으로 계산하지 않음 | `StreakType::getPeriod()` 메서드로 정확한 기간 계산 |
