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
use function Book_Database\format_date;
use function Book_Database\generate_book_index_title;
use function Book_Database\get_attached_book_terms;
use function Book_Database\get_book_taxonomies;
use function Book_Database\get_book_terms;
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

	// Add taxonomies last.
	$function = __NAMESPACE__ . '\book_taxonomy_fields';
	if ( function_exists( $function ) ) {
		add_action( 'book-database/book-edit/information-fields', $function );
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

/**
 * Field: book author
 *
 * @param Book|false $book
 */
function book_author_field( $book ) {

	$authors = ! empty( $book ) ? $book->get_author_names( true ) : '';

	ob_start();
	?>
	<div id="bdb-tags-author" class="bdb-tags-wrap" data-taxonomy="author">
		<div class="jaxtag">
			<div class="nojs-tags hide-if-js">
				<label for="bdb-input-tag-author"><?php _e( 'Enter the name of the author(s)', 'book-database' ); ?></label>
				<textarea name="book_terms[author]" data-taxonomy="author" rows="3" cols="20" id="bdb-input-tag-author"><?php echo esc_textarea( $authors ); ?></textarea>
			</div>
			<div class="bdb-ajaxtag hide-if-no-js">
				<p>
					<label for="bdb-new-author" class="screen-reader-text"><?php _e( 'Enter the name of the author(s)', 'book-database' ); ?></label>
					<input type="text" id="bdb-new-author" class="form-input-tip regular-text bdb-new-tag" size="16" autocomplete="off" value="">
					<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>" tabindex="3">
				</p>
			</div>
		</div>
		<div class="bdb-tags-checklist"></div>
	</div>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Author(s)', 'book-database' ),
		'field' => ob_get_clean()
	) );

}

/**
 * Field: book series
 *
 * @param Book|false $book
 */
function book_series_field( $book ) {

	$series_name     = ! empty( $book ) ? $book->get_series_name() : '';
	$series_position = ! empty( $book ) ? $book->get_series_position() : '';

	ob_start();
	?>
	<div id="bdb-book-series-name-wrap">
		<input type="text" id="bdb-book-series-name" name="series_name" class="regular-text" value="<?php echo esc_attr( $series_name ); ?>">
		<label for="bdb-book-series-name" class="description"><?php _e( 'Series name', 'book-database' ); ?></label>
	</div>
	<div id="bdb-book-series-position-wrap">
		<input type="text" id="bdb-book-series-position" name="series_position" class="regular-text" value="<?php echo esc_attr( $series_position ); ?>">
		<label for="bdb-book-series-position" class="description"><?php _e( 'Position in the series', 'book-database' ); ?></label>
	</div>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Series', 'book-database' ),
		'field' => ob_get_clean()
	) );

}

/**
 * Field: book publication date
 *
 * @param Book|false $book
 */
