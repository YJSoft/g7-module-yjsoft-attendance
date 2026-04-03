# 06. UI/UX, 레이아웃, 상호작용 계획 (레이아웃 JSON 규칙 반영)

## 1) 사용자 출석부 페이지 구성

- 상단 정보
  - 오늘 날짜/요일
  - 현재 시간(클라이언트 초 단위 갱신)
  - 연속 출석일/누적 출석일
- 출석 입력
  - 랜덤 기본 인삿말 노출
  - 사용자 수정 입력
  - 출석 버튼
- 캘린더
  - 출석일 표시
  - 결석일 표시
  - 오늘 강조
- 상세보기 드롭다운
  - 출석 가능 시간, 보너스 규칙, 랜덤포인트 정책 표시

## 2) 레이아웃 JSON 필수 준수

- HTML 태그 직접 사용 금지: `Div`, `Button`, `Span`, `Input` 등 기본 컴포넌트 사용.
- 텍스트는 `text` 속성 또는 `children` 내 컴포넌트로 표현.
- 다국어 문구는 `$t:key` 사용.
- 데이터 바인딩은 Optional Chaining + fallback을 기본으로 적용.
  - 예: `{{attendance?.streak ?? 0}}`
- 액션 핸들러명은 문서 정의값만 사용.
  - `apiCall`, `navigate`, `setState`, `openModal`, `closeModal` 등

## 3) 사용자 흐름

1. 페이지 로드
   - status data_source 호출
   - calendar data_source 호출
   - 기본 인삿말 풀에서 랜덤 기본값 설정
2. 출석 클릭
   - `apiCall`로 user/checkin API 호출
   - 성공 시 캘린더와 요약 정보 상태 갱신
3. 상세보기
   - 버튼 클릭 시 `setState`로 펼침/접힘 토글

## 4) 보안/안정성 UX 원칙

- 사용자 레이아웃 data_sources에 관리자 엔드포인트 등록 금지.
- 에러 메시지는 사용자가 이해 가능한 문구 + 다국어 키 기반으로 노출.
- 렌더링 에러 방지를 위해 바인딩마다 fallback을 명시한다.
