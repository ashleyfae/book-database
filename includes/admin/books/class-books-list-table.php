<?php
/**
 * Books Admin Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Books_List_Table
 * @package Book_Database
 */
class Books_List_Table extends List_Table {

	/**
	 * Books_List_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => 'book',
			'plural'   => 'books',
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
		return get_books_admin_page_url();
	}

	/**
	 * Get available columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox">',
			'cover'    => esc_html__( 'Cover', 'book-database' ),
			'title'    => esc_html__( 'Title', 'book-database' ),
			'author'   => esc_html__( 'Author', 'book-database' ),
			'series'   => esc_html__( 'Series', 'book-database' ),
			'pub_date' => esc_html__( 'Publication Date', 'book-database' ),
			'rating'   => esc_html__( 'Rating', 'book-database' )
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
			'title'    => array( 'title', true ),
			'author'   => array( 'author', true ),
			'series'   => array( 'series', true ),
			'pub_date' => array( 'pub_date', true ),
			'rating'   => array( 'rating', true ),
		);
	}

	/**
	 * Get the counts
	 */
	public function get_counts() {
		$this->counts = array(
			'total' => count_books()
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
		return 'cover';
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param Object $object
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
	 * Render the "Title" column.
	 *
	 * @param Object $item
	 */
	public function column_title( $item ) {

		$edit_url = get_books_admin_page_url( array(
			'view'    => 'edit',
			'book_id' => $item->id
		) );

		$actions = array(
			'edit'    => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'  => '<span class="trash"><a href="' . esc_url( get_delete_book_url( $item->id ) ) . '" class="bdb-delete-item" data-object="' . esc_attr__( 'book', 'book-database' ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'book_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $item->id ) . '</span>'
		);

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $item->title ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param Object $item
	 * @param string $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value = '';
		$book  = new Book( $item );

		$edit_url = get_books_admin_page_url( array(
			'view'    => 'edit',
			'book_id' => $book->get_id()
		) );

		switch ( $column_name ) {

			case 'cover' :
				if ( $book->get_cover_id() ) {
					$value = '<a href="' . esc_url( $edit_url ) . '">' . $book->get_cover( 'thumbnail' ) . '</a>';
				}
				break;

			case 'author' :
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

			case 'series' :
				if ( ! empty( $item->series_id ) && ! empty( $item->series_name ) ) {
					$name  = isset( $item->series_position ) ? sprintf( '%s #%s', $item->series_name, $item->series_position ) : $item->series_name;
					$value = '<a href="' . esc_url( add_query_arg( 'series_id', urlencode( $item->series_id ), $this->get_base_url() ) ) . '">' . esc_html( $name ) . '</a>';
				} else {
					$value = '&ndash;';
				}
				break;

			case 'pub_date' :
				$value = $book->get_pub_date( true );
				break;

			case 'rating' :
				if ( isset( $item->avg_rating ) ) {
					$rating         = new Rating( $item->avg_rating );
					$rounded_rating = $rating->round_rating();
					$value          = '<a href="' . esc_url( add_query_arg( 'rating', urlencode( $rounded_rating ), $this->get_base_url() ) ) . '" title="' . esc_attr( sprintf( __( 'All %s star books', 'book-database' ), $rounded_rating ) ) . '">' . $rating->format_html_stars() . '</a>';
				} else {
					$value = '&ndash;';
				}
				break;

		}

		return $value;

	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		esc_html_e( 'No books found.', 'book-database' );
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
			'id__not_in' => array(),
			'number'     => $this->per_page,
			'offset'     => $this->get_offset(),
			'orderby'    => sanitize_text_field( $this->get_request_var( 'orderby', 'book.id' ) ),
			'order'      => sanitize_text_field( $this->get_request_var( 'order', 'DESC' ) ),
			'book_query' => array(),
			'tax_query'  => array(),
			'count'      => $count
		);

		// Filter by author ID.
		$author_id = $this->get_request_var( 'author_id' );
		if ( ! empty( $author_id ) ) {
			$args['author_query'][] = array(
				'field' => 'id',
				'terms' => absint( $author_id )
			);
		}

		// Filter by term ID.
		$term_id = $this->get_request_var( 'term_id' );
		if ( ! empty( $term_id ) ) {
			$args['tax_query'][] = array(
				'field' => 'id',
				'terms' => absint( $term_id )
			);
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

		// Maybe add author name search.
		$author_name = $this->get_request_var( 'book_author' );
		if ( ! empty( $author_name ) ) {
			$args['author_query'][] = array(
				'field'    => 'name',
				'value'    => sanitize_text_field( $author_name ),
				'operator' => 'LIKE'
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

		// Maybe add ISBN search.
		$isbn = $this->get_request_var( 'isbn' );
		if ( ! empty( $isbn ) ) {
			$args['edition_query'][] = array(
				'field'    => 'isbn',
				'value'    => sanitize_text_field( $isbn ),
				'operator' => 'LIKE'
			);
		}

		// Filter by format.
		$format = $this->get_request_var( 'format_filter' );
		if ( ! empty( $format ) ) {
			$args['edition_query'][] = array(
				'field' => 'format',
				'value' => sanitize_text_field( $format )
			);
		}

		// Filter by read / unread status.
		$read_status = $this->get_request_var( 'read_status_filter' );
		if ( ! empty( $read_status ) ) {
			switch ( $read_status ) {
				case 'reading' :
					$args['reading_log_query'][] = array(
						'field' => 'date_started',
						'value' => array(
							'after' => '0000-00-00 00:00:00'
						)
					);
					$args['reading_log_query'][] = array(
						'field' => 'date_finished',
						'value' => null
					);
					break;

				case 'read' :
					$args['reading_log_query'][] = array(
						'field' => 'date_started',
						'value' => array(
							'after' => '0000-00-00 00:00:00'
						)
					);
					$args['reading_log_query'][] = array(
						'field' => 'date_finished',
						'value' => array(
							'after' => '0000-00-00 00:00:00'
						)
					);
					break;

				case 'unread' :
					$read_book_ids = get_reading_logs( array(
						'number'  => 9999,
						'fields'  => 'book_id',
						'groupby' => 'book_id'
					) );

					if ( $read_book_ids ) {
						$args['id__not_in'] = $args['id__not_in'] + $read_book_ids;
					}
					break;
			}
		}

		// Filter by ownership status.
		$edition = $this->get_request_var( 'owned_status_filter' );
		if ( ! empty( $edition ) ) {
			switch ( $edition ) {
				case 'owned' :
					$args['edition_query'][] = array(
						'field' => 'date_acquired',
						'value' => array(
							'after' => '0000-00-00 00:00:00'
						)
					);
					break;

				case 'unowned' :
					$owned_book_ids = get_editions( array(
						'number'  => 9999,
						'fields'  => 'book_id',
						'groupby' => 'book_id'
					) );

					if ( $owned_book_ids ) {
						$args['id__not_in'] = $args['id__not_in'] + $owned_book_ids;
					}
					break;

				case 'signed' :
					$args['edition_query'][] = array(
						'field' => 'signed',
						'value' => 1
					);
					break;
			}
		}

		// Filter by rating.
		$rating = $this->get_request_var( 'rating', null );
		if ( null !== $rating ) {
			$args['reading_log_query'] = array(
				array(
					'field' => 'rating',
					'value' => floatval( $rating )
				)
			);
		}

		// Fix orderby
		if ( 'author' === $args['orderby'] ) {
			$args['orderby'] = 'author.name';
		} elseif ( 'title' === $args['orderby'] ) {
			$args['orderby'] = 'book.title';
		} elseif ( 'series' === $args['orderby'] ) {
			$args['orderby'] = 'series.name';
		} elseif ( 'pub_date' === $args['orderby'] ) {
			$args['orderby'] = 'book.pub_date';
		} elseif ( 'rating' === $args['orderby'] ) {
			$args['orderby'] = 'avg_rating.rating';
		} else {
			$args['orderby'] = 'book.id';
		}

		$query = new Books_Query();

		return $query->get_books( $args );

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

		$title  = isset( $_REQUEST['book_title'] ) ? wp_unslash( $_REQUEST['book_title'] ) : '';
		$author = isset( $_REQUEST['book_author'] ) ? wp_unslash( $_REQUEST['book_author'] ) : '';
		$series = isset( $_REQUEST['series_name'] ) ? wp_unslash( $_REQUEST['series_name'] ) : '';
		$isbn   = isset( $_REQUEST['isbn'] ) ? wp_unslash( $_REQUEST['isbn'] ) : '';
		?>
		<div class="search-form">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-title"><?php esc_html_e( 'Search by book title', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-title" name="book_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Book title', 'book-database' ); ?>">

			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-author"><?php esc_html_e( 'Search by author name', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-author" name="book_author" value="<?php echo esc_attr( $author ); ?>" placeholder="<?php esc_attr_e( 'Author name', 'book-database' ); ?>">

			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-series"><?php esc_html_e( 'Search by series name', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-series" name="series_name" value="<?php echo esc_attr( $series ); ?>" placeholder="<?php esc_attr_e( 'Series name', 'book-database' ); ?>">

			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-isbn"><?php esc_html_e( 'Search by ISBN', 'book-database' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>-isbn" name="isbn" value="<?php echo esc_attr( $isbn ); ?>" placeholder="<?php esc_attr_e( 'ISBN', 'book-database' ); ?>">
		</div>
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

		$read_filter   = $this->get_request_var( 'read_status_filter', '' );
		$owned_filter  = $this->get_request_var( 'owned_status_filter', '' );
		$format_filter = $this->get_request_var( 'format_filter', '' );
		?>
		<div class="alignleft actions">
			<label for="bdb-filter-by-read" class="screen-reader-text"><?php _e( 'Filter by read or unread', 'book-database' ); ?></label>
			<select id="bdb-filter-by-read" name="read_status_filter">
				<option value="" <?php selected( empty( $read_filter ) ); ?>><?php _e( 'Read &amp; Unread', 'book-database' ); ?></option>
				<option value="reading" <?php selected( $read_filter, 'reading' ); ?>><?php _e( 'Currently Reading', 'book-database' ); ?></option>
				<option value="read" <?php selected( $read_filter, 'read' ); ?>><?php _e( 'Read', 'book-database' ); ?></option>
				<option value="unread" <?php selected( $read_filter, 'unread' ); ?>><?php _e( 'Unread', 'book-database' ); ?></option>
			</select>

			<label for="bdb-filter-by-owned" class="screen-reader-text"><?php _e( 'Filter by owned', 'book-database' ); ?></label>
			<select id="bdb-filter-by-owned" name="owned_status_filter">
				<option value="" <?php selected( empty( $owned_filter ) ); ?>><?php _e( 'Owned &amp; Unowned', 'book-database' ); ?></option>
				<option value="owned" <?php selected( $owned_filter, 'owned' ); ?>><?php _e( 'Owned Books', 'book-database' ); ?></option>
				<option value="signed" <?php selected( $owned_filter, 'signed' ); ?>><?php _e( 'Signed Books', 'book-database' ); ?></option>
				<option value="unowned" <?php selected( $owned_filter, 'unowned' ); ?>><?php _e( 'Unowned Books', 'book-database' ); ?></option>
			</select>

			<label for="bdb-filter-by-format" class="screen-reader-text"><?php _e( 'Filter by format', 'book-database' ); ?></label>
			<select id="bdb-filter-by-format" name="format_filter">
				<option value="" <?php selected( empty( $format_filter ) ); ?>><?php _e( 'All Formats', 'book-database' ); ?></option>
				<?php foreach ( get_book_formats() as $format_key => $format_name ) : ?>
					<option value="<?php echo esc_attr( $format_key ); ?>" <?php selected( $format_filter, $format_key ); ?>><?php echo esc_html( $format_name ); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'book-database' ); ?>">
		</div>
		<?php

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

		$ids = wp_parse_id_list( (array) $this->get_request_var( 'book_id', false ) );

		// Bail if no IDs
		if ( empty( $ids ) ) {
			return;
		}

		try {

			foreach ( $ids as $book_id ) {

				switch ( $this->current_action() ) {

					case 'delete' :
						delete_book( $book_id );
						break;

				}

			}

			$this->show_admin_notice( $this->current_action(), count( $ids ) );

		} catch ( \Exception $e ) {
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
				$message = _n( '1 book deleted.', sprintf( '%d books deleted', $number ), $number, 'book-database' );
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

}