<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyNormalizeTest extends TestCase {

    protected function tearDown(): void {
        unset($GLOBALS['mrt_test_post_meta']);
        parent::tearDown();
    }

    public function test_total_duration_from_legs_sums(): void {
        $legs = [
            [
                'from_departure' => '08:00',
                'to_arrival' => '08:30',
            ],
            [
                'from_departure' => '09:00',
                'to_arrival' => '09:45',
            ],
        ];
        self::assertSame(75, MRT_normalize_total_duration_from_legs($legs));
    }

    public function test_total_duration_from_legs_null_if_segment_invalid(): void {
        $legs = [
            ['from_departure' => '10:00', 'to_arrival' => 'bad'],
        ];
        self::assertNull(MRT_normalize_total_duration_from_legs($legs));
    }

    public function test_total_duration_empty_legs_zero(): void {
        self::assertSame(0, MRT_normalize_total_duration_from_legs([]));
    }

    public function test_normalize_multi_leg_for_api_shape(): void {
        $item = [
            'connection_type' => 'transfer',
            'transfer_station_id' => 5,
            'legs' => [
                [
                    'service_id' => 1,
                    'from_departure' => '10:00',
                    'to_arrival' => '10:40',
                    'train_type' => 'diesel',
                ],
                [
                    'service_id' => 2,
                    'from_departure' => '11:00',
                    'to_arrival' => '11:30',
                    'train_type' => 'diesel',
                ],
            ],
        ];
        $out = MRT_normalize_multi_leg_for_api($item, '2026-04-01');
        self::assertSame('transfer', $out['connection_type']);
        self::assertSame(5, $out['transfer_station_id']);
        self::assertSame(70, $out['duration_minutes']);
        self::assertSame('10:00', $out['departure']);
        self::assertSame('11:30', $out['arrival']);
        self::assertSame('diesel', $out['train_type']);
        self::assertSame(1, $out['service_id']);
    }

    public function test_normalize_multi_leg_notice_unique(): void {
        $GLOBALS['mrt_test_post_meta'] = [
            '10|mrt_service_notice' => 'Delay',
            '11|mrt_service_notice' => 'Delay',
        ];
        $item = [
            'legs' => [
                ['service_id' => 10, 'from_departure' => '08:00', 'to_arrival' => '08:20', 'train_type' => 'a'],
                ['service_id' => 11, 'from_departure' => '08:30', 'to_arrival' => '09:00', 'train_type' => 'a'],
            ],
        ];
        $out = MRT_normalize_multi_leg_for_api($item, '2026-01-01');
        self::assertSame("Delay", $out['notice']);
    }

    public function test_flatten_wrapped_direct_connection(): void {
        $GLOBALS['mrt_test_post_meta'] = [
            '42|mrt_service_route_id' => 99,
            '42|mrt_service_end_station_id' => '',
            '42|mrt_direction' => 'dit',
        ];
        $item = [
            'connection_type' => 'direct',
            'legs' => [
                [
                    'service_id' => 42,
                    'train_type' => 'steam',
                    'from_departure' => '10:05',
                    'to_arrival' => '11:10',
                ],
            ],
        ];
        $flat = MRT_flatten_wrapped_direct_connection($item);
        self::assertNotNull($flat);
        self::assertSame(42, $flat['service_id']);
        self::assertSame('Post 42', $flat['service_name']);
        self::assertSame('Post 99', $flat['route_name']);
        self::assertSame('Dit', $flat['destination']);
        self::assertSame('10:05', $flat['from_departure']);
        self::assertSame('11:10', $flat['to_arrival']);
    }

    public function test_flatten_wrapped_returns_null_when_not_direct(): void {
        $item = [
            'connection_type' => 'transfer',
            'legs' => [
                ['service_id' => 1, 'from_departure' => '08:00', 'to_arrival' => '09:00', 'train_type' => ''],
            ],
        ];
        self::assertNull(MRT_flatten_wrapped_direct_connection($item));
    }

    public function test_flatten_wrapped_returns_null_when_first_leg_empty(): void {
        $item = [
            'connection_type' => 'direct',
            'legs' => [],
        ];
        self::assertNull(MRT_flatten_wrapped_direct_connection($item));
    }
}
