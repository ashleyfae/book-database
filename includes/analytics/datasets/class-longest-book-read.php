<?php
/**
 * Dataset: Longest book read
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Longest_Book_Read
 *
 * @package Book_Database\Analytics
 */
class Longest_Book_Read extends Shortest_Book_Read {

	protected $orderby = 'DESC';

}