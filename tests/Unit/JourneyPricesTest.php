<?php
/**
 * Tests for price matrix helpers (production code: inc/functions/journey-prices.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Global MRT_* functions from inc/functions/journey-prices.php (no namespace).
 */
final class JourneyPricesTest extends TestCase {

    public function test_default_matrix_shape(): void {
        $m = MRT_get_default_price_matrix();
        self::assertSame(['single', 'return', 'day'], array_keys($m));
        foreach (MRT_price_ticket_type_keys() as $row) {
            self::assertArrayHasKey($row, $m);
            self::assertSame(
                array_fill_keys(MRT_price_category_keys(), null),
                $m[$row]
            );
        }
    }

    public function test_sanitize_rejects_negative_to_null(): void {
        $in = MRT_get_default_price_matrix();
        $in['single']['adult'] = -5;
        $out = MRT_sanitize_price_matrix($in);
        self::assertNull($out['single']['adult']);
    }

    public function test_sanitize_accepts_zero(): void {
        $in = MRT_get_default_price_matrix();
        $in['return']['child_4_15'] = 0;
        $out = MRT_sanitize_price_matrix($in);
        self::assertSame(0, $out['return']['child_4_15']);
    }

    public function test_sanitize_empty_string_to_null(): void {
        $in = MRT_get_default_price_matrix();
        $in['day']['student_senior'] = '';
        $out = MRT_sanitize_price_matrix($in);
        self::assertNull($out['day']['student_senior']);
    }

    public function test_sanitize_non_array_input_returns_defaults(): void {
        $out = MRT_sanitize_price_matrix('bad');
        self::assertEquals(MRT_get_default_price_matrix(), $out);
    }
}
