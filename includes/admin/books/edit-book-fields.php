<?php
/**
 * Initialize Fields
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Admin\Fields;

use Book_Database\Book;
use function Book_Database\book_database;
use function Book_Database\generate_book_index_title;
use function Book_Database\get_enabled_book_fields;

/**
 * Load all enabled fields into the Add/Edit book display.
 */
function hook_fields() {

	$enabled_fields = get_enabled_book_fields();

	if ( empty( $enabled_fields ) ) {
		return;
	}

	foreach ( $enabled_fields as $key => $options ) {
		$function = __NAMESPACE__ . '\book_' . $key . '_field';

		if ( ! function_exists( $function ) ) {
			continue;
		}

		add_action( 'book-database/book-edit/information-fields', $function );

		// Add title alt field after title.
		if ( 'title' === $key ) {
			$function = __NAMESPACE__ . '\book_index_title_field';

			if ( function_exists( $function ) ) {
				add_action( 'book-database/book-edit/information-fields', $function );
			}
		}
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\hook_fields' );

/**
 * Field: book cover
 *
 * @param Book|false $book
 */
function book_cover_field( $book ) {

	$cover_id  = ! empty( $book ) ? $book->get_cover_id() : 0;
	$cover_url = ! empty( $book ) ? $book->get_cover_url( 'large' ) : '';

	ob_start();
	?>
	<img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php esc_attr_e( 'Book cover', 'book-database' ); ?>" id="bdb-cover-image" style="<?php echo empty( $cover_url ) ? 'display: none;' : ''; ?>">

	<div class="bdb-cover-image-fields" data-image="#bdb-cover-image" data-image-id="#bdb-cover-id">
		<button class="bdb-upload-image button"><?php esc_html_e( 'Upload Image', 'book-database' ); ?></button>
		<button class="bdb-remove-image button" style="<?php echo empty( $cover_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'book-database' ); ?></button>
	</div>
	<input type="hidden" id="bdb-cover-id" name="cover_id" value="<?php echo esc_attr( $cover_id ); ?>">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Cover Image', 'book-database' ),
		'field' => ob_get_clean()
	) );

}

/**
 * Field: book title
 *
 * @param Book|false $book
 */
function book_title_field( $book ) {

	ob_start();
	?>
	<input type="text" id="bdb-book-title" class="regular-text" name="title" value="<?php echo ! empty( $book ) ? esc_attr( $book->get_title() ) : ''; ?>">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Book Title', 'book-database' ),
		'id'    => 'bdb-book-title',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: book index title
 *
 * @param Book|false $book
 */
function book_index_title_field( $book ) {

	$original_title  = ! empty( $book ) ? $book->get_title() : '';
	$index_title     = ! empty( $book ) ? $book->get_index_title() : '';
	$generated_title = generate_book_index_title( $original_title );
	$selected        = 'custom';

	$choices = array(
		'original' => $original_title
	);

	if ( ! empty( $generated_title ) ) {
		$choices[ $generated_title ] = $generated_title;
	}

	if ( empty( $index_title ) || $original_title == $index_title ) {
		$selected = 'original';
	} elseif ( ! empty( $choices[ $index_title ] ) ) {
		$selected = $choices[ $index_title ];
	}

	$choices['custom'] = __( 'Custom', 'book-database' );

	ob_start();
	?>
	<select id="bdb-book-index-title" name="index_title">
		<?php foreach ( $choices as $choice_key => $choice_value ) : ?>
			<option value="<?php echo esc_attr( $choice_key ); ?>" <?php selected( $choice_key, $selected ); ?>><?php echo esc_html( $choice_value ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="text" id="bdb-book-index-title-custom" class="regular-text" name="index_title_custom" value="<?php echo esc_attr( $index_title ); ?>">
	<p class="description"><?php _e( 'Used when ordering in the review index and determining which letter the book title should fall under.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Index Title', 'book-database' ),
		'id'    => 'bdb-book-index-title',
		'field' => ob_get_clean()
	) );

}