<?php
/**
 * Book Table Month Class
 *
 * Taken from Sugar Event Calendar.
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
 * Class BDB_Books_Month_Table
 *
 * @since 1.0
 */
class BDB_Books_Month_Table extends BDB_Books_Table {

	/**
	 * The mode of the current view.
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $mode = 'month';

	/**
	 * What day does a calendar week start on?
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $start_of_week = '0';

	/**
	 * The beginning boundary for the current view.
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $view_start = '';

	/**
	 * The end boundary for the current view.
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $view_end = '';

	/**
	 * Duration of view, from start to end.
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $view_duration = 0;

	/**
	 * The year being viewed.
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $year = 2017;

	/**
	 * The month being viewed.
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $month = 1;

	/**
	 * The day being viewed.
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $day = 1;

	/**
	 * The exact day being viewed based on year/month/day.
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $today = '';

	/**
	 * The items with pointers.
	 *
	 * @var array
	 * @access public
	 * @since  1.0
	 */
	public $pointers = array();

	/**
	 * Unix time month start.
	 *
	 * @var int
	 * @access private
	 * @since  1.0
	 */
	private $month_start = 0;

	/**
	 * Unix time month end.
	 *
	 * @var int
	 * @access private
	 * @since  1.0
	 */
	private $month_end = 0;

	/**
	 * BDB_Books_Month_Table constructor.
	 *
	 * @param array $args
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $args = array() ) {

		add_filter( 'book-database/books-table/extra-tablenav/filters/before', array( $this, 'calendar_nav' ) );

		// Ready the pointer content
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_pointers_footer' ) );

		// Start of week
		$this->start_of_week = get_option( 'start_of_week', '0' );

		// Set year, month, & day
		$this->year  = $this->get_year();
		$this->month = $this->get_month();
		$this->day   = $this->get_day();

		// Set "today" based on current request
		$this->today = strtotime( "{$this->year}/{$this->month}/{$this->day}" );

		// View start
		$view_start = "{$this->year}-{$this->month}-01 00:00:00";

		// Month boundaries
		$this->month_start = mysql2date( 'U', $view_start );
		$this->month_end   = strtotime( '+1 month', $this->month_start );

		// View end
		$view_end = date_i18n( 'Y-m-d H:i:s', $this->month_end );

		// Set the view
		$this->set_view( $view_start, $view_end );

		parent::__construct( $args );

	}

	/**
	 * Set the start, end, and duration of the current view.
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function set_view( $start = '', $end = '' ) {

		$this->view_start    = $start;
		$this->view_end      = $end;
		$this->view_duration = strtotime( $end ) - strtotime( $start );

	}

	/**
	 * Get the days for any given week.
	 *
	 * @access protected
	 * @since  1.0
	 * @return array
	 */
	protected function get_days_for_week() {

		// Day values
		$days = array(
			'0' => 'sunday',
			'1' => 'monday',
			'2' => 'tuesday',
			'3' => 'wednesday',
			'4' => 'thursday',
			'5' => 'friday',
			'6' => 'saturday'
		);

		// Get the day index
		$index  = array_search( $this->start_of_week, array_keys( $days ) );
		$start  = array_slice( $days, $index, count( $days ), true );
		$finish = array_slice( $days, 0, $index, true );

		// Combine to retain keys
		return $start + $finish;
	}

	/**
	 * Get the number of the current month.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_month() {
		return (int) isset( $_REQUEST['cm'] )
			? (int) $_REQUEST['cm']
			: date_i18n( 'n' );
	}

	/**
	 * Get the name of the current month.
	 *
	 * @access public
	 * @since  1.0
	 * @return string
	 */
	public function get_month_name() {
		return $GLOBALS['wp_locale']->get_month( $this->get_month() );
	}

