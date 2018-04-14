<?php

/**
 * Terms Table Class
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
 * Class BDB_Terms_Table
 *
 * Renders the terms table.
 *
 * @since 1.0
 */
class BDB_Terms_Table extends WP_List_Table {

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
	 * BDB_Terms_Table constructor.
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
			'singular' => __( 'Term', 'book-database' ),
			'plural'   => __( 'Terms', 'book-database' ),
			'ajax'     => false
		) );

	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_views() {
		$base = bdb_get_admin_page_terms();

		$current = isset( $_GET['type'] ) ? $_GET['type'] : 'author';
		$types   = bdb_get_taxonomies( true );
		$counts  = $this->get_counts();
		$views   = array();

		foreach ( $types as $id => $taxonomy ) {
			$views[ $id ] = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'type', urlencode( $id ), $base ), $current === $id ? ' class="current"' : '', esc_html( $taxonomy['name'] ) . '&nbsp;<span class="count">(' . $counts[ $id ] . ')</span>' );
		}

		return $views;
	}

	/**
	 * Get status counts
	 *
	 * Returns an array of all the statuses and their number of results.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_counts() {

		$types  = bdb_get_taxonomies( true );
		$counts = array();

		foreach ( $types as $id => $taxonomy ) {
			$counts[ $id ] = book_database()->book_terms->count( array( 'type' => $id ) );
		}

		return $counts;

	}

	/**
	 * Show the Search Field
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function search_box( $text, $input_id ) {

		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['type'] ) ) {
			echo '<input type="hidden" name="type" value="' . esc_attr( $_REQUEST['type'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php

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
	 * @param object $item        Contains all the data of the customers.
	 * @param string $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0
	 * @return string Column name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			/*case 'count' :
				// @todo link it
				break;*/

			default :
				$value = property_exists( $item, $column_name ) ? $item->$column_name : null;
				break;

		}

		return apply_filters( 'book-database/reviews-table/column/' . $column_name, $value, $item->term_id );

	}

	/**
	 * Render Checkbox Column
	 *
	 * @param object $item Contains all the data of the term.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function column_cb( $item ) {

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		?>
		<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $item->term_id ); ?>">
			<?php _e( 'Select this term', 'book-database' ) ?>
		</label>
		<input id="cb-select-<?php echo esc_attr( $item->term_id ); ?>" type="checkbox" name="terms[]" value="<?php echo esc_attr( $item->term_id ); ?>">
		<?php

	}

	/**
	 * Render Column Name
	 *
	 * @param object $item Contains all the data of the term.
	 *
	 * @access public
	 * @since  1.0
	 * @return string
	 */
	public function column_name( $item ) {
		$name    = esc_html( $item->name );
		$actions = array(
			'edit'   => '<a href="' . esc_url( bdb_get_admin_page_edit_term( $item->term_id ) ) . '">' . __( 'Edit', 'book-database' ) . '</a>',
			'delete' => '<a href="' . esc_url( bdb_get_admin_page_delete_term( $item->term_id ) ) . '">' . __( 'Delete', 'book-database' ) . '</a>'
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
			'cb'          => '<input type="checkbox">',
			'name'        => __( 'Name', 'book-database' ),
			'description' => __( 'Description', 'book-database' ),
			'slug'        => __( 'Slug', 'book-database' ),
			'count'       => __( 'Count', 'book-database' )
		);

		return apply_filters( 'book-database/terms-table/term-columns', $columns );
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
			'name'        => array( 'name', true ),
			'description' => array( 'description', true ),
			'slug'        => array( 'slug', true ),
			'count'       => array( 'count', true )
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
				<p><?php _e( 'Terms successfully deleted.', 'book-database' ); ?></p>
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

		return apply_filters( 'book-database/terms-table/get-bulk-actions', $actions );
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
				wp_die( __( 'You don\'t have permission to delete terms.', 'book-database' ) );
			}

			if ( isset( $_GET['terms'] ) && is_array( $_GET['terms'] ) && count( $_GET['terms'] ) ) {
				book_database()->book_terms->delete_by_ids( $_GET['terms'] );

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
	 * Build all the terms data.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of term data.
	 */
	public function terms_data() {

		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'ID';
		$type    = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'author';

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
			'name'    => $search,
			'type'    => $type
		);

		$this->args = $args;
		$terms      = bdb_get_terms( $args );

		if ( $terms ) {
			$data = wp_unslash( $terms );
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
	 * @uses   bdb_count_reviews()
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

		$this->items = $this->terms_data();

		$this->total = bdb_count_reviews( $this->args );

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
			__( 'No terms found. Would you like to %sadd one?%s', 'book-database' ),
			'<a href="' . esc_url( bdb_get_admin_page_add_term() ) . '">',
			'</a>'
		);
	}

}