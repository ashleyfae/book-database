<?php

/**
 * Series Table Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
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
 * Class BDB_Series_Table
 *
 * Renders the series table.
 *
 * @since 1.0
 */
class BDB_Series_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $per_page = 30;

	/**
	 * Number of reviews found
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $count = 0;

	/**
	 * Total number of reviews
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @access public
	 * @since  1.0
	 */
	public $args = array();

	/**
	 * Display delete message
	 *
	 * @var bool
	 * @access private
	 * @since  1.0
	 */
	private $display_delete_message = false;

	/**
	 * BDB_Series_Table constructor.
	 *
	 * @see    WP_List_Table::__construct()
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {

		global $status, $page;

		parent::__construct( array(
			'singular' => __( 'Series', 'book-database' ),
			'plural'   => __( 'Series', 'book-database' ),
			'ajax'     => false
		) );

	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @access protected
	 * @since  1.0
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Renders most of the columns in the list table.
	 *
	 * @param BDB_Series $series      Contains all the data of the series.
	 * @param string     $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0
	 * @return string Column contents
	 */
	public function column_default( $series, $column_name ) {

		$value = '';

		switch ( $column_name ) {

			case 'books_read' :
				$read  = $series->get_number_books_read();
				$total = $series->get_number_books();
				$url   = add_query_arg( 'series_id', absint( $series->ID ), bdb_get_admin_page_books() );

				$value = sprintf( '<a href="%s" title="%s">%s/%s</a>', esc_url( $url ), esc_attr__( 'View all books in this series', 'book-database' ), $read, $total );
				break;

			case 'rating' :
				$rating = $series->get_average_rating();
				$value  = sprintf( __( '%s Stars', 'book-database' ), $rating );
				break;

		}

		return apply_filters( 'book-database/series-table/column/' . $column_name, $value, $series );

	}

	/**
	 * Render Checkbox Column
	 *
	 * @param BDB_Series $series Contains all the data of the series.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function column_cb( $series ) {

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		?>
		<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $series->ID ); ?>">
			<?php _e( 'Select this series', 'book-database' ) ?>
		</label>
		<input id="cb-select-<?php echo esc_attr( $series->ID ); ?>" type="checkbox" name="series[]" value="<?php echo esc_attr( $series->ID ); ?>">
		<?php

	}

	/**
	 * Render Column Name
	 *
	 * @param BDB_Series $item Contains all the data of the series.
	 *
	 * @access public
	 * @since  1.0
	 * @return string
	 */
	public function column_name( $item ) {
		$edit_url = bdb_get_admin_page_edit_series( $item->ID );
		$name     = '<a href="' . esc_url( $edit_url ) . '" class="row-title" aria-label="' . esc_attr( sprintf( '%s (Edit)', $item->name ) ) . '">' . $item->name . '</a>';
		$actions  = array(
			'edit'   => '<a href="' . esc_url( $edit_url ) . '">' . __( 'Edit', 'book-database' ) . '</a>',
			'delete' => '<a href="' . esc_url( bdb_get_admin_page_delete_series( $item->ID ) ) . '">' . __( 'Delete', 'book-database' ) . '</a>'
		);

		return $name . $this->row_actions( $actions );
	}

	/**
	 * Get Columns
	 *
	 * Retrieves the column IDs and names.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox">',
			'name'       => __( 'Name', 'book-database' ),
			'books_read' => __( 'Books Read', 'book-database' ),
			'rating'     => __( 'Rating', 'book-database' )
		);

		return apply_filters( 'book-database/books-table/series-columns', $columns );
	}

	/**
	 * Get the sortable columns.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name'       => array( 'name', true ),
			'books_read' => array( 'books_read', true )
		);
	}

	/**
	 * Table Navigation
	 *
	 * Generate the table navigation above or below the table.
	 *
	 * @param string $which
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}

		// Display 'delete' success message.
		if ( 'top' == $which && true === $this->display_delete_message ) {
			?>
			<div id="message" class="updated notice notice-success">
				<p><?php _e( 'Series successfully deleted.', 'book-database' ); ?></p>
			</div>
			<?php
		}

		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ): ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php

	}

	/**
	 * Get Bulk Actions
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete Permanently', 'book-database' )
		);

		return apply_filters( 'book-database/series-table/get-bulk-actions', $actions );
	}

	/**
	 * Process Bulk Actions
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function process_bulk_actions() {

		if ( 'delete' == $this->current_action() ) {

			// Check nonce.
			if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
				wp_die( __( 'Failed security check.', 'book-database' ) );
			}

			// Checek capability.
			if ( ! current_user_can( 'delete_posts' ) ) {
				wp_die( __( 'You don\'t have permission to delete books.', 'book-database' ) );
			}

			if ( isset( $_GET['series'] ) && is_array( $_GET['series'] ) && count( $_GET['series'] ) ) {
				foreach ( $_GET['series'] as $series_id ) {
					bdb_delete_series( absint( $series_id ) );
				}

				// Display the delete message.
				$this->display_delete_message = true;
			}

		}

	}

	/**
	 * Retrieve the current page number.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool|string Search query or false if none.
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Build all the series data.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of series data.
	 */
	public function series_data() {

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'ID';

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
		);

		$series = book_database()->series->get_series( $args );

		if ( ! empty( $series ) ) {
			foreach ( $series as $single_series ) {
				$data[] = new BDB_Series( $single_series );
			}
		}

		return $data;

	}

	/**
	 * Prepare Items
	 *
	 * Setup the final data for the table.
	 *
	 * @uses   BDB_Series_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   BDB_Series_Table::series_data()
	 * @uses   WP_List_Table::set_pagination_args()
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function prepare_items() {

		// Process bulk actions.
		$this->process_bulk_actions();

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->series_data();

		$this->total = book_database()->books->count( $this->args );

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page )
		) );

	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  1.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		printf(
			__( 'No series found. You can add one by %screating a book%s and filling out the series field.', 'book-database' ),
			'<a href="' . esc_url( bdb_get_admin_page_add_book() ) . '">',
			'</a>'
		);
	}

}