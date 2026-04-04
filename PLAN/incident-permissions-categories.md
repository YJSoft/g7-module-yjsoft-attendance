# Incident: 권한 구조 오류 — categories 필드 누락

## 발생 원인

`module.php`의 `getPermissions()` 메서드가 G7 코어가 요구하는 **3레벨 계층형 구조**(모듈 → 카테고리 → 권한)를 따르지 않고,
**플랫(flat) 배열** 형태로 권한을 정의하고 있었다.

### 잘못된 구조 (수정 전)

```php
return [
    ['identifier' => 'yjsoft-attendance.attend', ...],
    ['identifier' => 'yjsoft-attendance.admin.settings', ...],
    ['identifier' => 'yjsoft-attendance.admin.view', ...],
];
```

### 올바른 구조 (수정 후)

```php
return [
    'name' => ['ko' => '출석부', 'en' => 'Attendance'],
    'description' => ['ko' => '...', 'en' => '...'],
    'categories' => [
        [
            'identifier' => 'attendance',
            'permissions' => [
                ['action' => 'attend', 'type' => 'user', ...],
            ],
        ],
        [
            'identifier' => 'settings',
            'permissions' => [
                ['action' => 'read', 'type' => 'admin', ...],
                ['action' => 'update', 'type' => 'admin', ...],
            ],
        ],
        ...
    ],
];
```

## 근본 원인

G7 공식 문서(`docs/extension/permissions.md`)와 번들 모듈(sirsoft-page, sirsoft-board, sirsoft-ecommerce)의 
실제 구현을 참조하지 않고, 독자적인 플랫 배열 구조를 사용했다.

## 학습 교훈

1. **항상 공식 문서 먼저 확인**: `getPermissions()` 반환 구조는 `docs/extension/permissions.md`의 "getPermissions() 포맷 (모듈)" 섹션 참조
2. **번들 모듈 참고**: `g7-module-sirsoft-page`, `g7-module-sirsoft-board`, `g7-module-sirsoft-ecommerce` 3개 모듈의 `module.php` 참고
3. **identifier 자동 생성 규칙**: `{module-id}.{category-identifier}.{action}` — identifier를 직접 지정하지 않고, category의 identifier + permission의 action으로 자동 생성됨
4. **READ/UPDATE 분리**: 설정의 조회와 수정은 별도 권한(`settings.read`, `settings.update`)으로 분리해야 한다

## 영향 범위

- `module.php`: `getPermissions()`, `getAdminMenus()`
- `src/routes/api.php`: 미들웨어 권한 식별자
- `tests/Feature/Controllers/`: 테스트의 권한 부여 코드

## 변경된 권한 식별자 매핑

| 수정 전                               | 수정 후                               |
| ------------------------------------- | ------------------------------------- |
| `yjsoft-attendance.attend`            | `yjsoft-attendance.attendance.attend` |
| `yjsoft-attendance.admin.settings`    | `yjsoft-attendance.settings.read`     |
| `yjsoft-attendance.admin.settings`    | `yjsoft-attendance.settings.update`   |
| `yjsoft-attendance.admin.view`        | `yjsoft-attendance.stats.read`        |
