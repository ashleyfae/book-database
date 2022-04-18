<?php
/**
 * class-monthly-books-list-table.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Models\Book;

/**
 * Class Monthly_Books_List_Table
 *
 * @package Book_Database
 */
class Monthly_Books_List_Table extends Books_List_Table {

	/**
	 * Mode
	 *
	 * @var string
	 */
	protected $mode = 'month';

	/**
	 * The day the calendar week starts on
	 *
	 * @var string
	 */
	protected $start_of_week = '0';

	/**
	 * Beginning boundary for the current view
	 *
	 * @var string
	 */
	protected $view_start = '';

	/**
	 * Ending boundary for teh current view
	 *
	 * @var string
	 */
	protected $view_end = '';

	/**
	 * Duration of the view, from start to end, in seconds
	 *
	 * @var int
	 */
	protected $view_duration = 0;

	/**
	 * Unix timestamp of the start of the month
	 *
	 * @var int
	 */
	protected $month_start = 0;

	/**
	 * Unix timestamp for the end of the month
	 *
	 * @var int
	 */
	protected $month_end = 0;

	/**
	 * The year being viewed
	 *
	 * @var int
	 */
	protected $year = 2019;

	/**
	 * The month being viewed
	 *
	 * @var int
	 */
	protected $month = 1;

	/**
	 * The day being viewed
	 *
	 * @var int
	 */
	protected $day = 1;

	/**
	 * The exact day being viewed based on year/month/day
	 *
	 * @var string
	 */
	protected $today = '';

	/**
	 * Monthly_Books_List_Table constructor.
	 */
	public function __construct() {

		add_filter( 'book-database/books-table/extra-tablenav/filters/before', array( $this, 'calendar_nav' ) );

		// Start of the week
		$this->start_of_week = get_option( 'start_of_week', '0' );

		// Set year, month, & day.
		$this->year  = $this->get_year();
		$this->month = $this->get_month();
		$this->day   = $this->get_day();

		// Set "today" based on the current request.
		$this->today = strtotime( "{$this->year}/{$this->month}/{$this->day}" );

		parent::__construct();

		// View start
		$view_start = "{$this->year}-{$this->month}-01 00:00:00";

		// Month boundaries
		$this->month_start = mysql2date( 'U', $view_start );
		$this->month_end   = strtotime( date( 'Y-m-t 23:59:59', $this->month_start ) );

		// View end
		$view_end = date( 'Y-m-d H:i:s', $this->month_end );

		// Set the view
		$this->set_view( $view_start, $view_end );

	}

	/**
	 * Set the start, end, and duration of the current view
	 *
	 * @param string $start Start date
	 * @param string $end   End date
	 */
	protected function set_view( $start = '', $end = '' ) {

		$this->view_start    = $start;
		$this->view_end      = $end;
		$this->view_duration = strtotime( $end ) - strtotime( $start );

	}

	/**
	 * Get the current year
	 *
	 * @return int
	 */
	protected function get_year() {
		return isset( $_REQUEST['cy'] ) ? absint( $_REQUEST['cy'] ) : date( 'Y' );
	}

	/**
	 * Get the current month
	 *
	 * @return int
	 */
	protected function get_month() {
		return isset( $_REQUEST['cm'] ) ? absint( $_REQUEST['cm'] ) : date( 'n' );
	}

	/**
	 * Get the current day
	 *
	 * @return int
	 */
	protected function get_day() {
		return isset( $_REQUEST['cd'] ) ? absint( $_REQUEST['cd'] ) : date( 'j' );
	}

