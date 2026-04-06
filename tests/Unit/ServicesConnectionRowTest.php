<?php
/**
 * Tests for connection row helpers (inc/functions/services.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServicesConnectionRowTest extends TestCase {

    public function test_departure_at_from_prefers_from_departure(): void {
        $row = [
            'from_departure' => '10:15',
            'from_arrival' => '10:20',
        ];
        self::assertSame('10:15', MRT_connection_row_departure_at_from($row));
    }

    public function test_departure_at_from_falls_back_to_from_arrival(): void {
        $row = ['from_arrival' => '09:05'];
        self::assertSame('09:05', MRT_connection_row_departure_at_from($row));
    }

    public function test_departure_at_from_empty_when_missing_times(): void {
        self::assertSame('', MRT_connection_row_departure_at_from([]));
    }
}
