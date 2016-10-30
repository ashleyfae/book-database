<?php
/**
 * Modal Template: Book Information
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$book = new BDB_Book( 0 );
?>

<div class="bookdb-book-form bookdb-book-details-form">
	<?php do_action( 'book-database/book-edit/information-fields', $book ); ?>
</div>
