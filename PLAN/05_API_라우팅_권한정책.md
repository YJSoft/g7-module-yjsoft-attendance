# 05. API, 라우팅, 권한 정책 (G7 규칙 정렬)

## 1) 라우트 Prefix/Name 규칙 (module-routing 기준)

모듈 API 라우트는 코어가 자동 Prefix를 부여하므로 아래 규칙을 고정한다.

- URL Prefix: `api/modules/yjsoft-attendance/...`
- Name Prefix: `api.modules.yjsoft-attendance....`
- 웹 Prefix(필요 시): `modules/yjsoft-attendance/...`
- 비활성 모듈 상태에서는 라우트 비등록 상태가 정상이다.

## 2) API 계층 분리

### 사용자(인증) API

- Base: `AuthBaseController`
- 예시
  - `GET /api/modules/yjsoft-attendance/user/status`
  - `POST /api/modules/yjsoft-attendance/user/checkin`
  - `GET /api/modules/yjsoft-attendance/user/calendar`

### 관리자 API

- Base: `AdminBaseController`
- 예시
  - `GET /api/modules/yjsoft-attendance/admin/settings`
  - `PUT /api/modules/yjsoft-attendance/admin/settings`
  - `GET /api/modules/yjsoft-attendance/admin/statistics`

## 3) 절대 금지

- 사용자 페이지/레이아웃에서 관리자 API 호출 금지.
- `handler: "api"` 금지, `handler: "apiCall"`만 사용.
- `G7Core.api.call` 직접 호출 금지(문서 권장 패턴 위반).

## 4) 검증 및 응답 규칙

- 입력 검증은 FormRequest에서 수행한다.
- 컨트롤러는 Service 주입만 허용(Repository 직접 주입 금지).
- API 응답은 ResponseHelper 계열(`success`, `error`, `successWithResource`)을 사용한다.
- 예외/검증 메시지는 하드코딩하지 않고 다국어 키로 처리한다.

## 5) 데이터소스/엔드포인트 보안 (frontend security)

- 레이아웃 JSON data_sources에서 허용되는 엔드포인트 패턴을 준수한다.
- 사용자 레이아웃에서 `/api/modules/yjsoft-attendance/admin/*` 사용 금지.
- 민감 설정값은 frontend_schema(또는 동등한 노출제어)로 마스킹한다.
