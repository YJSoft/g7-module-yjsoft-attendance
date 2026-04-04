# Incident: CI 워크플로우에서 _bundled 경로 재사용

> **발생일**: 2026-04-04  
> **심각도**: 중간 (CI가 잘못된 경로로 테스트 실행)  
> **상태**: 해결됨

---

## 무슨 일이 있었나

GitHub Actions CI 워크플로우(`tests.yml`) 작성 시, 서드파티 모듈임에도 불구하고 G7 코어 내 모듈 심링크 경로를 `modules/_bundled/yjsoft-attendance`로 설정했다. README.md의 테스트 명령과 설치 가이드에서도 동일한 실수를 반복했다.

### 잘못된 CI 경로 (❌)

```yaml
# .github/workflows/tests.yml
mkdir -p g7/modules/_bundled
ln -s "$GITHUB_WORKSPACE/module" g7/modules/_bundled/yjsoft-attendance
```

### 올바른 CI 경로 (✅)

```yaml
# .github/workflows/tests.yml
mkdir -p g7/modules
ln -s "$GITHUB_WORKSPACE/module" g7/modules/yjsoft-attendance
```

---

## 왜 이런 실수를 했나

1. **이전 incident(incident-bundled.md)의 교훈을 코드에 적용하지 못했다.** 저장소 루트에 `modules/_bundled/` 폴더를 만드는 실수는 바로잡았지만, CI에서 G7 코어에 모듈을 설치할 때의 경로까지는 생각이 미치지 못했다.

2. **`_bundled` 폴더의 의미를 정확히 이해하지 못했다.** `modules/_bundled/`는 **그누보드7과 함께 제공(번들)되는 모듈만** 저장되는 경로다. 서드파티 모듈은 `modules/{module-id}/`에 설치되어야 한다. `_bundled` 폴더의 모듈도 인스톨러가 `modules/` 하위로 복사해야 비로소 활성화된다.

3. **다른 에이전트의 작업물을 그대로 받아들였다.** vitest.config.ts의 주석에도 `_bundled` 경로가 적혀 있었는데, 이를 검증 없이 수용했다.

---

## 영향 범위

| 파일 | 수정 내용 |
|------|----------|
| `.github/workflows/tests.yml` | 모듈 심링크 경로 `_bundled/` → 직접 `modules/` |
| `README.md` | 설치 가이드, PHPUnit 명령어 경로 수정 |
| `vitest.config.ts` | 주석에서 `_bundled` 참조 제거 |

---

## 핵심 규칙 재확인

```
modules/_bundled/  → 그누보드7 코어에 번들된 공식 모듈만 저장
modules/{id}/      → 서드파티 모듈 설치 경로 (인스톨러가 _bundled에서 복사하거나, 직접 설치)
```

**서드파티 모듈 저장소의 CI에서 G7 코어에 모듈을 배치할 때는 반드시 `modules/{module-id}/`를 사용한다. 절대로 `_bundled/`를 사용하지 않는다.**

---

## 이 문서의 용도

`incident-bundled.md`와 함께, CI 및 문서 작성 시에도 경로를 올바르게 사용하고 있는지 검증하기 위한 체크리스트 역할을 한다.
