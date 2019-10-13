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
	 * @todo Maybe add rating.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title'    => array( 'title', true ),
			'pub_date' => array( 'pub_date', true ),
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
	 * Render the "Title" column.
	 *
	 * @param Book $item
	 */
	public function column_title( $item ) {

		$edit_url = get_books_admin_page_url( array(
			'view'    => 'edit',
			'book_id' => $item->get_id()
		) );

		$actions = array(
			'edit'    => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'  => '<span class="trash"><a href="' . esc_url( get_delete_book_url( $item->get_id() ) ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'book_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $item->get_id() ) . '</span>'
		);

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $item->get_title() ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param Book   $item
	 * @param string $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value = '';

		$edit_url = get_books_admin_page_url( array(
			'view'    => 'edit',
			'book_id' => $item->get_id()
		) );

		switch ( $column_name ) {

			case 'cover' :
				if ( $item->get_cover_id() ) {
					$value = '<a href="' . esc_url( $edit_url ) . '">' . $item->get_cover( 'thumbnail' ) . '</a>';
				}
				break;

			case 'author' :
				$authors = $item->get_authors();
				if ( $authors ) {
					$authors_array = array();

					foreach ( $authors as $author ) {
						$authors_array[] = '<a href="' . esc_url( add_query_arg( 'author_id', urlencode( $author->get_id() ), $this->get_base_url() ) ) . '">' . esc_html( $author->get_name() ) . '</a>';
					}

					$value = implode( ', ', $authors_array );
				} else {
					$value = '&ndash;';
				}
				break;

			case 'series' :
				$series_id = $item->get_series_id();
				if ( ! empty( $series_id ) ) {
					$series = get_book_series_by( 'id', $series_id );
					$value  = '<a href="' . esc_url( add_query_arg( 'series_id', urlencode( $series_id ), $this->get_base_url() ) ) . '">' . esc_html( sprintf( '%s #%s', $series->get_name(), $item->get_series_position() ) ) . '</a>';
				} else {
					$value = '&ndash;';
				}
				break;

			case 'pub_date' :
				$value = $item->get_pub_date( true );
				break;

			case 'rating' :
				// @todo
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
			'number'    => $this->per_page,
			'offset'    => $this->get_offset(),
			'orderby'   => sanitize_text_field( $this->get_request_var( 'orderby', 'id' ) ),
			'order'     => sanitize_text_field( $this->get_request_var( 'order', 'DESC' ) ),
			'tax_query' => array()
		);

		// Filter by author ID.
		$author_id = $this->get_request_var( 'author_id' );
		if ( ! empty( $author_id ) ) {
			$args['author_id'][] = array(
				'field'    => 'id',
				'terms'    => absint( $author_id )
			);
		}

		// Maybe add search.
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		if ( $count ) {
			return count_books( $args );
		} else {
			return get_books( $args );
		}

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
	 * @todo make these filters work
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' != $which ) {
			return;
		}

		$owned_filter  = $this->get_request_var( 'owned_status_filter', '' );
		$format_filter = $this->get_request_var( 'format_filter', '' );
		?>
		<div class="alignleft actions">
			<label for="bdb-filter-by-owned" class="screen-reader-text"><?php _e( 'Filter by owned', 'book-database' ); ?></label>
			<select id="bdb-filter-by-owned" name="owned_status_filter">
				<option value="" <?php selected( empty( $owned_filter ) ); ?>><?php _e( 'All Books', 'book-database' ); ?></option>
				<option value="owned" <?php selected( $owned_filter, 'owned' ); ?>><?php _e( 'Owned Books', 'book-database' ); ?></option>
				<option value="signed" <?php selected( $owned_filter, 'signed' ); ?>><?php _e( 'Signed Books', 'book-database' ); ?></option>
				<option value="unowned" <?php selected( $owned_filter, 'unowned' ); ?>><?php _e( 'Unowned Books', 'book-database' ); ?></option>
			</select>

			<label for="bdb-filter-by-format" class="screen-reader-text"><?php _e( 'Filter by format', 'book-database' ); ?></label>
			<select id="bdb-filter-by-format" name="format_filter">
				<option value="" <?php selected( empty( $format_filter ) ); ?>><?php _e( 'All Formats', 'book-database' ); ?></option>
				<?php // @todo loop through formats ?>
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

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-books' ) ) {
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