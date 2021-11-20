<?php
/**
 * Reviews Admin Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Database\Reviews\ReviewsQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Models\Book;
use Book_Database\Models\Review;
use Book_Database\ValueObjects\Rating;

/**
 * Class Reviews_List_Table
 *
 * @package Book_Database
 */
class Reviews_List_Table extends List_Table {

	/**
	 * Reviews_List_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => 'review',
			'plural'   => 'reviews',
			'ajax'     => false
		) );

		$this->get_counts();

	}

	/**
	 * Get the base URL for this list table.
	 *
	 * @return string Base URL.
	 */
	public function get_base_url() {
		return get_reviews_admin_page_url();
	}

	/**
	 * Get available columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox">',
			'cover'          => esc_html__( 'Cover', 'book-database' ),
			'book_title'     => esc_html__( 'Book Title', 'book-database' ),
			'book_author'    => esc_html__( 'Book Author', 'book-database' ),
			'reviewer'       => esc_html__( 'Reviewer', 'book-database' ),
			'rating'         => esc_html__( 'Rating', 'book-database' ),
			'date_written'   => esc_html__( 'Date Written', 'book-database' ),
			'date_published' => esc_html__( 'Date Published', 'book-database' ),
		);

		return $columns;
	}

	/**
	 * Get the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'book_title'     => array( 'book.title', true ),
			'book_author'    => array( 'author.name', true ),
			'rating'         => array( 'log.rating', true ),
			'date_written'   => array( 'review.date_written', true ),
			'date_published' => array( 'review.date_published', true ),
		);
	}

	/**
	 * Get the counts
	 */
	public function get_counts() {
		$this->counts = array(
			'total' => count_reviews()
		);
	}

