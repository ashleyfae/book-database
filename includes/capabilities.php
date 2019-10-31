<?php
/**
 * Capabilities
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get all capabilities added by this plugin
 *
 * @since 1.0
 * @return array
 */
function get_book_capabilities() {
	return array(
		'bdb_view_books',
		'bdb_edit_books',
		'bdb_manage_book_settings'
	);
}

/**
 * Returns true if the user can view books in the admin area
 *
 * @param int $user_id
 *
 * @return bool
 */
function user_can_view_books( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$can_view = user_can( $user_id, 'bdb_view_books' );

	// Admins can always view.
	if ( ! $can_view && user_can( $user_id, 'manage_options' ) ) {
		$can_view = true;
	}

	/**
	 * Filters whether or not a user is allowed to view books in the admin area.
	 *
	 * @param bool $can_view
	 * @param int  $user_id
	 */
	return apply_filters( 'book-database/capabilities/view-books', $can_view, $user_id );

}

/**
 * Returns true if the user can edit books in the admin area
 *
 * @param int $user_id
 *
 * @return bool
 */
function user_can_edit_books( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$can_edit = user_can( $user_id, 'bdb_edit_books' );

	// Admins can always edit.
	if ( ! $can_edit && user_can( $user_id, 'manage_options' ) ) {
		$can_edit = true;
	}

	/**
	 * Filters whether or not a user is allowed to edit books in the admin area.
	 *
	 * @param bool $can_view
	 * @param int  $user_id
	 */
	return apply_filters( 'book-database/capabilities/edit-books', $can_edit, $user_id );

}

/**
 * Returns true if the user can manage the BDB settings
 *
 * @param int $user_id
 *
 * @return bool
 */
function user_can_manage_book_settings( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$can_manage = user_can( $user_id, 'bdb_manage_book_settings' );

	// Admins can always edit.
	if ( ! $can_manage && user_can( $user_id, 'manage_options' ) ) {
		$can_manage = true;
	}

	/**
	 * Filters whether or not a user is allowed to manage BDB settings.
	 *
	 * @param bool $can_view
	 * @param int  $user_id
	 */
	return apply_filters( 'book-database/capabilities/manage-book-settings', $can_manage, $user_id );

}