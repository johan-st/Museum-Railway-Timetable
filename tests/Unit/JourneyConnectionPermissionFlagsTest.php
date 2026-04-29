<?php
/**
 * Tests for passenger boarding/alighting flags in MRT_find_connections.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyConnectionPermissionFlagsTest extends TestCase {

    /** @var mixed */
    private $previous_wpdb;

    protected function setUp(): void {
        parent::setUp();
        $this->previous_wpdb = $GLOBALS['wpdb'];
        $GLOBALS['mrt_test_post_meta'] = [
            '10|mrt_service_timetable_id' => 20,
            '20|mrt_timetable_dates' => ['2026-06-01'],
        ];
        $GLOBALS['mrt_test_get_posts'] = static function(array $args): array {
            if (($args['post_type'] ?? '') === 'mrt_timetable') {
                return [20];
            }
            if (($args['post_type'] ?? '') === 'mrt_service') {
                return [10];
            }
            return [];
        };
    }

    protected function tearDown(): void {
        $GLOBALS['wpdb'] = $this->previous_wpdb;
        unset($GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_get_posts']);
        parent::tearDown();
    }

    public function test_find_connections_requires_pickup_at_origin_and_dropoff_at_destination(): void {
        $GLOBALS['wpdb'] = new class {
            public string $prefix = 'wp_';
            public string $last_error = '';
            public string $last_query = '';

            public function prepare(string $query, ...$args): string {
                unset($args);
                return $query;
            }

            public function get_results($query = null, $output = OBJECT): array {
                unset($output);
                $this->last_query = (string) $query;
                return [[
                    'service_post_id' => 10,
                    'from_departure' => '09:00',
                    'from_arrival' => null,
                    'from_sequence' => 1,
                    'to_arrival' => '10:00',
                    'to_departure' => null,
                    'to_sequence' => 2,
                ]];
            }
        };

        $connections = MRT_find_connections(1, 2, '2026-06-01');

        self::assertCount(1, $connections);
        self::assertStringContainsString('from_st.pickup_allowed = 1', $GLOBALS['wpdb']->last_query);
        self::assertStringContainsString('to_st.dropoff_allowed = 1', $GLOBALS['wpdb']->last_query);
        self::assertStringNotContainsString('from_st.pickup_allowed = 1 OR from_st.dropoff_allowed = 1', $GLOBALS['wpdb']->last_query);
        self::assertStringNotContainsString('to_st.pickup_allowed = 1 OR to_st.dropoff_allowed = 1', $GLOBALS['wpdb']->last_query);
    }
}