	/**
	 * Get the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Permanently Delete', 'book-database' )
		);
	}

	/**
	 * Get the primary column name
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'book_title';
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param object $object
	 *
	 * @return string
	 */
	public function column_cb( $object ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'] . '_id',
			$object->id
		);
	}

	/**
	 * Render the "Book Title" column.
	 *
	 * @param object $item
	 */
	public function column_book_title( $item ) {

		$review = new Review( $item );

		$edit_url = get_reviews_admin_page_url( array(
			'view'      => 'edit',
			'review_id' => $review->get_id()
		) );

		$actions = array(
			'edit'      => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'    => '<span class="trash"><a href="' . esc_url( get_delete_review_url( $review->get_id() ) ) . '" class="bdb-delete-item" data-object="' . esc_attr__( 'review', 'book-database' ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'review_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $review->get_id() ) . '</span>'
		);

		if ( ! user_can_edit_books() ) {
			unset( $actions['delete'] );
		}

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $item->book_title ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value  = '';
		$review = new Review( $item );
		$book   = new Book( array(
			'id'              => $item->book_id ?? 0,
			'cover_id'        => $item->book_cover_id ?? null,
			'title'           => $item->book_title ?? '',
			'pub_date'        => $item->book_pub_date ?? '',
			'series_position' => $item->series_position ?? ''
		) );

		switch ( $column_name ) {

			case 'cover' :
				if ( $book->get_cover_id() ) {
					$edit_url = get_reviews_admin_page_url( array(
						'view'      => 'edit',
						'review_id' => $review->get_id()
					) );

					$value = '<a href="' . esc_url( $edit_url ) . '">' . $book->get_cover( 'thumbnail' ) . '</a>';
				}
				break;

			case 'book_author' :
				if ( ! empty( $item->author_id ) ) {
					$author_names  = ! empty( $item->author_name ) ? explode( ',', $item->author_name ) : array();
					$author_ids    = ! empty( $item->author_id ) ? explode( ',', $item->author_id ) : array();
					$authors_array = array();

					foreach ( $author_names as $key => $author_name ) {
						$author_id       = isset( $author_ids[ $key ] ) ? absint( $author_ids[ $key ] ) : 0;
						$url             = ! empty( $author_id ) ? add_query_arg( 'author_id', urlencode( $author_id ), $this->get_base_url() ) : $this->get_base_url();
						$authors_array[] = '<a href="' . esc_url( $url ) . '">' . esc_html( trim( $author_name ) ) . '</a>';
					}

					$value = implode( ', ', $authors_array );
				} else {
					$value = '&ndash;';
				}
				break;

			case 'reviewer' :
				$user  = get_userdata( $review->get_user_id() );
				$name  = $user instanceof \WP_User ? $user->display_name : sprintf( __( 'ID #%d', 'book-database' ), $review->get_user_id() );
				$value = '<a href="' . esc_url( add_query_arg( 'user_id', urlencode( $review->get_user_id() ), $this->get_base_url() ) ) . '">' . esc_html( $name ) . '</a>';
				break;

			case 'rating' :
				if ( ! is_null( $item->rating ) ) {
					$rating = new Rating( $item->rating );

					$classes = array();

					if ( ! is_null( $item->rating ) ) {
						$classes[] = 'bdb-rating';
						$classes[] = 'bdb-' . $rating->format_html_class();
					}

					$classes = array_map( 'sanitize_html_class', $classes );

					$value = '<a href="' . esc_url( add_query_arg( 'rating', urlencode( $rating->round_rating() ), $this->get_base_url() ) ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" title="' . esc_attr( sprintf( __( 'All %s star reviews', 'book-database' ), $rating->round_rating() ) ) . '">' . $rating->format_html_stars() . '</a>';
				} else {
					$value = '&ndash;';
				}
				break;

			case 'date_written' :
				$value = $review->get_date_written( true );
				break;

			case 'date_published' :
				$value = $review->get_date_published() ? $review->get_date_published( true ) : '&ndash;';
				break;

		}

		return $value;

	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		esc_html_e( 'No reviews found.', 'book-database' );
	}

	/**
	 * Retrieve object data.
	 *
	 * @param bool $count Whether or not to get objects (false) or just count the total number (true).
	 *
	 * @return object[]|int
	 */
	public function get_object_data( $count = false ) {

		$args = array(
			'number'            => $this->per_page,
			'offset'            => $this->get_offset(),
			'orderby'           => sanitize_text_field( $this->get_request_var( 'orderby', 'review.id' ) ),
			'order'             => sanitize_text_field( $this->get_request_var( 'order', 'DESC' ) ),
			'author_query'      => array(),
			'book_query'        => array(),
			'reading_log_query' => array(),
			'series_query'      => array(),
			'count'             => $count
		);

		// Maybe filter by user ID.
		$user_id = $this->get_request_var( 'user_id' );
		if ( ! empty( $user_id ) ) {
			$args['user_id'] = absint( $user_id );
		}

		// Maybe add book title search.
		$book_title = $this->get_request_var( 'book_title' );
		if ( ! empty( $book_title ) ) {
			$args['book_query'][] = array(
				'field'    => 'title',
				'value'    => sanitize_text_field( $book_title ),
				'operator' => 'LIKE'
			);
		}

		// Maybe add book author search.
		$book_author = $this->get_request_var( 'book_author' );
		if ( ! empty( $book_author ) ) {
			$args['author_query'][] = array(
				'field'    => 'name',
				'value'    => sanitize_text_field( $book_author ),
				'operator' => 'LIKE'
			);
		}

		// Filter by author ID.
		$author_id = $this->get_request_var( 'author_id' );
		if ( ! empty( $author_id ) ) {
			$args['author_query'][] = array(
				'field' => 'id',
				'value' => absint( $author_id )
			);
		}

		// Filter by series ID.
		$series_id = $this->get_request_var( 'series_id' );
		if ( ! empty( $series_id ) ) {
			$args['book_query'][] = array(
				'field' => 'series_id',
				'value' => absint( $series_id ),
			);
		}

		// Maybe add series search.
		$series_name = $this->get_request_var( 'series_name' );
		if ( ! empty( $series_name ) ) {
			$args['series_query'][] = array(
				'field'    => 'name',
				'value'    => sanitize_text_field( $series_name ),
				'operator' => 'LIKE'
			);
		}

		// Maybe add rating search.
		$rating = $this->get_request_var( 'rating', '' );
		if ( '' !== $rating ) {
			$args['reading_log_query'][] = array(
				'field' => 'rating',
				'value' => floatval( $rating )
			);
		}

		$query = new ReviewsQuery();

		return $query->get_reviews( $args );

	}

	/**
	 * Process bulk actions
	 */
	public function process_bulk_actions() {

		// Bail if a nonce was not supplied.
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
			return;
		}

		$ids = wp_parse_id_list( (array) $this->get_request_var( 'review_id', false ) );

		// Bail if no IDs
		if ( empty( $ids ) ) {
			return;
		}

		try {

			foreach ( $ids as $review_id ) {

				switch ( $this->current_action() ) {

					case 'delete' :
						delete_review( $review_id );
						break;

				}

			}

			$this->show_admin_notice( $this->current_action(), count( $ids ) );

		} catch ( Exception $e ) {
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html( $e->getMessage() ); ?></p>
			</div>
			<?php
		}

	}

	/**
	 * Show an admin notice
	 *
	 * @param string $action
	 * @param int    $number
	 * @param string $class
	 */
	private function show_admin_notice( $action, $number = 1, $class = 'success' ) {

		$message = '';

		switch ( $action ) {
			case 'delete' :
				$message = _n( '1 review deleted.', sprintf( '%d reviews deleted', $number ), $number, 'book-database' );
				break;
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

	/**
	 * Show the search field.
	 *
	 * Adds separate search boxes for each type
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 */
	public function search_box( $text, $input_id ) {

		$orderby  = $this->get_request_var( 'orderby' );
		$order    = $this->get_request_var( 'order' );
		$input_id = $input_id . '-search-input';

		if ( ! empty( $orderby ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
		}

		if ( ! empty( $order ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}

		$title  = $this->get_request_var( 'book_title' );
		$author = $this->get_request_var( 'book_author' );
		$rating = $this->get_request_var( 'rating', '' );
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-title"><?php esc_html_e( 'Search by book title', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-title" name="book_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Book title', 'book-database' ); ?>">

			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-author"><?php esc_html_e( 'Search by author name', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-author" name="book_author" value="<?php echo esc_attr( $author ); ?>" placeholder="<?php esc_attr_e( 'Author name', 'book-database' ); ?>">

			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-rating"><?php esc_html_e( 'Search by rating', 'book-database' ); ?></label>
			<select id="<?php echo esc_attr( $input_id ); ?>-rating" name="rating">
				<option value="" <?php selected( $rating, '' ); ?>><?php _e( 'Any Rating', 'book-database' ); ?></option>
				<?php
				foreach ( get_available_ratings() as $rating_key => $rating_name ) {
					?>
					<option value="<?php echo esc_attr( $rating_key ); ?>" <?php selected( $rating, $rating_key ); ?>><?php echo esc_html( $rating_name ); ?></option>
					<?php
				}
				?>
			</select>

			<input type="submit" class="button" value="<?php echo esc_attr( $text ); ?>">
		</p>
		<?php

	}

	/**
	 * Render extra content between bulk actions and pagination
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' != $which ) {
			return;
		}

		$user_ids = get_reviewer_user_ids();

		// Bail if we only have one reviewer.
		if ( 1 === count( $user_ids ) ) {
			return;
		}

		$user_filter = $this->get_request_var( 'user_id' );
		?>
		<div class="alignleft actions">
			<label for="bdb-filter-by-user-id" class="screen-reader-text"><?php _e( 'Filter by reviewer', 'book-database' ); ?></label>
			<select id="bdb-filter-by-user-id" name="user_id">
				<option value="" <?php selected( empty( $owned_filter ) ); ?>><?php _e( 'All Reviewers', 'book-database' ); ?></option>
				<?php
				foreach ( $user_ids as $user_id ) {
					$user = get_userdata( $user_id );

					if ( empty( $user ) ) {
						continue;
					}
					?>
					<option value="<?php echo esc_attr( $user_id ); ?>" <?php selected( $user_filter, $user_id ); ?>><?php echo esc_html( $user->display_name ); ?></option>
					<?php
				}
				?>
			</select>

			<input type="submit" name="filter_action" id="bdb-review-query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'book-database' ); ?>">
		</div>
		<?php

	}

}
