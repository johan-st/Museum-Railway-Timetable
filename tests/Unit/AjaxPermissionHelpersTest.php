<?php
/**
 * Tests for target-specific AJAX permission helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AjaxPermissionHelpersTest extends TestCase {

    protected function tearDown(): void {
        unset($GLOBALS['mrt_test_current_user_can'], $GLOBALS['mrt_test_current_user_can_calls']);
        parent::tearDown();
    }

    public function test_edit_permission_helper_checks_specific_post(): void {
        $GLOBALS['mrt_test_current_user_can'] = true;

        MRT_verify_ajax_edit_post_permission(123);

        self::assertSame([['edit_post', [123]]], $GLOBALS['mrt_test_current_user_can_calls']);
    }

    public function test_delete_permission_helper_checks_specific_post(): void {
        $GLOBALS['mrt_test_current_user_can'] = true;

        MRT_verify_ajax_delete_post_permission(456);

        self::assertSame([['delete_post', [456]]], $GLOBALS['mrt_test_current_user_can_calls']);
    }
}
