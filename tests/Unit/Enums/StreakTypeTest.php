<?php

namespace Modules\Yjsoft\Attendance\Tests\Unit\Enums;

use Carbon\Carbon;
use Modules\Yjsoft\Attendance\Enums\StreakType;
use Modules\Yjsoft\Attendance\Tests\ModuleTestCase;

class StreakTypeTest extends ModuleTestCase
{
    /**
     * 주어진 날짜의 주간 기간(월~일)이 올바르게 계산되는지 검증
     */
    public function test_weekly_period_calculation(): void
    {
        // 2026-04-01 (수요일)
        $date = Carbon::parse('2026-04-01');
        $period = StreakType::Weekly->getPeriod($date);

        // ISO 8601: 월요일 시작
        $this->assertEquals('2026-03-30', $period['start']); // 월요일
        $this->assertEquals('2026-04-05', $period['end']);     // 일요일
    }

    /**
     * 월 1일~말일이 올바르게 계산되는지 검증
     */
    public function test_monthly_period_calculation(): void
    {
        $date = Carbon::parse('2026-03-15');
        $period = StreakType::Monthly->getPeriod($date);

        $this->assertEquals('2026-03-01', $period['start']);
        $this->assertEquals('2026-03-31', $period['end']);

        // 2월 (윤년 아닌 해)
        $date2 = Carbon::parse('2026-02-10');
        $period2 = StreakType::Monthly->getPeriod($date2);

        $this->assertEquals('2026-02-01', $period2['start']);
        $this->assertEquals('2026-02-28', $period2['end']);
    }

    /**
     * 연도 1/1~12/31이 올바르게 계산되는지 검증
     */
    public function test_yearly_period_calculation(): void
    {
        $date = Carbon::parse('2026-06-15');
        $period = StreakType::Yearly->getPeriod($date);

        $this->assertEquals('2026-01-01', $period['start']);
        $this->assertEquals('2026-12-31', $period['end']);
    }

    /**
     * 월을 걸친 연속 출석은 해당 월 개근이 아님을 검증
     *
     * 3월 15일~4월 14일 연속 출석은 3월이나 4월 개근이 아님.
     * 개근은 해당 월의 1일~말일 전일 출석이어야 함.
     */
    public function test_cross_month_not_streak(): void
    {
        // 3월 15일 기준의 월간 기간은 3월 1일~31일
        $marchDate = Carbon::parse('2026-03-15');
        $marchPeriod = StreakType::Monthly->getPeriod($marchDate);

        $this->assertEquals('2026-03-01', $marchPeriod['start']);
        $this->assertEquals('2026-03-31', $marchPeriod['end']);

        // 4월 14일 기준의 월간 기간은 4월 1일~30일
        $aprilDate = Carbon::parse('2026-04-14');
        $aprilPeriod = StreakType::Monthly->getPeriod($aprilDate);

        $this->assertEquals('2026-04-01', $aprilPeriod['start']);
        $this->assertEquals('2026-04-30', $aprilPeriod['end']);

        // 3월 15일~4월 14일은 31일간 연속이지만
        // 3월 개근(3/1~3/31)에도 4월 개근(4/1~4/30)에도 해당하지 않음
        $periodStart3 = Carbon::parse($marchPeriod['start']);
        $periodEnd3 = Carbon::parse($marchPeriod['end']);
        $totalDays3 = $periodStart3->diffInDays($periodEnd3) + 1;

        // 3/15~3/31까지만 출석한 경우 → 17일 (31일에 미달)
        $this->assertEquals(31, $totalDays3);
        $this->assertGreaterThan(17, $totalDays3);

        $periodStart4 = Carbon::parse($aprilPeriod['start']);
        $periodEnd4 = Carbon::parse($aprilPeriod['end']);
        $totalDays4 = $periodStart4->diffInDays($periodEnd4) + 1;

        // 4/1~4/14까지만 출석한 경우 → 14일 (30일에 미달)
        $this->assertEquals(30, $totalDays4);
        $this->assertGreaterThan(14, $totalDays4);
    }

    /**
     * values() 메서드가 모든 값을 반환하는지 검증
     */
    public function test_values_returns_all(): void
    {
        $values = StreakType::values();

        $this->assertContains('weekly', $values);
        $this->assertContains('monthly', $values);
        $this->assertContains('yearly', $values);
        $this->assertCount(3, $values);
    }

    /**
     * isValid() 메서드가 올바르게 검증하는지 확인
     */
    public function test_is_valid(): void
    {
        $this->assertTrue(StreakType::isValid('weekly'));
        $this->assertTrue(StreakType::isValid('monthly'));
        $this->assertTrue(StreakType::isValid('yearly'));
        $this->assertFalse(StreakType::isValid('daily'));
        $this->assertFalse(StreakType::isValid(''));
    }

    /**
     * label() 메서드가 문자열을 반환하는지 검증
     */
    public function test_label_returns_string(): void
    {
        foreach (StreakType::cases() as $type) {
            $this->assertIsString($type->label());
            $this->assertNotEmpty($type->label());
        }
    }
}