	/**
	 * Get the current day.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_day() {
		return (int) isset( $_REQUEST['cd'] )
			? (int) $_REQUEST['cd']
			: date_i18n( 'j' );
	}

	/**
	 * Get the current year.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_year() {
		return (int) isset( $_REQUEST['cy'] )
			? (int) $_REQUEST['cy']
			: date_i18n( 'Y' );
	}

	/**
	 * Setup the calendar view columns.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {

		static $retval = null;

		// Calculate if not calculated already
		if ( null === $retval ) {

			// PHP day => day ID
			$days = $this->get_days_for_week();

			// Setup return value
			$retval = array();
			foreach ( $days as $key => $day ) {
				$retval[ $day ] = $GLOBALS['wp_locale']->get_weekday( $key );
			}
		}

		return $retval;

	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @access protected
	 * @since  1.0
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'monday';
	}

	/**
	 * Display navigation for changing month/year.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function calendar_nav() {

		$months = array();

		for ( $month_index = 1; $month_index <= 12; $month_index ++ ) {
			$months[ $month_index ] = $GLOBALS['wp_locale']->get_month( $month_index );
		}
		?>
		<label for="bdb-filter-by-month" class="screen-reader-text"><?php _e( 'Switch to this month', 'book-database' ); ?></label>
		<?php
		echo book_database()->html->select( array(
			'name'             => 'cm',
			'id'               => 'bdb-filter-by-cm',
			'options'          => $months,
			'selected'         => $this->month,
			'show_option_all'  => false,
			'show_option_none' => false
		) );
		?>

		<label for="bdb-filter-by-year" class="screen-reader-text"><?php _e( 'Switch to this year', 'book-database' ); ?></label>
		<?php
		echo book_database()->html->text( array(
			'name'  => 'cy',
			'id'    => 'bdb-filter-by-cy',
			'value' => $this->year,
			'type'  => 'number'
		) );

	}

	/**
	 * Get a list of CSS classes for the list table tag.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'calendar', 'month', $this->_args['plural'] );
	}

	/**
	 * Start the week with a table row.
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function get_row_start() {
		echo '<tr>';
	}

	/**
	 * End the week with a closed table row.
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function get_row_end() {
		echo '</tr>';
	}

	/**
	 * Display the table heading with the necessary padding.
	 *
	 * @param int $iterator
	 * @param int $start_date
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function get_row_pad( $iterator = 1, $start_date = 1 ) {
		echo '<th class="padding ' . $this->get_day_classes( $iterator, $start_date ) . '"></th>';
	}

	/**
	 * Is the current calendar view today?
	 *
	 * @access protected
	 * @since  1.0
	 * @return bool
	 */
	protected function is_today( $month, $day, $year ) {
		$_month = (bool) ( $month == date_i18n( 'n' ) );
		$_day   = (bool) ( $day == date_i18n( 'j' ) );
		$_year  = (bool) ( $year == date_i18n( 'Y' ) );

		return (bool) ( true === $_month && true === $_day && true === $_year );
	}

	/**
	 * Get classes for table cell.
	 *
	 * @param int $iterator
	 * @param int $start_day
	 *
	 * @access protected
	 * @since  1.0
	 * @return string
	 */
	protected function get_day_classes( $iterator = 1, $start_day = 1 ) {

		// Day offset
		$offset = ( $iterator - $start_day ) + 1;

		// Don't allow negative offsets
		if ( $offset <= 0 ) {
			$offset = 0;
		}

		// Get day of week, and day key
		$days    = $this->get_days_for_week();
		$dow     = ( $iterator % count( $days ) );
		$day_key = array_values( $days )[ $dow ];

		// Position & day info
		$position     = "position-{$dow}";
		$cell_number  = "cell-{$offset}";
		$day_number   = "day-{$offset}";
		$month_number = "month-{$this->month}";
		$year_number  = "year-{$this->year}";

		// Today?
		$is_today = $this->is_today( $this->month, $offset, $this->year )
			? 'today'
			: '';

		// Assemble classes
		$classes = array(
			$this->the_day, // @todo
			$day_key,
			$is_today,
			$position,
			$cell_number,
			$day_number,
			$month_number,
			$year_number
		);

		return implode( ' ', $classes );

	}

	/**
	 * Get the already queried books for a given day.
	 *
	 * @param int    $iterator
	 * @param string $type
	 *
	 * @access protected
	 * @since  1.0
	 * @return array
	 */
	protected function get_queried_items( $iterator = 1, $type = 'items' ) {
		return isset( $this->{$type}[ $iterator ] ) ? $this->{$type}[ $iterator ] : array();
	}

