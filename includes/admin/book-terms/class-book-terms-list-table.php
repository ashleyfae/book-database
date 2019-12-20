<?php
/**
 * Book Terms Admin Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Terms_List_Table
 * @package Book_Database
 */
class Book_Terms_List_Table extends List_Table {

	protected $default_taxonomy;

	/**
	 * Book_Terms_List_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => 'book_term',
			'plural'   => 'book_terms',
			'ajax'     => false
		) );

		$taxonomies = get_book_taxonomies( array(
			'number'  => 1,
			'orderby' => 'name',
			'order'   => 'ASC',
			'fields'  => 'name'
		) );

		if ( ! empty( $taxonomies ) ) {
			$this->default_taxonomy = reset( $taxonomies );
		}

		$this->get_counts();

	}

	/**
	 * Get the base URL for this list table.
	 *
	 * @return string Base URL.
	 */
	public function get_base_url() {
		return get_book_terms_admin_page_url();
	}

	/**
	 * Get available columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox">',
			'name'        => esc_html__( 'Name', 'book-database' ),
			'description' => esc_html__( 'Description', 'book-database' ),
			'slug'        => esc_html__( 'Slug', 'book-database' ),
			'book_count'  => esc_html__( 'Book Count', 'book-database' ),
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
			'name'       => array( 'name', true ),
			'slug'       => array( 'slug', true ),
			'book_count' => array( 'book_count', true ),
		);
	}

	/**
	 * Get the counts
	 */
	public function get_counts() {

		$counts     = array();
		$taxonomies = get_book_taxonomies( array(
			'fields'  => 'slug',
			'orderby' => 'name',
			'order'   => 'ASC'
		) );

		foreach ( $taxonomies as $taxonomy ) {
			/**
			 * @var string $taxonomy
			 */
			$counts[ $taxonomy ] = count_book_terms( array( 'taxonomy' => $taxonomy ) );
		}

		$this->counts = $counts;

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
	 * @param Book_Term $item
	 */
	public function column_name( $item ) {

		$edit_url = get_book_terms_admin_page_url( array(
			'view'    => 'edit',
			'term_id' => $item->get_id()
		) );

		$actions = array(
			'edit'    => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'book-database' ) . '</a>',
			'delete'  => '<span class="trash"><a href="' . esc_url( get_delete_book_term_url( $item->get_id() ) ) . '" class="bdb-delete-item" data-object="' . esc_attr__( 'book_term', 'book-database' ) . '">' . esc_html__( 'Delete', 'book-database' ) . '</a></span>',
			'term_id' => '<span class="bdb-id-col">' . sprintf( __( 'ID: %d', 'book-database' ), $item->get_id() ) . '</span>'
		);

		if ( ! user_can_edit_books() ) {
			unset( $actions['delete'] );
		}

		return '<strong><a href="' . esc_url( $edit_url ) . '" class="row-title">' . esc_html( $item->get_name() ) . '</a></strong>' . $this->row_actions( $actions );

	}

	/**
	 * Renders most of the columns in the list table
	 *
	 * @param Book_Term $item
	 * @param string    $column_name
	 *
	 * @return string Column value.
	 */
	public function column_default( $item, $column_name ) {

		$value = '';

		switch ( $column_name ) {

			case 'description' :
				$value = $item->get_description();
				break;

			case 'slug' :
				$value = $item->get_slug();
				break;

			case 'book_count' :
				$book_url = get_books_admin_page_url( array(
					'term_id' => $item->get_id()
				) );
				$value    = '<a href="' . esc_url( $book_url ) . '">' . esc_html( $item->get_book_count() ) . '</a>';
				break;

		}

		return $value;

	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		esc_html_e( 'No terms found.', 'book-database' );
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
			'taxonomy' => $this->get_status( $this->default_taxonomy ),
			'number'   => $this->per_page,
			'offset'   => $this->get_offset(),
			'orderby'  => sanitize_text_field( $this->get_request_var( 'orderby', 'id' ) ),
			'order'    => sanitize_text_field( $this->get_request_var( 'order', 'DESC' ) ),
		);

		// Maybe add search.
		$search = $this->get_search();
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		if ( $count ) {
			return count_book_terms( $args );
		} else {
			return get_book_terms( $args );
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

		$ids = wp_parse_id_list( (array) $this->get_request_var( 'book_term_id', false ) );

		// Bail if no IDs
		if ( empty( $ids ) ) {
			return;
		}

		try {

			foreach ( $ids as $term_id ) {

				switch ( $this->current_action() ) {

					case 'delete' :
						delete_book_term( $term_id );
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
				$message = _n( '1 term deleted.', sprintf( '%d terms deleted', $number ), $number, 'book-database' );
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
	 * Retrieve the view types
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {

		// Get the current status
		$current = $this->get_status( $this->default_taxonomy );

		// Args to remove
		$remove = array( 'bdb_message', 'status', 'paged', '_wpnonce' );

		// Base URL
		$url = remove_query_arg( $remove, $this->get_base_url() );

		// Is all selected?
		$class = in_array( $current, array( '', 'all' ), true ) ? ' class="current"' : '';

		$counts = $this->counts;
		$views  = array();

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
				$taxonomy = get_book_taxonomy_by( 'slug', $status );

				if ( ! $taxonomy instanceof Book_Taxonomy ) {
					continue;
				}

				$count_url = add_query_arg( array(
					'status' => $status,
					'paged'  => false,
				), $url );

				$class = ( $current === $status ) ? ' class="current"' : '';

				$count = '&nbsp;<span class="count">(' . absint( $this->counts[ $status ] ) . ')</span>';

				$label            = ( $taxonomy->get_name() ) . $count;
				$views[ $status ] = sprintf( '<a href="%s"%s>%s</a>', $count_url, $class, $label );
			}
		}

		return $views;
	}

}