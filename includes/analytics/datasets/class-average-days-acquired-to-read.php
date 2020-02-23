<?php
/**
 * Dataset: Average number of days between acquiring a book and starting to read a book
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Average_Days_Acquired_to_Read
 *
 * @package Book_Database\Analytics
 */
class Average_Days_Acquired_to_Read extends Dataset {

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT ROUND( AVG( DATEDIFF( date_started, date_acquired ) ) ) + 1 AS number_days_to_start
		FROM {$tbl_editions} AS edition
		INNER JOIN {$tbl_log} AS log ON log.id = (
			SELECT id
			FROM {$tbl_log} AS log2
			WHERE edition_id = edition.id
			AND date_started IS NOT NULL
			ORDER BY date_started
			LIMIT 1
		)
		WHERE date_acquired IS NOT NULL
		{$this->get_date_condition( 'date_acquired', 'date_acquired' )}";

		$days = $this->get_db()->get_var( $query );

		return is_null( $days ) ? '&ndash;' : absint( $days );

	}
}