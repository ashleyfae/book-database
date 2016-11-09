<?php

/**
 * Review Table Class
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

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class BDB_Reviews_Table
 *
 * Renders the book reviews table.
 *
 * @since 1.0.0
 */
class BDB_Reviews_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $per_page = 30;

	/**
	 * Number of reviews found
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $count = 0;

	/**
	 * Total number of reviews
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @access public
	 * @since  1.0.0
	 */
	public $args = array();

	/**
	 * BDB_Reviews_Table constructor.
	 *
	 * @see    WP_List_Table::__construct()
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		global $status, $page;

		parent::__construct( array(
			'singular' => __( 'Review', 'book-database' ),
			'plural'   => __( 'Reviews', 'book-database' ),
			'ajax'     => false
		) );

	}

	/**
	 * Show the Search Field
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '">';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '">';
		}

		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>">
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * Renders most of the columns in the list table.
	 *
	 * @param object $item        Contains all the data of the customers.
	 * @param string $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string Column name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'post_id' :
				if ( empty( $item['post_id'] ) ) {
					$value = __( 'n/a', 'book-database' );
				} else {
					$edit_link = get_edit_post_link( $item['post_id'] );
					$value     = sprintf(
						__( '%s %s(Edit)%s', 'book-database' ),
						get_the_title( $item['post_id'] ),
						'<a href="' . esc_url( $edit_link ) . '">',
						'</a>'
					);
				}
				break;

			case 'book_title' :
				$value = $title = $item['book_title'];
				if ( $title && $item['book_id'] ) {
					$url   = add_query_arg( array( 'book_id' => urlencode( $item['book_id'] ) ), bdb_get_admin_page_reviews() );
					$value = '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
				}
				break;

			case 'rating' :
				$value = $rating = $item['rating'];
				if ( $rating ) {
					$rating_obj = new BDB_Rating( $rating );
					$url        = add_query_arg( array( 'rating' => urlencode( $item['rating'] ) ), bdb_get_admin_page_reviews() );
					$value      = '<a href="' . esc_url( $url ) . '">' . $rating_obj->format( 'html_stars' ) . '</a>';
				}
				break;

			case 'date' :
				$value = date_i18n( get_option( 'date_format' ), strtotime( $item['date'] ) );
				break;

			default :
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;

		}

		return apply_filters( 'book-database/reviews-table/column/' . $column_name, $value, $item['ID'] );

	}

	/**
	 * Render Column Name
	 *
	 * @param array $item Contains all the data of the reviews.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function column_id( $item ) {
		$name    = '#' . $item['ID'];
		$actions = array(
			'edit'   => '<a href="' . esc_url( bdb_get_admin_page_edit_review( $item['ID'] ) ) . '">' . __( 'Edit', 'book-database' ) . '</a>',
			'delete' => '<a href="' . admin_url( 'edit.php?post_type=bdb_book&page=ubb-reviews&view=delete&id=' . $item['ID'] ) . '">' . __( 'Delete', 'book-database' ) . '</a>'
			// @todo
		);

		return $name . $this->row_actions( $actions );
	}

	/**
	 * Get Columns
	 *
	 * Retrieves the column IDs and names.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => __( 'ID', 'book-database' ),
			'book_title' => __( 'Book Title', 'book-database' ),
			'author'     => __( 'Author', 'book-database' ),
			'rating'     => __( 'Rating', 'book-database' ),
			'date'       => __( 'Date', 'book-database' )
		);

		return apply_filters( 'book-database/reviews-table/review-columns', $columns );
	}

	/**
	 * Get the sortable columns.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'ID'     => array( 'ID', true ),
			'rating' => array( 'rating', true ),
			'date'   => array( 'date', true )
		);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|string Search query or false if none.
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Build all the reviews data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of review data.
	 */
	public function reviews_data() {

		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'ID';

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby
		);

		// Filter by book title.
		if ( isset( $_GET['book_id'] ) ) {
			$args['book_id'] = absint( $_GET['book_id'] );
		}

		// Filter by rating.
		if ( isset( $_GET['rating'] ) ) {
			$args['rating'] = sanitize_text_field( $_GET['rating'] );
		}

		if ( is_numeric( $search ) ) {
			$args['ID'] = $search;
		} elseif ( strpos( $search, 'user:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'user:', '', $search ) );
		}

		$this->args = $args;
		$reviews    = book_database()->reviews->get_reviews( $args );

		if ( $reviews ) {

			foreach ( $reviews as $review ) {

				$review_obj = new BDB_Review( $review->ID );
				$user_id    = ! empty( $review->user_id ) ? intval( $review->user_id ) : 0;
				$book       = $review->book_id ? new BDB_Book( $review->book_id ) : false;

				$data[] = array(
					'ID'         => $review->ID,
					'book_id'    => $review->book_id,
					'post_id'    => $review->post_id,
					'url'        => $review->url,
					'book_title' => $book ? $book->get_title() : false,
					'author'     => $book ? $book->get_author_names() : false,
					'user_id'    => $user_id,
					'date'       => $review->date_added,
					'rating'     => $review_obj->get_rating()
				);

			}

		}

		return $data;

	}

	/**
	 * Prepare Items
	 *
	 * Setup the final data for the table.
	 *
	 * @uses   BDB_Reviews_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   BDB_Reviews_Table::reviews_data()
	 * @uses   bdb_count_total_reviews()
	 * @uses   WP_List_Table::set_pagination_args()
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reviews_data();

		$this->total = bdb_count_total_reviews( $this->args );

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page )
		) );

	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		printf(
			__( 'No reviews found. You can %sadd one via a blog post%s or add one from a third party site by clicking "Add New" above.', 'book-database' ),
			'<a href="' . esc_url( admin_url( 'post-new.php' ) ) . '">',
			'</a>'
		);
	}

}