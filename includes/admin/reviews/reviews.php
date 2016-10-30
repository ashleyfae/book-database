<?php
/**
 * Review Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews Page
 *
 * Render the reviews page contents.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_page() {
	$default_reviews = bdb_review_views();
	$requested_view  = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'reviews';

	if ( array_key_exists( $requested_view, $default_reviews ) && function_exists( $default_reviews[ $requested_view ] ) ) {
		bdb_render_review_view( $requested_view, $default_reviews );
	} else {
		bdb_reviews_list();
	}
}

/**
 * Register the views for review management.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_review_views() {
	$views = array();

	return apply_filters( 'book-database/reviews/views', $views );
}

/**
 * Display List of Reviews
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_list() {

	include dirname( __FILE__ ) . '/class-review-table.php';

	$review_table = new BDB_Reviews_Table();
	$review_table->prepare_items();

	?>
	<div class="wrap">
		<h1>
			<?php printf( __( '%s Reviews', 'book-database' ), bdb_get_label_singular() ); ?>
			<a href="#TB_inline?width=640&inlineId=insert-review" class="thickbox page-title-action"><?php _e( 'Add New', 'book-database' ); ?></a>
		</h1>
		<?php do_action( 'book-database/reviews/table/top' ); ?>
		<form id="ubb-reviews-filter" method="GET" action="<?php echo esc_url( admin_url( 'edit.php?post_type=bdb_book&page=ubb-reviews' ) ); ?>">
			<?php
			$review_table->search_box( __( 'Search Reviews', 'book-database' ), 'ubb-reviews' );
			$review_table->display();
			?>
			<input type="hidden" name="post_type" value="bdb_book">
			<input type="hidden" name="page" value="ubb-reviews">
			<input type="hidden" name="view" value="reviews">
		</form>
		<?php do_action( 'book-database/reviews/table/bottom' ); ?>
	</div>
	<?php
}