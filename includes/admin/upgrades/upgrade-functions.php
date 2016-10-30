<?php
/**
 * Upgrade Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

/**
 * Perform automatic database upgrades when necessary.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_do_automatic_upgrades() {

	$did_upgrade = false;
	$bdb_version = preg_replace( '/[^0-9.].*/', '', get_option( 'bdb_version' ) );

	if ( version_compare( $bdb_version, BDB_VERSION, '<' ) ) {
		// Let us know that an upgrade has happened
		$did_upgrade = true;
	}

	if ( $did_upgrade ) {

		// If it is a major version, send to what's new page
		if ( substr_count( BDB_VERSION, '.' ) < 2 ) {
			set_transient( '_bdb_activation_redirect', true, 30 );
		}

		update_option( 'bdb_version', preg_replace( '/[^0-9.].*/', '', BDB_VERSION ) );

		// Send a check in. Note: this only sends if data tracking has been enabled
		// @todo
		//$tracking = new BDB_Tracking;
		//$tracking->send_checkin( false, true );

	}

}

add_action( 'admin_init', 'bdb_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.0.0
 * @return void
 */
function bdb_show_upgrade_notices() {
	// Don't show notices on the upgrades page.
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'ubb-upgrades' ) {
		return;
	}

	$bdb_version = get_option( 'bdb_version' );

	if ( ! $bdb_version ) {
		$bdb_version = BDB_VERSION;
	}

	$bdb_version = preg_replace( '/[^0-9.].*/', '', $bdb_version );

	$resume_upgrade = bdb_maybe_resume_upgrade();

	if ( ! empty( $resume_upgrade ) ) {

		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		printf(
			'<div class="error"><p>' . __( 'Ultimate Book Blogger needs to complete a database upgrade that was previously started, click <a href="%s">here</a> to resume the upgrade.', 'book-database' ) . '</p></div>',
			esc_url( $resume_url )
		);

	} else {

		if ( version_compare( $bdb_version, '4.0', '<' ) ) {
			printf(
				'<div class="updated"><p>' . __( 'Ultimate Book Blogger needs to upgrade the customer database, click %shere%s to start the upgrade.', 'book-database' ) . '</p></div>',
				'<a href="' . esc_url( admin_url( 'index.php?page=ubb-upgrades&ubb-upgrade=upgrade_book_review_db' ) ) . '">',
				'</a>'
			);
		}

		/*
		 *  NOTICE:
		 *
		 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
		 *  /includes/install.php @ Appox Line 102
		 *
		 */
		// End 'Stepped' upgrade process notices

	}
}

add_action( 'admin_notices', 'bdb_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.0.0
 * @return void
 */
function bdb_trigger_upgrades() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_die( __( 'You do not have permission to do UBB upgrades.', 'book-database' ), __( 'Error', 'book-database' ), array( 'response' => 403 ) );
	}

	$bdb_version = get_option( 'bdb_version' );

	if ( ! $bdb_version ) {
		$bdb_version = '3.0.0';
		add_option( 'bdb_version', $bdb_version );
	}

}

add_action( 'wp_ajax_bdb_trigger_upgrades', 'bdb_trigger_upgrades' );

/**
 * Maybe Resume Upgrade
 *
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 *
 * @since 1.0.0
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 */
function bdb_maybe_resume_upgrade() {
	$doing_upgrade = get_option( 'bdb_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;
}

/**
 * Set Upgrade Complete
 *
 * Add an upgrade actions to the completed upgrades array.
 *
 * @param string $upgrade_action
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = bdb_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'bdb_completed_upgrades', $completed_upgrades );

}

/**
 * @todo
 *
 * @since 1.0.0
 */
function bdb_v4_upgrade_book_reviews_db() {

	global $wpdb;

	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_die( __( 'You do not have permission to do UBB upgrades.', 'book-database' ), __( 'Error', 'book-database' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! bdb_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	if ( ! get_option( 'bdb_reviews_db_version' ) ) {
		// Create the customers database on the first run
		@book_database()->reviews->create_table();
	}

	/**
	 * @todo Continue from EDD line 622
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/admin/upgrades/upgrade-functions.php
	 */

}

add_action( 'bdb_upgrade_book_reviews_db', 'bdb_v4_upgrade_book_reviews_db' );