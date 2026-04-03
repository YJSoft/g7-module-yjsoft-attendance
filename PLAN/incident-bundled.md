# Incident: modules/_bundled/ 경로 오용

> **발생일**: 2026-04-03  
> **심각도**: 높음 (전체 디렉토리 구조 오류)  
> **상태**: 해결됨

---

## 무슨 일이 있었나

Stage 1 모듈 기반 구조 생성 시, 모든 파일을 `modules/_bundled/yjsoft-attendance/` 하위에 생성하는 치명적 실수를 저질렀다.

### 잘못 생성한 경로 (❌)

```
modules/_bundled/yjsoft-attendance/
├── module.json
├── module.php
├── composer.json
├── LICENSE
├── config/settings/defaults.json
├── src/lang/ko/messages.php
├── src/lang/en/messages.php
├── resources/lang/ko.json
└── resources/lang/en.json
```

### 올바른 경로 (✅)

```
(저장소 루트)/
├── module.json
├── module.php
├── composer.json
├── config/settings/defaults.json
├── src/lang/ko/messages.php
├── src/lang/en/messages.php
├── resources/lang/ko.json
└── resources/lang/en.json
```

---

## 왜 이런 실수를 했나

1. **문서를 대충 읽었다.** PLAN/STAGE-1-module-structure.md의 디렉토리 구조 섹션에 `modules/_bundled/yjsoft-attendance/` 라고 적혀 있는 것을 보고, 이것이 **G7 코어 내에서의 설치 경로**라는 맥락을 파악하지 않고 그대로 모듈 저장소 안에 해당 폴더를 만들었다.

2. **핵심 개념을 이해하지 못했다.** `modules/_bundled/`는 **그누보드7 코어 저장소**에서 번들 모듈이 저장되는 경로다. 독립 모듈 저장소(`g7-module-yjsoft-attendance`)의 루트 자체가 곧 모듈의 루트 디렉토리다. 이 기본적인 사실을 인지하지 못했다.

3. **참고 문서를 충분히 읽지 않았다.** `docs/ai-tools/skills/create-module.md`를 사전에 확인했다면, "생성 위치: `modules/_bundled/vendor-module/`"가 G7 코어 내부 경로임을 바로 알 수 있었다. 해당 문서를 읽지 않고 작업을 시작했다.

4. **확인 없이 바로 파일을 생성했다.** 구조에 대한 확신이 없는 상태에서 검증 단계 없이 즉시 파일을 만들기 시작했다.

---

## 교훈 및 재발 방지 규칙

### 반드시 지킬 것

1. **독립 모듈 저장소의 루트 = 모듈 루트 디렉토리다.** `modules/_bundled/`나 `modules/` 같은 경로를 모듈 저장소 안에 절대 만들지 않는다.

2. **작업 전에 반드시 아래 문서를 읽는다:**
   - `docs/ai-tools/skills/create-module.md` — 모듈 스캐폴딩 구조
   - `docs/extension/module-basics.md` — 모듈 디렉토리 구조 및 번들 규칙
   - `PLAN/incident-bundled.md` — **이 문서** (과거 실수 상기)

3. **PLAN 문서의 경로가 "G7 코어 기준"인지 "모듈 저장소 기준"인지 반드시 구분한다.** PLAN 문서에 `modules/_bundled/yjsoft-attendance/` 라고 적혀 있어도, 이는 G7 코어에 설치될 때의 경로일 뿐이다.

4. **파일 생성 전에 "이 경로가 맞는가?"를 한 번 더 확인한다.** 특히 최상위 디렉토리 구조는 실수 시 전체 파일을 삭제하고 다시 만들어야 하므로 신중해야 한다.

---

## 이 문서의 용도

**모든 Stage 작업을 시작하기 전에 이 문서를 읽어야 한다.** 과거의 실수를 반복하지 않기 위함이다.
