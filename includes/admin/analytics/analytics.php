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

		<div class="bookdb-date-range"></div> <!-- @todo -->

		<section id="bookdb-review-analytics-metrics">

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Reviews', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<h2 id="number-reviews"></h2>
					<p class="bottom-text" id="number-reviews-compare"><span></span></p>
				</div>
			</div>

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Pages Read', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<h2 id="pages"></h2>
					<p class="bottom-text" id="pages-compare"><span></span></p>
				</div>
			</div>

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Average Rating', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<h2 id="avg-rating"></h2>
					<p class="bottom-text" id="avg-rating-compare"><span></span></p>
				</div>
			</div>

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Rating Breakdown', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<div id="rating-breakdown"></div> <!-- @todo Table of each rating with number -->
				</div>
			</div>

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Genres', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<div id="genre-breakdown"></div> <!-- @todo Table of each genre with number -->
				</div>
			</div>

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Publishers', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<div id="publisher-breakdown"></div> <!-- @todo Table of each genre with number -->
				</div>
			</div>

		</section>

		<section id="bookdb-list-of-reviews">

			<div class="bookdb-metric">
				<div class="bookdb-metric-inner">
					<p class="top-text"><?php _e( 'Books Reviewed', 'book-database' ); ?></p>
					<div class="bookdb-loading"></div>
					<div id="book-list"></div>
				</div>
			</div>

		</section>
	</div>
	<?php

}