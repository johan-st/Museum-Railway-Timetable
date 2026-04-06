<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyAjaxParseTest extends TestCase {

    /** @var array<string, mixed> */
    private $postBackup = [];

    protected function setUp(): void {
        parent::setUp();
        $this->postBackup = $_POST;
        $_POST = [];
    }

    protected function tearDown(): void {
        $_POST = $this->postBackup;
        parent::tearDown();
    }

    public function test_parse_stations_pair_success(): void {
        $_POST['from_station'] = '12';
        $_POST['to_station'] = '34';
        $out = MRT_journey_ajax_parse_stations_pair();
        self::assertIsArray($out);
        self::assertSame(12, $out['from']);
        self::assertSame(34, $out['to']);
    }

    public function test_parse_stations_pair_missing_station(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '0';
        $out = MRT_journey_ajax_parse_stations_pair();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_stations', $out->get_error_code());
    }

    public function test_parse_stations_pair_same_station(): void {
        $_POST['from_station'] = '5';
        $_POST['to_station'] = '5';
        $out = MRT_journey_ajax_parse_stations_pair();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_same', $out->get_error_code());
    }

    public function test_parse_from_to_date_success(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['date'] = '2026-06-15';
        $out = MRT_journey_ajax_parse_from_to_date();
        self::assertIsArray($out);
        self::assertSame('2026-06-15', $out['date']);
    }

    public function test_parse_from_to_date_invalid_date(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['date'] = '15-06-2026';
        $out = MRT_journey_ajax_parse_from_to_date();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_date', $out->get_error_code());
    }

    public function test_parse_trip_search_single(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['date'] = '2026-01-20';
        $_POST['trip_type'] = 'single';
        $out = MRT_journey_ajax_parse_trip_search_params();
        self::assertIsArray($out);
        self::assertSame('single', $out['trip_type']);
        self::assertArrayNotHasKey('outbound_arrival', $out);
    }

    public function test_parse_trip_search_return_requires_arrival(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['date'] = '2026-01-20';
        $_POST['trip_type'] = 'return';
        $_POST['outbound_arrival'] = '';
        $out = MRT_journey_ajax_parse_trip_search_params();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_return_arrival', $out->get_error_code());
    }

    public function test_parse_trip_search_return_success(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['date'] = '2026-01-20';
        $_POST['trip_type'] = 'return';
        $_POST['outbound_arrival'] = '14:30';
        $_POST['outbound_service_id'] = '7';
        $_POST['min_turnaround_minutes'] = '15';
        $out = MRT_journey_ajax_parse_trip_search_params();
        self::assertIsArray($out);
        self::assertSame('return', $out['trip_type']);
        self::assertSame('14:30', $out['outbound_arrival']);
        self::assertSame(7, $out['outbound_service_id']);
        self::assertSame(15, $out['min_turnaround_minutes']);
    }

    public function test_parse_calendar_month_params(): void {
        $_POST['from_station'] = '3';
        $_POST['to_station'] = '4';
        $_POST['year'] = '2026';
        $_POST['month'] = '4';
        $out = MRT_journey_ajax_parse_calendar_month_params();
        self::assertIsArray($out);
        self::assertSame(2026, $out['year']);
        self::assertSame(4, $out['month']);
    }

    public function test_parse_calendar_month_invalid_range(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['year'] = '2026';
        $_POST['month'] = '13';
        $out = MRT_journey_ajax_parse_calendar_month_params();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_calendar_month_range', $out->get_error_code());
    }

    public function test_parse_connection_detail_params(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['service_id'] = '88';
        $out = MRT_journey_ajax_parse_connection_detail_params();
        self::assertIsArray($out);
        self::assertSame(88, $out['service_id']);
    }

    public function test_parse_connection_detail_invalid_service(): void {
        $_POST['from_station'] = '1';
        $_POST['to_station'] = '2';
        $_POST['service_id'] = '0';
        $out = MRT_journey_ajax_parse_connection_detail_params();
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_service', $out->get_error_code());
    }
}
