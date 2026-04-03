<?php

namespace Modules\Yjsoft\Attendance\Enums;

/**
 * 개근 타입 Enum
 *
 * 주간/월간/연간 개근을 구분하는 Backed Enum.
 * 각 타입별 기간은 달력 기준으로 판정한다.
 */
enum StreakType: string
{
    /**
     * 주간 개근 (ISO 8601 기준 월요일~일요일)
     */
    case Weekly = 'weekly';

    /**
     * 월간 개근 (해당 월 1일~말일)
     */
    case Monthly = 'monthly';

    /**
     * 연간 개근 (해당 연도 1월 1일~12월 31일)
     */
    case Yearly = 'yearly';

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
            self::Weekly  => __('yjsoft-attendance::messages.streak.weekly'),
            self::Monthly => __('yjsoft-attendance::messages.streak.monthly'),
            self::Yearly  => __('yjsoft-attendance::messages.streak.yearly'),
        };
    }

    /**
     * 주어진 날짜가 속하는 기간의 시작/종료일 반환
     *
     * @param \DateTimeInterface $date 기준 날짜
     * @return array{start: string, end: string}
     */
    public function getPeriod(\DateTimeInterface $date): array
    {
        return match ($this) {
            self::Weekly  => $this->getWeekPeriod($date),
            self::Monthly => $this->getMonthPeriod($date),
            self::Yearly  => $this->getYearPeriod($date),
        };
    }

    /**
     * ISO 8601 기준 해당 주의 월요일~일요일 기간 반환
     *
     * @param \DateTimeInterface $date 기준 날짜
     * @return array{start: string, end: string}
     */
    private function getWeekPeriod(\DateTimeInterface $date): array
    {
        // ISO 8601: 월요일 시작
        $dayOfWeek = (int) date('N', $date->getTimestamp()); // 1=Mon, 7=Sun
        $monday = (clone \Carbon\Carbon::instance($date))->subDays($dayOfWeek - 1)->startOfDay();
        $sunday = (clone $monday)->addDays(6)->endOfDay();

        return ['start' => $monday->toDateString(), 'end' => $sunday->toDateString()];
    }

    /**
     * 해당 월의 1일~말일 기간 반환
     *
     * @param \DateTimeInterface $date 기준 날짜
     * @return array{start: string, end: string}
     */
    private function getMonthPeriod(\DateTimeInterface $date): array
    {
        $carbon = \Carbon\Carbon::instance($date);

        return [
            'start' => $carbon->copy()->startOfMonth()->toDateString(),
            'end'   => $carbon->copy()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * 해당 연도의 1월 1일~12월 31일 기간 반환
     *
     * @param \DateTimeInterface $date 기준 날짜
     * @return array{start: string, end: string}
     */
    private function getYearPeriod(\DateTimeInterface $date): array
    {
        $carbon = \Carbon\Carbon::instance($date);

        return [
            'start' => $carbon->copy()->startOfYear()->toDateString(),
            'end'   => $carbon->copy()->endOfYear()->toDateString(),
        ];
    }
}
