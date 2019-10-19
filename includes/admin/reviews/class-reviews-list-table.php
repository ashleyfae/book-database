<?php
/**
 * Reviews Admin Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reviews_List_Table
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
			'date_written'   => array( 'date_written', true ),
			'date_published' => array( 'date_published', true ),
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
	 * Render the "Book Title" column.
	 *
	 * @param Review $item
	 */
	public function column_book_title( $item ) {

		$edit_url = get_reviews_admin_page_url( array(
			'view'      => 'edit',
			'review_id' => $item->get_id()
		) );

		$book = get_book( $item->get_book_id() );

		$actions = array(
			'edit'      => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'    => '<span class="trash"><a href="' . esc_url( get_delete_review_url( $item->get_id() ) ) . '" class="bdb-delete-item" data-object="' . esc_attr__( 'review', 'book-database' ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'review_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $item->get_id() ) . '</span>'
		);

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $book->get_title() ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param Review $item
	 * @param string $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value = '';

		$authors = get_attached_book_authors( $item->get_book_id(), array( 'fields' => 'name' ) );

		switch ( $column_name ) {

			case 'book_author' :
				$author_links = array();

				foreach ( $authors as $author_name ) {
					$link           = add_query_arg( 'book_author', urlencode( $author_name ), $this->get_base_url() );
					$author_links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $author_name ) . '</a>';
				}
				$value = implode( ', ', $author_links );
				break;

			case 'reviewer' :
				$user  = get_userdata( $item->get_user_id() );
				$name  = $user instanceof \WP_User ? $user->display_name : sprintf( __( 'ID #%d', 'book-database' ), $item->get_user_id() );
				$value = '<a href="' . esc_url( add_query_arg( 'user_id', urlencode( $item->get_user_id() ), $this->get_base_url() ) ) . '">' . esc_html( $name ) . '</a>';
				break;

			case 'rating' :
				$reading_log = get_reading_log_by( 'review_id', $item->get_id() );

				if ( $reading_log instanceof Reading_Log && ! is_null( $reading_log->get_rating() ) ) {
					$rating = new Rating( $reading_log->get_rating() );
					$value  = '<a href="' . esc_url( add_query_arg( 'rating', urlencode( $rating->round_rating() ), $this->get_base_url() ) ) . '">' . $rating->format_html_stars() . '</a>';
				} else {
					$value = '&ndash;';
				}
				break;

			case 'date_written' :
				$value = $item->get_date_written( true );
				break;

			case 'date_published' :
				$value = $item->get_date_published() ? $item->get_date_published( true ) : '&ndash;';
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
	 * @return array|int
	 */
	public function get_object_data( $count = false ) {

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $this->get_offset(),
			'orderby' => sanitize_text_field( $this->get_request_var( 'orderby', 'id' ) ),
			'order'   => sanitize_text_field( $this->get_request_var( 'order', 'DESC' ) ),
		);

		// Maybe filter by user ID.
		$user_id = $this->get_request_var( 'user_id' );
		if ( ! empty( $user_id ) ) {
			$args['user_id'] = absint( $user_id );
		}

		// Maybe add search.
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
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
				'field' => 'search',
				'terms' => array( sanitize_text_field( $book_author ) ),
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

		if ( $count ) {
			return count_reviews( $args );
		} else {
			return get_reviews( $args );
		}

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