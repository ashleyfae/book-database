<?php
/**
 * Admin Notices
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Display admin notices
 */
function display_admin_notices() {

	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$code    = ! empty( $_GET['bdb_message'] ) ? urldecode( $_GET['bdb_message'] ) : '';
	$class   = 'success'; // updated or error
	$message = '';

	if ( empty( $code ) ) {
		return;
	}

	$objects = array(
		'author' => __( 'Author', 'book-database' ),
		'book'   => __( 'Book', 'book-database' ),
		'series' => __( 'Series', 'book-database' ),
		'term'   => __( 'Term', 'book-database' )
	);

	foreach ( $objects as $key => $name ) {
		switch ( $code ) {

			case $key . '_added' :
				$message = sprintf( __( '%s added', 'book-database' ), $name );
				break;

			case $key . '_updated' :
				$message = sprintf( __( '%s updated', 'book-database' ), $name );
				break;

			case $key . '_deleted' :
				$message = sprintf( __( '%s deleted', 'book-database' ), $name );
				break;

		}
	}

	if ( empty( $message ) ) {
		return;
	}
	?>
	<div class="notice notice-<?php echo esc_attr( sanitize_html_class( $class ) ); ?>">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
	<?php

}

add_action( 'admin_notices', __NAMESPACE__ . '\display_admin_notices' );