	/**
	 * Get books for a given cell.
	 *
	 * @param int    $iterator
	 * @param string $type
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function get_books_for_cell( $iterator = 1, $type = 'items' ) {

		// Get books and bail if none.
		$books = $this->get_queried_items( $iterator, $type );

		if ( empty( $books ) ) {
			return;
		}

		foreach ( $books as $book ) {

			$book = new BDB_Book( $book );

			// Setup the pointer ID.
			$pointer_id = "{$book->ID}-{$iterator}";

			// Get teh book edit link.
			$book_link = bdb_get_admin_page_edit_book( $book->ID );

			// Handle empty titles.
			$book_title = $book->get_title();
			if ( empty( $book_title ) ) {
				$book_title = __( '(No title)', 'book-database' );
			}

			?>
			<a href="<?php echo esc_url( $book_link ); ?>" id="book-pointer-<?php echo esc_attr( $pointer_id ); ?>" class="bdb-calendar-book-link">
				<?php
				if ( $book->get_cover_id() ) {
					echo $book->get_cover( 'large' );
				} else {
					printf( '%s by %s', $book_title, $book->get_author_names() );
				}
				?>
			</a>
			<?php

		}

	}

	/**
	 * Display the contents of an individual cell.
	 *
	 * @param int $iterator
	 * @param int $start_day
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function get_row_cell( $iterator = 1, $start_day = 1 ) {

		// Calculate the day of the month
		$day_of_month = (int) ( $iterator - (int) $start_day + 1 );

		// Calculate link to day view
		$link_to_day = add_query_arg( array(
			'mode' => 'day',
			'cy'   => $this->year,
			'cm'   => $this->month,
			'cd'   => $day_of_month
		), $this->get_base_url() );

		// Link to add new event on this day
		$add_event_for_day = add_query_arg( 'pub_date', urlencode( $this->year . '-' . $this->month . '-' . $day_of_month ), bdb_get_admin_page_add_book() );
		?>
		<td class="<?php echo $this->get_day_classes( $iterator, $start_day ); ?>">
			<a href="#" class="day-number">
				<?php echo (int) $day_of_month; ?>
			</a>

			<a href="<?php echo esc_url( $add_event_for_day ); ?>" class="add-book-for-day">
				<i class="dashicons dashicons-plus"></i>
			</a>

			<div class="books-for-cell">
				<?php $this->get_books_for_cell( $day_of_month ); ?>
			</div>
		</td>
		<?php

	}

	/**
	 * Display a calendar by month and year.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function display_mode() {

		// Get timestamp for first & last days of month
		$timestamp  = mktime( 0, 0, 0, $this->month, 1, $this->year );
		$max_day    = date( 't', $timestamp );
		$this_month = getdate( $timestamp );

		// Get days for week, in order, to set the start day
		$days      = $this->get_days_for_week();
		$length    = count( $days );
		$start_day = array_search( $this_month['wday'], array_keys( $days ) );

		// Loop through days of the month
		for ( $i = 0; $i < ( $max_day + $start_day ); $i ++ ) {

			// New row
			if ( ( $i % $length ) === 0 ) {
				$this->get_row_start();
			}

			// Pad day
			if ( $i < $start_day ) {
				$this->get_row_pad( $i, $start_day );

				// Month day
			} else {
				$this->get_row_cell( $i, $start_day );
			}

			if ( ( $i % $length ) === ( $length - 1 ) ) {
				$this->get_row_end();
			}
		}

	}

	/**
	 * Build all the book data.
	 *
	 * @param array $args Query args.
	 *
	 * @access protected
	 * @since  1.0
	 * @return array Array of book data.
	 */
	protected function books_data( $args = array() ) {

		$args['number']            = - 1;
		$args['order']             = 'ASC';
		$args['orderby']           = 'pub_date';
		$args['pub_date']['start'] = $this->view_start;
		$args['pub_date']['end']   = $this->view_end;

		return parent::books_data( $args );

	}

