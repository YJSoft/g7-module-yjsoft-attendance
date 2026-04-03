<?php

namespace Modules\Yjsoft\Attendance\Services;

use App\Contracts\Extension\ModuleSettingsInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

/**
 * 출석부 모듈 설정 서비스
 *
 * ModuleSettingsInterface 구현. 코어의 module_setting() 헬퍼에서 자동 검색/위임.
 */
class AttendanceSettingsService implements ModuleSettingsInterface
{
    private const MODULE_IDENTIFIER = 'yjsoft-attendance';
    private ?array $defaults = null;
    private ?array $settings = null;

    /**
     * defaults.json 경로 반환
     */
    public function getSettingsDefaultsPath(): ?string
    {
        $path = $this->getModulePath() . '/config/settings/defaults.json';

        return file_exists($path) ? $path : null;
    }

    /**
     * 단일 설정값 조회
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getAllSettings();

        return Arr::get($settings, $key, $default);
    }

    /**
     * 단일 설정값 저장
     */
    public function setSetting(string $key, mixed $value): bool
    {
        $settings = $this->getAllSettings();
        Arr::set($settings, $key, $value);

        $parts = explode('.', $key);
        $category = $parts[0];

        return $this->saveCategorySettings($category, $settings[$category] ?? []);
    }

    /**
     * 전체 설정 조회
     */
    public function getAllSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $defaults = $this->getDefaults();
        $categories = $defaults['_meta']['categories'] ?? [];
        $defaultValues = $defaults['defaults'] ?? [];

        $settings = [];
        foreach ($categories as $category) {
            $categoryDefaults = $defaultValues[$category] ?? [];
            $savedSettings = $this->loadCategorySettings($category);
            $settings[$category] = array_merge($categoryDefaults, $savedSettings);
        }

        $this->settings = $settings;

        return $settings;
    }

    /**
     * 카테고리별 설정 조회
     */
    public function getSettings(string $category): array
    {
        $allSettings = $this->getAllSettings();

        return $allSettings[$category] ?? [];
    }

    /**
     * 설정 저장
     */
    public function saveSettings(array $settings): bool
    {
        $success = true;

        foreach ($settings as $category => $categorySettings) {
            if (str_starts_with($category, '_')) {
                continue;
            }
            if (! $this->saveCategorySettings($category, $categorySettings)) {
                $success = false;
            }
        }

        $this->settings = null;

        return $success;
    }

    /**
     * 프론트엔드 공개 설정 반환
     */
    public function getFrontendSettings(): array
    {
        $defaults = $this->getDefaults();
        $frontendSchema = $defaults['frontend_schema'] ?? [];
        $allSettings = $this->getAllSettings();

        $frontendSettings = [];
        foreach ($frontendSchema as $category => $schema) {
            if (! ($schema['expose'] ?? false)) {
                continue;
            }

            $categorySettings = $allSettings[$category] ?? [];
            $fields = $schema['fields'] ?? [];

            if (empty($fields)) {
                $frontendSettings[$category] = $categorySettings;
                continue;
            }

            $exposedFields = [];
            foreach ($fields as $fieldName => $fieldSchema) {
                if ($fieldSchema['expose'] ?? false) {
                    $exposedFields[$fieldName] = $categorySettings[$fieldName] ?? null;
                }
            }
            if (! empty($exposedFields)) {
                $frontendSettings[$category] = $exposedFields;
            }
        }

        return $frontendSettings;
    }

    /**
     * 모듈 경로 반환
     */
    private function getModulePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * 설정 저장 경로
     */
    private function getStoragePath(): string
    {
        return storage_path('app/modules/' . self::MODULE_IDENTIFIER . '/settings');
    }

    /**
     * defaults.json 파싱 결과 반환 (캐싱)
     */
    private function getDefaults(): array
    {
        if ($this->defaults !== null) {
            return $this->defaults;
        }

        $path = $this->getSettingsDefaultsPath();
        if ($path === null) {
            $this->defaults = [];

            return [];
        }

        $this->defaults = json_decode(File::get($path), true) ?? [];

        return $this->defaults;
    }

    /**
     * 카테고리별 저장된 설정 로드
     */
    private function loadCategorySettings(string $category): array
    {
        $path = $this->getStoragePath() . '/' . $category . '.json';

        if (! File::exists($path)) {
            return [];
        }

        return json_decode(File::get($path), true) ?? [];
    }

    /**
     * 카테고리별 설정 파일 저장
     */
    private function saveCategorySettings(string $category, array $settings): bool
    {
        $storagePath = $this->getStoragePath();

        if (! File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $path = $storagePath . '/' . $category . '.json';

        return (bool) File::put(
            $path,
            json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
