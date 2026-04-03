<?php

namespace Modules\Yjsoft\Attendance\Enums;

/**
 * 자동출석 모드 Enum
 *
 * 자동출석 기능의 동작 방식을 정의하는 Backed Enum.
 */
enum AccessControlMode: string
{
    /**
     * 자동출석 비활성화
     */
    case Disabled = 'disabled';

    /**
     * 로그인 시 자동출석
     */
    case OnLogin = 'on_login';

    /**
     * 자동로그인 시에만 자동출석
     */
    case OnAutoLogin = 'on_auto_login';

    /**
     * 모든 상태 값을 문자열 배열로 반환
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * 유효한 값인지 확인
     *
     * @param string $value 검증할 값
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * 다국어 라벨 반환
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Disabled    => __('yjsoft-attendance::messages.access_control.disabled'),
            self::OnLogin     => __('yjsoft-attendance::messages.access_control.on_login'),
            self::OnAutoLogin => __('yjsoft-attendance::messages.access_control.on_auto_login'),
        };
    }
}