function book_pub_date_field( $book ) {

	$pub_date = ! empty( $book ) ? $book->get_pub_date( true ) : '';

	if ( empty( $pub_date ) && ! empty( $_GET['pub_date'] ) ) {
		$pub_date = format_date( $_GET['pub_date'] );
	}

	ob_start();
	?>
	<input type="text" id="bdb-book-pub-date" class="regular-text" name="pub_date" value="<?php echo esc_attr( $pub_date ); ?>">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Publication Date', 'book-database' ),
		'id'    => 'bdb-book-pub-date',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: pages
 *
 * @param Book|false $book
 */
function book_pages_field( $book ) {

	$pages = ! empty( $book ) ? $book->get_pages() : '';

	ob_start();
	?>
	<input type="number" id="bdb-book-pages" name="pages" value="<?php echo esc_attr( $pages ); ?>">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Pages', 'book-database' ),
		'id'    => 'bdb-book-pub-date',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: Goodreads URL
 *
 * @param Book|false $book
 */
function book_goodreads_url_field( $book ) {

	$url = ! empty( $book ) ? $book->get_goodreads_url() : '';

	ob_start();
	?>
	<input type="url" id="bdb-book-goodreads-url" class="regular-text" name="goodreads_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Goodreads URL', 'book-database' ),
		'id'    => 'bdb-book-goodreads-url',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: buy URL
 *
 * @param Book|false $book
 */
function book_buy_link_field( $book ) {

	$url = ! empty( $book ) ? $book->get_buy_link() : '';

	ob_start();
	?>
	<input type="url" id="bdb-book-buy-url" class="regular-text" name="buy_link" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Purchase URL', 'book-database' ),
		'id'    => 'bdb-book-buy-url',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: synopsis
 *
 * @param Book|false $book
 */
function book_synopsis_field( $book ) {

	$synopsis = ! empty( $book ) ? $book->get_synopsis() : '';

	ob_start();
	?>
	<textarea id="bdb-book-synopsis" class="large-textarea" name="synopsis"><?php echo esc_textarea( $synopsis ); ?></textarea>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Synopsis', 'book-database' ),
		'id'    => 'bdb-book-synopsis',
		'field' => ob_get_clean()
	) );

}

/**
 * Field: taxonomies
 *
 * @param Book|false $book
 */
function book_taxonomy_fields( $book ) {

	$taxonomies = get_book_taxonomies( array(
		'slug__not_in' => array( 'author' ),
		'number'       => 100
	) );

	if ( empty( $taxonomies ) ) {
		return;
	}

	foreach ( $taxonomies as $taxonomy ) {

		// Get this taxonomy's terms assigned to this book.
		$book_terms = array();
		if ( $book instanceof Book ) {
			$book_terms = get_attached_book_terms( $book->get_id(), $taxonomy->get_slug(), array( 'fields' => 'names' ) );
		}

		ob_start();

		if ( 'checkbox' === $taxonomy->get_format() ) {

			// "Categories"

			// Get all terms EXCEPT the ones already checked.
			$all_terms = get_book_terms( array(
				'number'       => 300,
				'taxonomy'     => $taxonomy->get_slug(),
				'name__not_in' => $book_terms,
				'fields'       => 'name',
				'orderby'      => 'name',
				'order'        => 'ASC'
			) );

			$final_terms = $book_terms + $all_terms;
			?>
			<div id="bdb-checkboxes-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="bdb-taxonomy-checkboxes" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>" data-name="<?php echo esc_attr( 'book_terms[' . $taxonomy->get_slug() . '][]' ); ?>">
				<div class="bdb-checkbox-wrap">
					<?php
					foreach ( $final_terms as $term_name ) {
						?>
						<label for="<?php echo esc_attr( sanitize_html_class( sanitize_key( sprintf( '%s-%s', $taxonomy->get_slug(), $term_name ) ) ) ); ?>">
							<input type="checkbox" id="<?php echo esc_attr( sanitize_html_class( sanitize_key( sprintf( '%s-%s', $taxonomy->get_slug(), $term_name ) ) ) ); ?>" class="bdb-checkbox" name="book_terms[<?php echo esc_attr( $taxonomy->get_slug() ); ?>][]" value="<?php echo esc_attr( $term_name ); ?>" <?php checked( in_array( $term_name, $book_terms ) ); ?>>
							<?php echo esc_html( $term_name ); ?>
						</label>
						<?php
					}
					?>
				</div>
				<div class="bdb-new-checkbox-term">
					<label for="bdb-new-checkbox-term-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="screen-reader-text"><?php printf( esc_html__( 'Enter the name of a new %s', 'book-database' ), esc_html( lcfirst( $taxonomy->get_name() ) ) ); ?></label>
					<input type="text" id="bdb-new-checkbox-term-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="regular-text bdb-new-checkbox-term-value">
					<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>">
				</div>
			</div>
			<?php

		} else {

			// "Tags"

			?>
			<div id="bdb-tags-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="bdb-tags-wrap" data-type="<?php echo esc_attr( $taxonomy->get_slug() ); ?>">
				<div class="jaxtag">
					<div class="nojs-tags hide-if-js">
						<label for="bdb-input-tag-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>"><?php printf( __( 'Enter the name of the %s', 'book-database' ), esc_html( $taxonomy->get_name() ) ); ?></label>
						<textarea name="book_terms[<?php echo esc_attr( $taxonomy->get_slug() ); ?>]" rows="3" cols="20" id="bdb-input-tag-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>"><?php echo esc_textarea( implode( ', ', $book_terms ) ); ?></textarea>
					</div>
					<div class="bdb-ajaxtag hide-if-no-js">
						<p>
							<label for="bdb-new-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-term" class="screen-reader-text"><?php printf( __( 'Enter the name of the %s', 'book-database' ), esc_html( $taxonomy->get_name() ) ); ?></label>
							<input type="text" id="bdb-new-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-term" class="form-input-tip regular-text bdb-new-tag" size="16" autocomplete="off" value="">
							<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>" tabindex="3">
						</p>
					</div>
				</div>
				<div class="bdb-tags-checklist"></div>
			</div>
			<?php

		}

		book_database()->get_html()->meta_row( array(
			'label' => $taxonomy->get_name(),
			'field' => ob_get_clean()
		) );

	}

}

/**
 * Field: books by this author
 *
 * @param Book|false $book
 */
function books_by_author( $book ) {

	// Don't show when adding a new book.
	if ( ! $book instanceof Book ) {
		return;
	}

	$authors = $book->get_authors();

	if ( empty( $authors ) ) {
		return;
	}



}

add_action( 'book-database/book-edit/after-save-box', __NAMESPACE__ . '\books_by_author' );