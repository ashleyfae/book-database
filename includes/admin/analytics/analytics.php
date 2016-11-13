<?php
/**
 * Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Analytics Page
 *
 * @since 1.0.0
 * @return void
 */
function bdb_analytics_page() {

	?>
	<div id="bookdb-review-analytics-wrap" class="wrap">
		<h1>
			<?php _e( 'Review Analytics', 'book-database' ); ?>
		</h1>
	</div>
	<?php

}