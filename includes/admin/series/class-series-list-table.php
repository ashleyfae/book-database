<?php
/**
 * Series Admin Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Admin\Utils\ListTable;
use Book_Database\Models\Series;
use Book_Database\ValueObjects\Rating;

/**
 * Class Series_List_Table
 *
 * @package Book_Database
 */
class Series_List_Table extends ListTable {

	/**
	 * Series_List_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => 'series',
			'plural'   => 'series',
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
		return get_series_admin_page_url();
	}

	/**
	 * Get available columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox">',
			'name'         => esc_html__( 'Name', 'book-database' ),
			'slug'         => esc_html__( 'Slug', 'book-database' ),
			'number_books' => esc_html__( 'Books Read', 'book-database' ),
			'rating'       => esc_html__( 'Rating', 'book-database' ),
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
			'name' => array( 'name', true ),
			'slug' => array( 'slug', true ),
		);
	}

	/**
	 * Get the counts
	 */
	public function get_counts() {
		$this->counts = array(
			'total' => count_book_series()
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
		return 'name';
	}

	/**
	 * Render the "Name" column.
	 *
	 * @param Series $item
	 */
	public function column_name( $item ) {

		$edit_url = get_series_admin_page_url( array(
			'view'      => 'edit',
			'series_id' => $item->get_id()
		) );

		$actions = array(
			'edit'      => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'    => '<span class="trash"><a href="' . esc_url( get_delete_series_url( $item->get_id() ) ) . '" class="bdb-delete-item" data-object="' . esc_attr__( 'series', 'book-database' ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'series_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $item->get_id() ) . '</span>'
		);

		if ( ! user_can_edit_books() ) {
			unset( $actions['delete'] );
		}

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $item->get_name() ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param Series $item
	 * @param string $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value = '';

		switch ( $column_name ) {

			case 'slug' :
				$value = $item->get_slug();
				break;

			case 'number_books' :
				$book_url = get_books_admin_page_url( array(
					'series_id' => $item->get_id()
				) );

				$read  = $item->get_number_books_read();
				$total = $item->get_number_books();

				$value = '<a href="' . esc_url( $book_url ) . '" title="' . esc_attr__( 'View all books in this series', 'book-database' ) . '">' . sprintf( '%d/%d', $read, $total ) . '</a>';
				break;

			case 'rating' :
				$rating  = new Rating( $item->get_average_rating() );
				$classes = array();

				if ( ! is_null( $item->get_average_rating() ) ) {
					$classes[] = 'bdb-rating';
					$classes[] = 'bdb-' . $rating->format_html_class();
				}

				$classes = array_map( 'sanitize_html_class', $classes );

				$value = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $rating->format_html_stars() . '</span>';
				break;

		}

		return $value;

	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		esc_html_e( 'No series found.', 'book-database' );
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

		// Maybe add search.
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		if ( $count ) {
			return count_book_series( $args );
		} else {
			return get_book_series( $args );
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

		$ids = wp_parse_id_list( (array) $this->get_request_var( 'series_id', false ) );

		// Bail if no IDs
		if ( empty( $ids ) || ( 1 === count( $ids ) && isset( $ids[0] ) && empty( $ids[0] ) ) ) {
			return;
		}

		try {

			foreach ( $ids as $series_id ) {

				switch ( $this->current_action() ) {

					case 'delete' :
						delete_book_series( $series_id );
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
				$message = _n( '1 series deleted.', sprintf( '%d series deleted', $number ), $number, 'book-database' );
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