	/**
	 * Setup book item
	 *
	 * @uses   BDB_Books_Month_Table::set_queried_item()
	 *
	 * @param array $book Book data.
	 * @param int   $max  Maximum number of books per day.
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function setup_item( $book, $max = 20 ) {

		$pub_date = $book['pub_date'];
		$day      = date_i18n( 'j', strtotime( $pub_date ) );

		$this->set_queried_item( $day, 'items', $book['ID'], $book );

	}

	/**
	 * Set a queried item in its proper array position.
	 *
	 * @param int    $iterator
	 * @param string $type
	 * @param int    $item_id
	 * @param mixed  $data
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function set_queried_item( $iterator = 1, $type = 'items', $item_id = 0, $data = array() ) {

		// Prevent debug notices if type is not set
		if ( ! isset( $this->{$type}[ $iterator ] ) ) {
			$this->{$type}[ $iterator ] = array();
		}

		// Set the queried item
		$this->{$type}[ $iterator ][ $item_id ] = $data;
	}

	/**
	 * Prepare Items
	 *
	 * Setup the final data for the table.
	 *
	 * @uses   BDB_Books_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   BDB_Books_Table::books_data()
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

		$books = $this->books_data();

		// Rearrange books into an array keyed by day of the month.
		foreach ( $books as $book ) {
			// Prepare book and item.
			$this->setup_item( $book, $this->per_page );
		}

		$this->total = book_database()->books->count( $this->args );

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page )
		) );

	}

	/**
	 * Paginate through months & years.
	 *
	 * @param array $args
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	public function pagination( $args = array() ) {

		// Parse args
		$args = wp_parse_args( $args, array(
			'which'  => 'top',
			'small'  => '1 month',
			'large'  => '1 year',
			'labels' => array(
				'today'      => esc_html__( 'Today', 'book-database' ),
				'next_small' => esc_html__( 'Next', 'book-database' ),
				'next_large' => esc_html__( 'Next', 'book-database' ),
				'prev_small' => esc_html__( 'Previous', 'book-database' ),
				'prev_large' => esc_html__( 'Previous', 'book-database' )
			)
		) );

		// No bottom pagination
		if ( 'top' !== $args['which'] ) {
			return;
		}

		// Base URLs
		$today = $this->get_base_url();

		// Calculate previous & next weeks & months
		$prev_small = strtotime( "-{$args['small']}", $this->today );
		$next_small = strtotime( "+{$args['small']}", $this->today );
		$prev_large = strtotime( "-{$args['large']}", $this->today );
		$next_large = strtotime( "+{$args['large']}", $this->today );

		// Week
		$prev_small_d = date_i18n( 'j', $prev_small );
		$prev_small_m = date_i18n( 'n', $prev_small );
		$prev_small_y = date_i18n( 'Y', $prev_small );
		$next_small_d = date_i18n( 'j', $next_small );
		$next_small_m = date_i18n( 'n', $next_small );
		$next_small_y = date_i18n( 'Y', $next_small );

		// Month
		$prev_large_d = date_i18n( 'j', $prev_large );
		$prev_large_m = date_i18n( 'n', $prev_large );
		$prev_large_y = date_i18n( 'Y', $prev_large );
		$next_large_d = date_i18n( 'j', $next_large );
		$next_large_m = date_i18n( 'n', $next_large );
		$next_large_y = date_i18n( 'Y', $next_large );

		// Setup month args
		$prev_small_args = array( 'cy' => $prev_small_y, 'cm' => $prev_small_m, 'cd' => $prev_small_d );
		$prev_large_args = array( 'cy' => $prev_large_y, 'cm' => $prev_large_m, 'cd' => $prev_large_d );
		$next_small_args = array( 'cy' => $next_small_y, 'cm' => $next_small_m, 'cd' => $next_small_d );
		$next_large_args = array( 'cy' => $next_large_y, 'cm' => $next_large_m, 'cd' => $next_large_d );

		// Setup links
		$prev_small_link = add_query_arg( $prev_small_args, $today );
		$next_small_link = add_query_arg( $next_small_args, $today );
		$prev_large_link = add_query_arg( $prev_large_args, $today );
		$next_large_link = add_query_arg( $next_large_args, $today );

		?>

		<div class="tablenav-pages previous">
			<a class="previous-page" href="<?php echo esc_url( $prev_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['prev_large'] ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="previous-page" href="<?php echo esc_url( $prev_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['prev_small'] ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>

			<a href="<?php echo esc_url( $today ); ?>" class="previous-page">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['today'] ); ?></span>
				<span aria-hidden="true">&Colon;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( $next_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['next_small'] ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( $next_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['next_large'] ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>
		</div>

		<?php

	}

	/**
	 * Output the pointers for each book.
	 *
	 * This is a pretty horrible way to accomplish this, but it's currently the
	 * way WordPress's pointer API expects to work, so be it.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function admin_pointers_footer() {
		?>
		<!-- Start Event Pointers -->
		<script type="text/javascript" id="book-database-pointers">
			/* <![CDATA[ */
			(function ($) {
				$('table.calendar .events-for-cell a').click(function (event) {
					event.preventDefault();
				});

				<?php foreach ( $this->pointers as $item ) : ?>

				$('<?php echo $item['anchor_id']; ?>').pointer({
					content: '<?php echo $item['content']; ?>',
					position: {
						edge: '<?php echo $item['edge']; ?>',
						align: '<?php echo $item['align']; ?>'
					}
				});

				$('<?php echo $item['anchor_id']; ?>').click(function () {
					$(this).pointer('open');
				});

				<?php endforeach; ?>
			})(jQuery);
			/* ]]> */
		</script>
		<!-- End Event Pointers -->
		<?php
	}

}