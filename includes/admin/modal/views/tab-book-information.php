<?php
/**
 * Modal Template: Book Information
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$book = new BDB_Book( 0 );
?>

<div id="bookdb-search-for-existing-book" class="bookdb-book-form">
	<h3><?php esc_html_e( 'Insert Existing Book', 'book-database' ); ?></h3>
	<div class="bookdb-box-row">
		<label for="bookdb-search-existing"><?php _e( 'Search by book title or author', 'book-database' ); ?></label>
		<div class="bookdb-input-wrapper">
			<input type="text" id="bookdb-search-existing" placeholder="<?php esc_attr_e( 'Title or author name', 'book-database' ); ?>">
			<label for="bookdb-search-field" class="screen-reader-text"><?php esc_html_e( 'Select which field to search in', 'book-database' ); ?></label>
			<select id="bookdb-search-field">
				<option value="title" selected><?php esc_html_e( 'Title', 'book-database' ); ?></option>
				<option value="author"><?php esc_html_e( 'Author', 'book-database' ); ?></option>
			</select>
			<div class="clear"></div>
			<button id="bookdb-search-existing-book" class="button button-primary"><?php _e( 'Search', 'book-database' ); ?></button>
		</div>
	</div>
</div>

<div id="bookdb-existing-book-results" class="bookdb-book-form">

</div>

<div class="bookdb-book-form bookdb-book-details-form">
	<h3><?php esc_html_e( 'Insert New Book', 'book-database' ); ?></h3>
	<?php do_action( 'book-database/book-edit/information-fields', $book ); ?>
</div>