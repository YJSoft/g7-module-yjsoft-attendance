# 그누보드7 출석부 모듈 (yjsoft-attendance) 개발 계획

> **모듈 식별자**: `yjsoft-attendance`  
> **네임스페이스**: `Modules\Yjsoft\Attendance\`  
> **참고 문서**: [AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)

---

## 전체 개발 단계

| 단계 | 이름 | 파일 |
|------|------|------|
| 1단계 | 모듈 기반 구조 설계 | [STAGE-1-module-structure.md](./STAGE-1-module-structure.md) |
| 2단계 | 데이터베이스 설계 | [STAGE-2-database.md](./STAGE-2-database.md) |
| 3단계 | 백엔드 구현 | [STAGE-3-backend-core.md](./STAGE-3-backend-core.md) · [STAGE-3-backend-api.md](./STAGE-3-backend-api.md) |
| 4단계 | 프론트엔드 레이아웃 구현 | [STAGE-4-frontend-user.md](./STAGE-4-frontend-user.md) · [STAGE-4-frontend-admin.md](./STAGE-4-frontend-admin.md) |
| 5단계 | 테스트 작성 | [STAGE-5-testing.md](./STAGE-5-testing.md) |
| 6단계 | 최종 검토 및 마무리 | [STAGE-6-review.md](./STAGE-6-review.md) |
| 별첨 | 금지 패턴 레퍼런스 | [FORBIDDEN-PATTERNS.md](./FORBIDDEN-PATTERNS.md) |

---

## 기능 요약

### 출석 기능
- 개근(주/월/년) 보너스 — **달력 기준** (예: 3월 1일~31일 연속 출석 시만 개근)
- 매일 출석 순위 1~3위 보너스 포인트
- 출석 시 인삿말 입력 (기본 인삿말 중 랜덤 기본값 자동 입력)
- 출석부 페이지: 달력 형태로 출석일/결석일 표시
- 출석부 페이지: 현재 시각 실시간 표시
- 자세히 보기 드롭다운 (개근점수, 랭킹점수 등 세부 정보)

### 설정 기능
- 개근(주/월/년) 보너스 포인트 설정
- 매일 1~3위 보너스 포인트 설정
- 자동출석 사용 여부 (로그인/자동로그인 후 자동 출석 처리)
- 출석 가능 시간대 설정 (시작 시간 ~ 종료 시간, 기본값: 제한 없음)
- 랜덤 추가 포인트 설정 (사용 여부, 최솟값, 최댓값, 지급 확률)
- 기본 인삿말 목록 설정
- 출석 허용 권한(Role) 설정 — 화이트리스트/블랙리스트 모드

---

## 핵심 준수 사항 (요약)

> 상세 내용은 [FORBIDDEN-PATTERNS.md](./FORBIDDEN-PATTERNS.md) 참조

1. **유저 페이지에서 관리자 전용 API 호출 금지** (`/api/admin/*` 엔드포인트)
2. **레이아웃 JSON에서 지원되지 않는 문법 사용 금지** (추측/가정 금지)
3. **HTML 태그 직접 사용 금지** (`<div>` → `Div`, `<button>` → `Button`)
4. **FormRequest에서만 검증** (Service/Controller 인라인 검증 금지)
5. **RepositoryInterface 주입 필수** (구체 클래스 직접 주입 금지)
6. **예외 메시지 하드코딩 금지** (`__()` 다국어 함수 필수)
7. **인증 필요 미들웨어 전역 등록 금지**
8. **모든 라우트에 `name()` 필수**

---

## 참고 문서 링크

| 문서 | URL |
|------|-----|
| AGENTS.md | https://github.com/gnuboard/g7/blob/main/AGENTS.md |
| 모듈 개발 기초 | https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md |
| 모듈 레이아웃 | https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md |
| 모듈 설정 | https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md |
| 모듈 라우트 | https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md |
| 모듈 다국어 | https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md |
| 컨트롤러 계층 | https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md |
| 라우트 네이밍 | https://github.com/gnuboard/g7/blob/main/docs/backend/routing.md |
| Service-Repository | https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md |
| 권한 시스템 | https://github.com/gnuboard/g7/blob/main/docs/extension/permissions.md |
| 레이아웃 JSON 스키마 | https://github.com/gnuboard/g7/blob/main/docs/frontend/layout-json.md |
| 컴포넌트 규칙 | https://github.com/gnuboard/g7/blob/main/docs/frontend/components.md |
| 데이터 바인딩 | https://github.com/gnuboard/g7/blob/main/docs/frontend/data-binding.md |
| 다크모드 | https://github.com/gnuboard/g7/blob/main/docs/frontend/dark-mode.md |
| 테스트 프로토콜 | https://github.com/gnuboard/g7/blob/main/AGENTS.md#테스트-프로토콜 |
