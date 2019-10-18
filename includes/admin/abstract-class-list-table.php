<?php
/**
 * Admin List Table Base Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

// Load WP_List_Table if not loaded
if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class List_Table
 * @package Book_Database
 */
abstract class List_Table extends \WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 */
	public $per_page = 30;

	/**
	 * Array of statuses and counts
	 *
	 * @var array
	 */
	public $counts = array(
		'total' => 0
	);

	/**
	 * List_Table constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		$this->set_per_page();

		add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );
	}

	/**
	 * Set number of results per page
	 *
	 * This uses the screen options setting if available. Otherwise it defaults to 30.
	 */
	protected function set_per_page() {

		$per_page      = 30;
		$user_id       = get_current_user_id();
		$screen        = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );

		if ( ! empty( $screen_option ) ) {
			$per_page = get_user_meta( $user_id, $screen_option, true );

			if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
		}

		$this->per_page = $per_page;

	}

	/**
	 * Get a request var, or return the default if not set.
	 *
	 * @param string $var
	 * @param mixed  $default
	 *
	 * @return mixed Un-sanitized request var
	 */
	public function get_request_var( $var = '', $default = false ) {
		return isset( $_REQUEST[ $var ] ) ? $_REQUEST[ $var ] : $default;
	}

	/**
	 * Get a status request var, if set.
	 *
	 * @param mixed $default
	 *
	 * @return string
	 */
	protected function get_status( $default = '' ) {
		return sanitize_key( $this->get_request_var( 'status', $default ) );
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @return int
	 */
	protected function get_paged() {
		return absint( $this->get_request_var( 'paged', 1 ) );
	}

	/**
	 * Retrieve the offset to use for the query.
	 *
	 * @return int Offset.
	 */
	protected function get_offset() {
		$offset = ( $this->get_paged() - 1 ) * $this->per_page;

		return absint( $offset );
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @return int Current page number.
	 */
	protected function get_search() {
		return rawurldecode( trim( $this->get_request_var( 's', '' ) ) );
	}

	/**
	 * Generate the table navigation above or below the table.
	 *
	 * We're overriding this to turn off the referer param in `wp_nonce_field()`.
	 *
	 * @param string $which
	 */
	public function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'], '_wpnonce', false );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Show the search field.
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no items and no search
		if ( ! $this->get_search() && ! $this->has_items() ) {
			return;
		}

		$orderby  = $this->get_request_var( 'orderby' );
		$order    = $this->get_request_var( 'order' );
		$input_id = $input_id . '-search-input';

		if ( ! empty( $orderby ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
		}

		if ( ! empty( $order ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}

		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>
				:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( esc_html( $text ), 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>

		<?php
	}

	/**
	 * Get the base URL for this list table.
	 *
	 * @return string Base URL.
	 */
	public function get_base_url() {
		return admin_url();
	}

	/**
	 * Retrieve the view types
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {

		// Get the current status
		$current = $this->get_status();

		// Args to remove
		$remove = array( 'bdb_message', 'status', 'paged', '_wpnonce' );

		// Base URL
		$url = remove_query_arg( $remove, $this->get_base_url() );

		// Is all selected?
		$class = in_array( $current, array( '', 'all' ), true ) ? ' class="current"' : '';

		$counts = $this->counts;
		$views = array();

		if ( isset( $this->counts['total'] ) ) {
			// All
			$count = '&nbsp;<span class="count">(' . esc_attr( $this->counts['total'] ) . ')</span>';
			$label = __( 'All', 'rcp' ) . $count;
			$views = array(
				'all' => sprintf( '<a href="%s"%s>%s</a>', $url, $class, $label ),
			);

			// Remove total from counts array
			unset( $counts['total'] );
		}

		// Loop through statuses.
		if ( ! empty( $counts ) ) {
			foreach ( $counts as $status => $count ) {
				$count_url = add_query_arg( array(
					'status' => $status,
					'paged'  => false,
				), $url );

				$class = ( $current === $status ) ? ' class="current"' : '';

				$count = '&nbsp;<span class="count">(' . absint( $this->counts[ $status ] ) . ')</span>';

				$label            = ( $status ) . $count; // @todo get_status_label() was here
				$views[ $status ] = sprintf( '<a href="%s"%s>%s</a>', $count_url, $class, $label );
			}
		}

		return $views;
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param Base_Object $object
	 *
	 * @return string
	 */
	public function column_cb( $object ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'] . '_id',
			$object->get_id()
		);
	}

	/**
	 * Remove "action" query arg. This prevents the bulk action admin notice from persisting across page views.
	 *
	 * @param array $query_args
	 *
	 * @return array
	 */
	public function removable_query_args( $query_args ) {
		$query_args[] = 'action';

		return $query_args;
	}

	/**
	 * Retrieve object data.
	 *
	 * @param bool $count Whether or not to get objects (false) or just count the total number (true).
	 *
	 * @return array|int
	 */
	public function get_object_data( $count = false ) {
		if ( $count ) {
			return 0;
		} else {
			return array();
		}
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_object_data();

		$total = $this->get_object_data( true );

		// Setup pagination
		$this->set_pagination_args( array(
			'total_items' => $total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total / $this->per_page )
		) );
	}

	/**
	 * Process bulk actions
	 */
	public function process_bulk_actions() {

	}
}