	/**
	 * Get a list of CSS classes for the list table <table> tag.
	 *
	 * @return array
	 */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'calendar', 'month', $this->_args['plural'] );
	}

	/**
	 * Start the week with a table row
	 */
	protected function get_row_start() {
		echo '<tr>';
	}

	/**
	 * End the week with a closed table row
	 */
	protected function get_row_end() {
		echo '</tr>';
	}

	/**
	 * TK
	 *
	 * @param int $iterator
	 * @param int $start_day
	 */
	protected function get_row_pad( $iterator = 1, $start_day = 1 ) {
		echo '<th class="padding ' . esc_attr( $this->get_day_classes( $iterator, $start_day ) ) . '"></th>';
	}

	/**
	 * Display the contents of a cell
	 *
	 * @param int $iterator
	 * @param int $start_day
	 */
	protected function get_row_cell( $iterator = 1, $start_day = 1 ) {

		// Calculate the day of the month.
		$day_of_month = (int) ( $iterator - (int) $start_day + 1 );

		// Link to add new event on this day.
		$add_book_for_day = add_query_arg( 'pub_date', urlencode( sprintf( '%d-%d-%d', $this->year, $this->month, $day_of_month ) ), get_books_admin_page_url( array(
			'view' => 'add'
		) ) );
		?>
		<td class="<?php echo esc_attr( $this->get_day_classes( $iterator, $start_day ) ); ?>">
			<span class="bdb-day-number">
				<?php echo esc_html( $day_of_month ); ?>
			</span>

			<a href="<?php echo esc_url( $add_book_for_day ); ?>" class="bdb-add-book-for-day" title="<?php esc_attr_e( 'Add new book published on this day' ); ?>">
				<i class="dashicons dashicons-plus"></i>
			</a>

			<div class="bdb-books-for-cell">
				<?php $this->get_books_for_cell( $day_of_month ); ?>
			</div>
		</td>
		<?php

	}

	/**
	 * Get the alreadsy queried books for a given day
	 *
	 * @param int    $iterator
	 * @param string $type
	 *
	 * @return array
	 */
	protected function get_queried_items( $iterator = 1, $type = 'items' ) {
		return $this->{$type}[ $iterator ] ?? array();
	}

	/**
	 * Render markup for individual books within a cell
	 *
	 * @param int    $iterator
	 * @param string $type
	 */
	protected function get_books_for_cell( $iterator = 1, $type = 'items' ) {

		// Get books.
		$books = $this->get_queried_items( $iterator, $type );

		if ( empty( $books ) ) {
			return;
		}

		foreach ( $books as $item ) {

			$book = new Book( $item );

			$edit_book_url = get_books_admin_page_url( array(
				'view'    => 'edit',
				'book_id' => $book->get_id()
			) );

			// Handle empty titles.
			$book_title = $book->get_title() ? $book->get_title() : __( '(No title)', 'book-database' );
			?>
			<a href="<?php echo esc_url( $edit_book_url ); ?>" class="bdb-calendar-book-link">
				<?php
				if ( $book->get_cover_id() ) {
					echo $book->get_cover( 'large' );
				} else {
					$author_names = ! empty( $item->author_name ) ? explode( ',', $item->author_name ) : array();
					$author_names = array_map( 'esc_html', $author_names );
					printf( '%s by %s', $book_title, implode( ', ', $author_names ) );
				}

				?>
				<span class="bdb-calendar-book-flags">
					<?php
					// Designate read
					$read = count_reading_logs( array(
						'book_id'             => $book->get_id(),
						'date_finished_query' => array(
							'after' => '0000-00-00 00:00:00'
						)
					) );
					if ( ! empty( $read ) ) {
						echo '<span class="dashicons dashicons-book"></span>';
					}

					// Designate reviewed
					$reviews = count_reviews( array(
						'book_id' => $book->get_id()
					) );
					if ( ! empty( $reviews ) ) {
						echo '<span class="dashicons dashicons-welcome-write-blog"></span>';
					}
					?>
				</span>
			</a>
			<?php

		}

	}

	/**
	 * Determines whether or not a given date is today
	 *
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 *
	 * @return bool
	 */
	protected function is_today( $month, $day, $year ) {
		$_month = (bool) ( $month == date( 'n' ) );
		$_day   = (bool) ( $day == date( 'j' ) );
		$_year  = (bool) ( $year == date( 'Y' ) );

		return (bool) ( true === $_month && true === $_day && true === $_year );
	}

	/**
	 * Get classes for a table cell
	 *
	 * @param int $iterator
	 * @param int $start_day
	 *
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

		$is_today = $this->is_today( $this->month, $offset, $this->year ) ? 'today' : '';

		// Assemble classes
		$classes = array(
			$this->the_day,
			$day_key,
			$is_today,
			$position,
			$cell_number,
			$day_number,
			$month_number,
			$year_number
		);

		// Sanitize classes.
		$classes = array_map( 'sanitize_html_class', $classes );

		return implode( ' ', $classes );

	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		$days = $this->get_days_for_week();

		return $days[1];
	}

	/**
	 * Get the days for any given week
	 *
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
	 * Display the table.
	 *
	 * @see WP_List_Table::display()
	 *
	 * @return void
	 */
	public function display() {
		$this->display_tablenav( 'top' );
		?>
		<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list" data-wp-lists="list:<?php echo $this->_args['singular']; ?>">
			<?php $this->display_mode(); ?>
			</tbody>

			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Display
	 */
	protected function display_mode() {

		// Get timestamp for first & last days of month.
		$timestamp  = mktime( 0, 0, 0, $this->month, 1, $this->year );
		$max_day    = date( 't', $timestamp );
		$this_month = getdate( $timestamp );

		// Get days for week, in order, to set the start day.
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
	 * Generate the pagination
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

		$args = array(
			'small'  => '1 month',
			'large'  => '1 year',
			'labels' => array(
				'next_small' => esc_html__( 'Next Month', 'book-database' ),
				'next_large' => esc_html__( 'Next Year', 'book-database' ),
				'prev_small' => esc_html__( 'Previous Month', 'book-database' ),
				'prev_large' => esc_html__( 'Previous Year', 'book-database' ),
				'today'      => esc_html__( 'Today', 'book-database' )
			)
		);

		$today = $this->get_base_url();

		// Calculate previous & next weeks & months
		$prev_small = strtotime( "-{$args['small']}", $this->today );
		$next_small = strtotime( "+{$args['small']}", $this->today );
		$prev_large = strtotime( "-{$args['large']}", $this->today );
		$next_large = strtotime( "+{$args['large']}", $this->today );

		// Week
		$prev_small_d = date( 'j', $prev_small );
		$prev_small_m = date( 'n', $prev_small );
		$prev_small_y = date( 'Y', $prev_small );
		$next_small_d = date( 'j', $next_small );
		$next_small_m = date( 'n', $next_small );
		$next_small_y = date( 'Y', $next_small );

		// Month
		$prev_large_d = date( 'j', $prev_large );
		$prev_large_m = date( 'n', $prev_large );
		$prev_large_y = date( 'Y', $prev_large );
		$next_large_d = date( 'j', $next_large );
		$next_large_m = date( 'n', $next_large );
		$next_large_y = date( 'Y', $next_large );

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
			<a class="previous-page button" href="<?php echo esc_url( $prev_large_link ); ?>" title="<?php echo esc_attr( $args['labels']['prev_large'] ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['prev_large'] ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="previous-page button" href="<?php echo esc_url( $prev_small_link ); ?>" title="<?php echo esc_attr( $args['labels']['prev_small'] ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['prev_small'] ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>

			<a class="previous-page button" href="<?php echo esc_url( $today ); ?>" title="<?php echo esc_attr( $args['labels']['today'] ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['today'] ); ?></span>
				<span aria-hidden="true">&Colon;</span>
			</a>

			<a class="next-page button" href="<?php echo esc_url( $next_small_link ); ?>" title="<?php echo esc_attr( $args['labels']['next_small'] ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['next_small'] ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>

			<a class="next-page button" href="<?php echo esc_url( $next_large_link ); ?>" title="<?php echo esc_attr( $args['labels']['next_large'] ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $args['labels']['next_large'] ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>
		</div>
		<?php

	}

	/**
	 * Remove the saerch box
	 *
	 * @param string $text
	 * @param string $input_id
	 *
	 * @return string
	 */
	public function search_box( $text, $input_id ) {
		return '';
	}

	/**
	 * Get available columns
	 *
	 * @return array
	 */
	public function get_columns() {

		static $columns = null;

		// Calculate if not calculated already.
		if ( null === $columns ) {
			$days = $this->get_days_for_week();

			$columns = array();
			foreach ( $days as $key => $day ) {
				$columns[ $day ] = $GLOBALS['wp_locale']->get_weekday( $key );
			}
		}

		return $columns;

	}

	/**
	 * Get the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Get the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * No bulk actions
	 */
	public function process_bulk_actions() {

	}

	/**
	 * Display navigation for changing month/year.
	 *
	 * @return void
	 */
	public function calendar_nav() {

		$months = array();

		for ( $month_index = 1; $month_index <= 12; $month_index ++ ) {
			$months[ $month_index ] = $GLOBALS['wp_locale']->get_month( $month_index );
		}
		?>
		<label for="bdb-filter-by-month" class="screen-reader-text"><?php _e( 'Switch to this month', 'book-database' ); ?></label>
		<select id="bdb-filter-by-month" name="cm">
			<?php foreach ( $months as $month_number => $month_name ) : ?>
				<option value="<?php echo esc_attr( $month_number ); ?>" <?php selected( $month_number, $this->month ); ?>><?php echo esc_html( $month_name ); ?></option>
			<?php endforeach; ?>
		</select>

		<label for="bdb-filter-by-year" class="screen-reader-text"><?php _e( 'Switch to this year', 'book-database' ); ?></label>
		<input type="number" id="bdb-filter-by-year" name="cy" value="<?php echo esc_attr( $this->year ); ?>">
		<?php

	}

	/**
	 * Get query args
	 *
	 * @param bool $count
	 *
	 * @return array
	 */
	protected function get_query_args( $count = false ) {

		$args                 = parent::get_query_args( $count );
		$args['number']       = 500;
		$args['order']        = 'ASC';
		$args['orderby']      = 'book.pub_date';
		$args['book_query'][] = array(
			'field' => 'pub_date',
			'value' => array(
				'after'     => $this->view_start,
				'before'    => $this->view_end,
				'inclusive' => true
			)
		);

		return $args;

	}

	/**
	 * Setup the final data for the table.
	 *
	 * @return void
	 */
	public function prepare_items() {

		parent::prepare_items();

		// Rearrange books into an array keyed by day of the month.
		$items       = $this->items;
		$this->items = array();
		foreach ( $items as $item ) {
			$this->setup_item( $item, $this->per_page );
		}

	}

	/**
	 * Add a book to the item array, keyed by day
	 *
	 * @param object $item
	 * @param int    $max
	 */
	protected function setup_item( $item, $max = 10 ) {

		$pub_date = $item->pub_date;
		$day      = date( 'j', strtotime( $pub_date ) );

		$this->set_queried_item( $day, 'items', $item->id, $item );

	}

	/**
	 * Set a queried item in its proper array position.
	 *
	 * @param int          $iterator
	 * @param string       $type
	 * @param int          $item_id
	 * @param object|array $data
	 *
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
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		// Do nothing; calendars always have rows
	}
}
