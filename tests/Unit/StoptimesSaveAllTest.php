<?php
/**
 * Tests for stop-time bulk save validation helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StoptimesSaveAllTest extends TestCase {

    public function test_prepare_stoptimes_rejects_invalid_time(): void {
        $result = MRT_prepare_stoptimes_for_save_all([[
            'station_id' => 1,
            'stops_here' => '1',
            'arrival' => '25:00',
            'departure' => '09:00',
        ]]);

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('invalid_time', $result->get_error_code());
    }

    public function test_prepare_stoptimes_omits_non_stopping_rows(): void {
        $result = MRT_prepare_stoptimes_for_save_all([
            [
                'station_id' => 1,
                'stops_here' => '0',
                'arrival' => 'bad',
            ],
            [
                'station_id' => 2,
                'stops_here' => '1',
                'arrival' => '09:00',
                'departure' => '',
                'pickup' => '1',
                'dropoff' => '1',
            ],
        ]);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertSame(2, $result[0]['station_post_id']);
        self::assertSame(1, $result[0]['stop_sequence']);
    }

    public function test_insert_prepared_stoptime_reports_failure(): void {
        $wpdb = new class {
            public string $prefix = 'wp_';
            public string $last_error = 'insert failed';

            public function insert($table, $data, $format) {
                unset($table, $data, $format);
                return false;
            }
        };
        $row = [
            'station_post_id' => 2,
            'stop_sequence' => 1,
            'arrival_time' => '09:00',
            'departure_time' => null,
            'pickup_allowed' => 1,
            'dropoff_allowed' => 1,
        ];

        self::assertFalse(MRT_insert_prepared_stoptime_for_save_all($wpdb, $row, 10));
    }

    public function test_insert_prepared_stoptime_returns_new_row_id(): void {
        $wpdb = new class {
            public string $prefix = 'wp_';
            public string $last_error = '';
            public int $insert_id = 88;

            public function insert($table, $data, $format) {
                unset($table, $data, $format);
                return 1;
            }
        };
        $row = [
            'station_post_id' => 2,
            'stop_sequence' => 1,
            'arrival_time' => '09:00',
            'departure_time' => null,
            'pickup_allowed' => 1,
            'dropoff_allowed' => 1,
        ];

        self::assertSame(88, MRT_insert_prepared_stoptime_for_save_all($wpdb, $row, 10));
    }

    public function test_delete_old_stoptimes_keeps_replacement_ids(): void {
        $wpdb = new class {
            public string $prefix = 'wp_';
            public string $last_error = '';
            public string $last_query = '';

            public function prepare(string $query, ...$args): string {
                unset($args);
                return $query;
            }

            public function query(string $query) {
                $this->last_query = $query;
                return 1;
            }
        };

        self::assertTrue(MRT_delete_old_stoptimes_after_save_all($wpdb, 10, [88, 89]));
        self::assertStringContainsString('service_post_id = %d', $wpdb->last_query);
        self::assertStringContainsString('id NOT IN (88,89)', $wpdb->last_query);
    }
}
