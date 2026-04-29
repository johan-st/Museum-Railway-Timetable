<?php
/**
 * Admin meta box hooks (init, use_block_editor, edit_form_after_title, admin_head, columns)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Remove editor support for all CPTs (only title needed, fields handled by meta boxes)
 */
add_action(
	'init',
	function () {
		remove_post_type_support( 'mrt_station', 'editor' );
		remove_post_type_support( 'mrt_service', 'editor' );
		remove_post_type_support( 'mrt_route', 'editor' );
	},
	20
);

/**
 * Explicitly disable Gutenberg/block editor for all CPTs
 */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use_block_editor, $post_type ) {
		if ( in_array( $post_type, array( 'mrt_station', 'mrt_service', 'mrt_route' ), true ) ) {
			return false;
		}
		return $use_block_editor;
	},
	10,
	2
);

/**
 * Add help text for Route title field
 */
add_action(
	'edit_form_after_title',
	function ( $post ) {
		if ( $post->post_type === 'mrt_route' ) {
			echo '<p class="description mrt-mb-1">';
			esc_html_e( 'Example route name: "Hultsfred - Västervik" or "Main Line".', 'museum-railway-timetable' );
			echo '</p>';
		}
	}
);

/**
 * Remove description field from train type taxonomy (only name needed)
 * CSS in admin-meta-boxes.css: .taxonomy-mrt_train_type .term-description-wrap
 */

/**
 * Hide description column in taxonomy list table
 */
add_filter(
	'manage_edit-mrt_train_type_columns',
	function ( $columns ) {
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}
		return $columns;
	}
);
