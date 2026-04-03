# 00. g7 AGENTS.md 하위 링크 문서 전수 확인 리포트

- 확인 일시(UTC): 2026-04-03
- 대상 AGENTS 문서: https://github.com/gnuboard/g7/blob/main/AGENTS.md
- 확인 방식: AGENTS.md의 상대경로 `.md` 링크를 추출해 각 파일을 raw URL로 조회, 접근 가능 여부/제목/라인 수를 점검

## 요약

- AGENTS.md에서 추출한 상대경로 `.md` 링크 수: **94개**
- 조회 성공: **93개**
- 조회 실패: **1개**
- 분류별 링크 수: backend 19 / frontend 42 / frontend-templates 6 / extension 22 / common 4 / .claude 1

## 전수 확인 목록

| # | 경로 | 상태 | 제목(첫 H1) | 라인 수 |
|---|---|---|---|---:|
| 1 | [.claude/docs/auto-document.md](https://github.com/gnuboard/g7/blob/main/.claude/docs/auto-document.md) | FAIL | - | - |
| 2 | [docs/backend/activity-log-hooks.md](https://github.com/gnuboard/g7/blob/main/docs/backend/activity-log-hooks.md) | OK | 활동 로그 훅 레퍼런스 (Activity Log Hooks Reference) | 715 |
| 3 | [docs/backend/activity-log.md](https://github.com/gnuboard/g7/blob/main/docs/backend/activity-log.md) | OK | 활동 로그 시스템 (Activity Log System) | 956 |
| 4 | [docs/backend/api-resources.md](https://github.com/gnuboard/g7/blob/main/docs/backend/api-resources.md) | OK | API 리소스 | 505 |
| 5 | [docs/backend/authentication.md](https://github.com/gnuboard/g7/blob/main/docs/backend/authentication.md) | OK | 인증 및 세션 처리 | 333 |
| 6 | [docs/backend/broadcasting.md](https://github.com/gnuboard/g7/blob/main/docs/backend/broadcasting.md) | OK | Broadcasting (실시간 이벤트) | 479 |
| 7 | [docs/backend/controllers.md](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md) | OK | 컨트롤러 계층 구조 | 320 |
| 8 | [docs/backend/core-config.md](https://github.com/gnuboard/g7/blob/main/docs/backend/core-config.md) | OK | 코어 설정 (config/core.php) | 202 |
| 9 | [docs/backend/core-update-system.md](https://github.com/gnuboard/g7/blob/main/docs/backend/core-update-system.md) | OK | 코어 업데이트 시스템 (Core Update System) | 741 |
| 10 | [docs/backend/enum.md](https://github.com/gnuboard/g7/blob/main/docs/backend/enum.md) | OK | Enum 사용 규칙 | 344 |
| 11 | [docs/backend/exceptions.md](https://github.com/gnuboard/g7/blob/main/docs/backend/exceptions.md) | OK | Custom Exception 다국어 처리 | 323 |
| 12 | [docs/backend/middleware.md](https://github.com/gnuboard/g7/blob/main/docs/backend/middleware.md) | OK | 미들웨어 등록 규칙 | 395 |
| 13 | [docs/backend/notification-system.md](https://github.com/gnuboard/g7/blob/main/docs/backend/notification-system.md) | OK | 알림 시스템 (Notification System) | 186 |
| 14 | [docs/backend/response-helper.md](https://github.com/gnuboard/g7/blob/main/docs/backend/response-helper.md) | OK | API 응답 규칙 (ResponseHelper) | 596 |
| 15 | [docs/backend/routing.md](https://github.com/gnuboard/g7/blob/main/docs/backend/routing.md) | OK | 라우트 네이밍 및 경로 | 278 |
| 16 | [docs/backend/search-system.md](https://github.com/gnuboard/g7/blob/main/docs/backend/search-system.md) | OK | Scout 검색 엔진 시스템 (Search System) | 371 |
| 17 | [docs/backend/seo-system.md](https://github.com/gnuboard/g7/blob/main/docs/backend/seo-system.md) | OK | SEO 페이지 생성기 시스템 (SEO Page Generator) | 1256 |
| 18 | [docs/backend/service-provider.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-provider.md) | OK | 서비스 프로바이더 안전성 | 306 |
| 19 | [docs/backend/service-repository.md](https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md) | OK | Service-Repository 패턴 | 972 |
| 20 | [docs/backend/validation.md](https://github.com/gnuboard/g7/blob/main/docs/backend/validation.md) | OK | 검증 (Validation) | 1031 |
| 21 | [docs/cheatsheet.md](https://github.com/gnuboard/g7/blob/main/docs/cheatsheet.md) | OK | 그누보드7 자주 쓰는 명령어 치트시트 | 212 |
| 22 | [docs/database-guide.md](https://github.com/gnuboard/g7/blob/main/docs/database-guide.md) | OK | 그누보드7 데이터베이스 개발 가이드 | 850 |
| 23 | [docs/extension/changelog-rules.md](https://github.com/gnuboard/g7/blob/main/docs/extension/changelog-rules.md) | OK | Changelog 규칙 (Changelog Rules) | 214 |
| 24 | [docs/extension/extension-manager.md](https://github.com/gnuboard/g7/blob/main/docs/extension/extension-manager.md) | OK | ExtensionManager (확장 관리자) | 350 |
| 25 | [docs/extension/extension-update-system.md](https://github.com/gnuboard/g7/blob/main/docs/extension/extension-update-system.md) | OK | 확장 업데이트 시스템 (Extension Update System) | 747 |
| 26 | [docs/extension/hooks.md](https://github.com/gnuboard/g7/blob/main/docs/extension/hooks.md) | OK | 훅 시스템 (Hook System) | 886 |
| 27 | [docs/extension/layout-extensions.md](https://github.com/gnuboard/g7/blob/main/docs/extension/layout-extensions.md) | OK | 레이아웃 확장 시스템 (Layout Extensions) | 683 |
| 28 | [docs/extension/menus.md](https://github.com/gnuboard/g7/blob/main/docs/extension/menus.md) | OK | 메뉴 시스템 | 394 |
| 29 | [docs/extension/module-assets.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-assets.md) | OK | 모듈 프론트엔드 에셋 시스템 | 484 |
| 30 | [docs/extension/module-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md) | OK | 모듈 개발 기초 | 630 |
| 31 | [docs/extension/module-commands.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-commands.md) | OK | 모듈 Artisan 커맨드 | 549 |
| 32 | [docs/extension/module-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md) | OK | 모듈 다국어 시스템 | 337 |
| 33 | [docs/extension/module-layouts.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md) | OK | 모듈 레이아웃 시스템 | 668 |
| 34 | [docs/extension/module-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md) | OK | 모듈 라우트 규칙 | 344 |
| 35 | [docs/extension/module-settings.md](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md) | OK | 모듈 환경설정 시스템 개발 가이드 | 550 |
| 36 | [docs/extension/permissions.md](https://github.com/gnuboard/g7/blob/main/docs/extension/permissions.md) | OK | 권한 시스템 | 817 |
| 37 | [docs/extension/plugin-development.md](https://github.com/gnuboard/g7/blob/main/docs/extension/plugin-development.md) | OK | 플러그인 개발 가이드 | 1155 |
| 38 | [docs/extension/storage-driver.md](https://github.com/gnuboard/g7/blob/main/docs/extension/storage-driver.md) | OK | 스토리지 드라이버 시스템 (StorageInterface) | 1485 |
| 39 | [docs/extension/template-basics.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-basics.md) | OK | 템플릿 시스템 기초 | 610 |
| 40 | [docs/extension/template-caching.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-caching.md) | OK | 템플릿 캐싱 전략 | 524 |
| 41 | [docs/extension/template-commands.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-commands.md) | OK | 템플릿 Artisan 커맨드 | 381 |
| 42 | [docs/extension/template-routing.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-routing.md) | OK | 템플릿 라우트/언어 파일 규칙 | 367 |
| 43 | [docs/extension/template-security.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-security.md) | OK | 템플릿 보안 정책 | 391 |
| 44 | [docs/extension/template-workflow.md](https://github.com/gnuboard/g7/blob/main/docs/extension/template-workflow.md) | OK | 템플릿 개발 워크플로우 | 686 |
| 45 | [docs/frontend/actions-g7core-api.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-g7core-api.md) | OK | 액션 시스템 - G7Core API (React 컴포넌트용) | 338 |
| 46 | [docs/frontend/actions-handlers-navigation.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-handlers-navigation.md) | OK | 액션 핸들러 - 네비게이션 | 435 |
| 47 | [docs/frontend/actions-handlers-state.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-handlers-state.md) | OK | 액션 핸들러 - 상태 관리 | 974 |
| 48 | [docs/frontend/actions-handlers-ui.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-handlers-ui.md) | OK | 액션 핸들러 - UI 인터랙션 | 1141 |
| 49 | [docs/frontend/actions-handlers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions-handlers.md) | OK | 액션 핸들러 - 핸들러별 상세 사용법 | 246 |
| 50 | [docs/frontend/actions.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/actions.md) | OK | 액션 핸들러 가이드 | 597 |
| 51 | [docs/frontend/auth-system.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/auth-system.md) | OK | 인증 시스템 (AuthManager) | 310 |
| 52 | [docs/frontend/component-props-composite.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/component-props-composite.md) | OK | 컴포넌트 Props 레퍼런스 - Composite | 581 |
| 53 | [docs/frontend/component-props.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/component-props.md) | OK | 컴포넌트 Props 레퍼런스 | 1112 |
| 54 | [docs/frontend/components-advanced.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components-advanced.md) | OK | 컴포넌트 고급 기능 | 479 |
| 55 | [docs/frontend/components-patterns.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components-patterns.md) | OK | 컴포넌트 패턴 및 다국어 | 320 |
| 56 | [docs/frontend/components-types.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components-types.md) | OK | 컴포넌트 타입별 개발 규칙 | 403 |
| 57 | [docs/frontend/components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/components.md) | OK | 컴포넌트 개발 규칙 | 154 |
| 58 | [docs/frontend/dark-mode.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/dark-mode.md) | OK | 다크 모드 지원 (engine-v1.1.0+) | 313 |
| 59 | [docs/frontend/data-binding-i18n.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding-i18n.md) | OK | 데이터 바인딩 - 다국어 처리 | 984 |
| 60 | [docs/frontend/data-binding.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding.md) | OK | 데이터 바인딩 및 표현식 | 677 |
| 61 | [docs/frontend/data-sources-advanced.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-sources-advanced.md) | OK | 데이터 소스 - 고급 기능 | 1474 |
| 62 | [docs/frontend/data-sources.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/data-sources.md) | OK | 데이터 소스 (Data Sources) | 759 |
| 63 | [docs/frontend/editors.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/editors.md) | OK | 에디터 컴포넌트 가이드 | 374 |
| 64 | [docs/frontend/g7core-api-advanced.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/g7core-api-advanced.md) | OK | G7Core 전역 API 레퍼런스 - 고급 | 979 |
| 65 | [docs/frontend/g7core-api.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/g7core-api.md) | OK | G7Core 전역 API 레퍼런스 | 948 |
| 66 | [docs/frontend/g7core-helpers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/g7core-helpers.md) | OK | G7Core 헬퍼 API | 470 |
| 67 | [docs/frontend/layout-json-components-loading.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-components-loading.md) | OK | 레이아웃 JSON - 데이터 로딩 및 생명주기 | 633 |
| 68 | [docs/frontend/layout-json-components-rendering.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-components-rendering.md) | OK | 레이아웃 JSON - 조건부/반복 렌더링 | 721 |
| 69 | [docs/frontend/layout-json-components-slots.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-components-slots.md) | OK | 레이아웃 JSON - 슬롯 시스템 | 350 |
| 70 | [docs/frontend/layout-json-components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-components.md) | OK | 레이아웃 JSON - 컴포넌트 (반복 렌더링, Blur, 생명주기, 슬롯) | 207 |
| 71 | [docs/frontend/layout-json-features-actions.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-features-actions.md) | OK | 레이아웃 JSON - 초기화, 모달, 액션, 스크립트 | 926 |
| 72 | [docs/frontend/layout-json-features-error.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-features-error.md) | OK | 레이아웃 JSON - 에러 핸들링 | 394 |
| 73 | [docs/frontend/layout-json-features-styling.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-features-styling.md) | OK | 레이아웃 JSON - 스타일 및 계산된 값 | 302 |
| 74 | [docs/frontend/layout-json-features.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-features.md) | OK | 레이아웃 JSON - 기능 (에러 핸들링, 초기화, 모달, 액션) | 190 |
| 75 | [docs/frontend/layout-json-inheritance.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json-inheritance.md) | OK | 레이아웃 JSON - 상속 (Extends, Partial, 병합) | 947 |
| 76 | [docs/frontend/layout-json.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json.md) | OK | 레이아웃 JSON 스키마 | 887 |
| 77 | [docs/frontend/layout-testing.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-testing.md) | OK | 그누보드7 레이아웃 파일 렌더링 테스트 가이드 | 724 |
| 78 | [docs/frontend/modal-usage.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/modal-usage.md) | OK | Modal 컴포넌트 사용 가이드 | 919 |
| 79 | [docs/frontend/responsive-layout.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/responsive-layout.md) | OK | 반응형 레이아웃 개발 (engine-v1.1.0+) | 509 |
| 80 | [docs/frontend/security.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/security.md) | OK | 보안 및 검증 | 622 |
| 81 | [docs/frontend/state-management-advanced.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/state-management-advanced.md) | OK | 상태 관리 - 고급 기능 | 819 |
| 82 | [docs/frontend/state-management-forms.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/state-management-forms.md) | OK | 상태 관리 - 폼 자동 바인딩 및 setState | 1239 |
| 83 | [docs/frontend/state-management.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/state-management.md) | OK | 전역 상태 관리 | 483 |
| 84 | [docs/frontend/tailwind-safelist.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/tailwind-safelist.md) | OK | Tailwind Safelist 가이드 | 209 |
| 85 | [docs/frontend/template-development.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/template-development.md) | OK | 템플릿 개발 가이드라인 | 537 |
| 86 | [docs/frontend/template-handlers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/template-handlers.md) | OK | 템플릿 전용 핸들러 | 60 |
| 87 | [docs/frontend/templates/sirsoft-admin_basic/components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-admin_basic/components.md) | OK | sirsoft-admin_basic 컴포넌트 | 487 |
| 88 | [docs/frontend/templates/sirsoft-admin_basic/handlers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-admin_basic/handlers.md) | OK | sirsoft-admin_basic 핸들러 | 446 |
| 89 | [docs/frontend/templates/sirsoft-admin_basic/layouts.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-admin_basic/layouts.md) | OK | sirsoft-admin_basic 레이아웃 | 369 |
| 90 | [docs/frontend/templates/sirsoft-basic/components.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-basic/components.md) | OK | sirsoft-basic 컴포넌트 | 248 |
| 91 | [docs/frontend/templates/sirsoft-basic/handlers.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-basic/handlers.md) | OK | sirsoft-basic 핸들러 | 432 |
| 92 | [docs/frontend/templates/sirsoft-basic/layouts.md](https://github.com/gnuboard/g7/blob/main/docs/frontend/templates/sirsoft-basic/layouts.md) | OK | sirsoft-basic 레이아웃 | 648 |
| 93 | [docs/requirements.md](https://github.com/gnuboard/g7/blob/main/docs/requirements.md) | OK | 그누보드7 시스템 요구사항 (System Requirements) | 229 |
| 94 | [docs/testing-guide.md](https://github.com/gnuboard/g7/blob/main/docs/testing-guide.md) | OK | 그누보드7 테스트 가이드 | 1030 |

## 확인 결과 해석

- 직접 링크된 하위 문서는 전부 점검했다.
- `.claude/docs/auto-document.md`는 raw URL 기준 접근 불가(FAIL)로 확인되어 원문 검토를 진행할 수 없었다.
- 나머지 문서는 모두 조회되어 제목/라인 수까지 확인했다.

## PLAN 반영

- 금지 패턴/컴플라이언스는 `08_금지사항_컴플라이언스체크.md` 기준으로 유지한다.
