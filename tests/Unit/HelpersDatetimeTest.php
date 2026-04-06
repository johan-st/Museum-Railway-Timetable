<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelpersDatetimeTest extends TestCase {

    public function test_validate_date_accepts_iso(): void {
        self::assertTrue(MRT_validate_date('2026-04-04'));
        self::assertFalse(MRT_validate_date('04-04-2026'));
        self::assertFalse(MRT_validate_date('2026-4-4'));
    }

    public function test_validate_time_hhmm(): void {
        self::assertTrue(MRT_validate_time_hhmm('09:30'));
        self::assertTrue(MRT_validate_time_hhmm(''));
        self::assertFalse(MRT_validate_time_hhmm('9:30'));
        self::assertFalse(MRT_validate_time_hhmm('25:00'));
    }

    public function test_format_duration_minutes_same_day(): void {
        self::assertSame(90, MRT_format_duration_minutes('10:00', '11:30'));
        self::assertSame(0, MRT_format_duration_minutes('12:00', '12:00'));
    }

    public function test_format_duration_minutes_invalid_or_overnight_returns_null(): void {
        self::assertNull(MRT_format_duration_minutes('11:00', '10:00'));
        self::assertNull(MRT_format_duration_minutes('bad', '10:00'));
    }

    public function test_time_hhmm_to_minutes(): void {
        self::assertSame(0, MRT_time_hhmm_to_minutes('00:00'));
        self::assertSame(1439, MRT_time_hhmm_to_minutes('23:59'));
        self::assertNull(MRT_time_hhmm_to_minutes('24:00'));
    }

    public function test_compare_hhmm(): void {
        self::assertSame(-1, MRT_compare_hhmm('08:00', '09:00'));
        self::assertSame(1, MRT_compare_hhmm('10:00', '09:59'));
        self::assertSame(0, MRT_compare_hhmm('07:00', '07:00'));
    }

    public function test_add_minutes_to_hhmm_caps_at_day_end(): void {
        self::assertSame('23:59', MRT_add_minutes_to_hhmm('23:00', 120));
        self::assertSame('10:15', MRT_add_minutes_to_hhmm('10:00', 15));
    }
}
