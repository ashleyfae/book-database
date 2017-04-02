<?php
/**
 * Book Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Below: Book Information Fields
 */

function bdb_setup_book_meta_fields() {
	$enabled_fields = bdb_get_option( 'book_layout', bdb_get_default_book_field_values() );

	if ( ! is_array( $enabled_fields ) ) {
		return;
	}

	foreach ( $enabled_fields as $key => $options ) {
		$function = 'bdb_book_' . $key . '_field';

		if ( ! function_exists( $function ) ) {
			continue;
		}

		add_action( 'book-database/book-edit/information-fields', $function );

		// Add title alt field after title.
		if ( 'title' == $key ) {
			add_action( 'book-database/book-edit/information-fields', 'bdb_book_title_alt_field' );
		}
	}

	// Add taxonomies last.
	add_action( 'book-database/book-edit/information-fields', 'bdb_book_taxonomy_fields' );
}

add_action( 'init', 'bdb_setup_book_meta_fields', 10 );

/**
 * Field: Book Cover
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_cover_field( $book ) {
	$cover_id = $book->get_cover_id();
	$url      = $book->get_cover_url( 'large' );

	ob_start();

	$style = $url ? '' : 'display: none;';

	echo '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( sprintf( __( 'Book cover for %s', 'book-database' ), $book->get_title() ) ) . '" id="bookdb-cover-image" style="' . esc_attr( $style ) . '">';

	?>
    <div class="bookdb-cover-image-fields" data-image="#bookdb-cover-image" data-image-id="#book_cover_id">
        <button class="button bookdb-upload-image"><?php esc_html_e( 'Upload Image', 'book-database' ); ?></button>
        <button class="button bookdb-remove-image" style="<?php echo ! $cover_id ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'book-database' ); ?></button>
    </div>

    <input type="hidden" id="book_cover_id" name="cover" value="<?php echo esc_attr( absint( $cover_id ) ); ?>">
	<?php

	$field = ob_get_clean();

	book_database()->html->meta_row( 'raw', array(
		'label' => __( 'Cover Image', 'book-database' ),
		'field' => $field
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_cover_field' );

/**
 * Field: Book Title
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_title_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => sprintf( __( '%s Title', 'book-database' ), bdb_get_label_singular() )
	), array(
		'id'    => 'book_title',
		'name'  => 'title',
		'value' => $book->get_title()
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_title_field' );

/**
 * Field: Alternative Book Title
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_title_alt_field( $book ) {
	$index_title = $book->get_index_title();
	$choices     = $book->get_title_choices();

	if ( ! $index_title || $book->get_title() == $index_title ) {
		$selected = 'original';
	} elseif ( array_key_exists( $index_title, $choices ) ) {
		$selected = $choices[ $index_title ];
	} else {
		$selected = 'custom';
	}

	$select = book_database()->html->select( array(
		'options'          => $book->get_title_choices( true ),
		'id'               => 'index_title',
		'name'             => 'index_title',
		'show_option_all'  => false,
		'show_option_none' => false,
		'selected'         => $selected
	) );

	$text = book_database()->html->text( array(
		'id'    => 'index_title_custom',
		'name'  => 'index_title_custom',
		'value' => $book->get_index_title(),
		'desc'  => __( 'Used when ordering in the review index and determining which letter the book title should fall under.', 'book-database' )
	) );

	book_database()->html->meta_row( 'raw', array(
		'label' => __( 'Index Title', 'book-database' ),
		'field' => $select . $text
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_title_alt_field' );

/**
 * Field: Author
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_author_field( $book ) {
	$authors = $book->get_author_names( true );

	ob_start();

	?>
    <div id="bookdb-tags-author" class="bookdb-tags-wrap" data-type="author">
        <div class="jaxtag">
            <div class="nojs-tags hide-if-js">
                <label for="bookdb-input-tag"><?php echo apply_filters( 'book-database/book-edit/authors/tags-desc', __( 'Enter the name of the author', 'easy-content-upgrades' ) ); ?></label>
                <textarea name="book_terms[author]" data-type="author" rows="3" cols="20" id="bookdb-input-tag-author"><?php echo esc_textarea( $authors ); ?></textarea>
            </div>
            <div class="bookdb-ajaxtag hide-if-no-js">
                <p>
                    <input type="text" name="bookdb-new-authors" class="form-input-tip regular-text bookdb-new-tag" size="16" autocomplete="off" value="">
                    <input type="button" class="button" value="<?php esc_attr_e( 'Add' ); ?>" tabindex="3">
                </p>
            </div>
        </div>
        <div class="bookdb-tags-checklist"></div>
    </div>
	<?php

	$field = ob_get_clean();

	book_database()->html->meta_row( 'raw', array( 'label' => __( 'Author(s)', 'book-database' ), 'field' => $field ) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_author_field' );

/**
 * Field: Book Series
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_series_field( $book ) {

	$series_args          = array(
		'id'    => 'book_series_name',
		'name'  => 'series_name',
		'value' => $book->get_series_name(),
		'desc'  => esc_html__( 'Series name', 'book-database' )
	);
	$series_position_args = array(
		'id'    => 'book_series_position',
		'name'  => 'series_position',
		'value' => $book->get_series_position(),
		'desc'  => esc_html__( 'Position in the series', 'book-database' )
	);
	?>
    <div id="bookdb-book-series-wrap" class="bookdb-box-row">
        <label><?php _e( 'Series', 'book-database' ); ?></label>
        <div class="bookdb-input-wrapper">
            <div id="bookdb-book-series-name-wrap">
				<?php echo book_database()->html->text( $series_args ); ?>
            </div>

            <div id="bookdb-book-series-position-wrap">
				<?php echo book_database()->html->text( $series_position_args ); ?>
            </div>
        </div>
    </div>
	<?php
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_series_field' );

/**
 * Field: Publication Date
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_pub_date_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Publication Date', 'book-database' )
	), array(
		'id'    => 'book_pub_date',
		'name'  => 'pub_date',
		'value' => $book->get_formatted_pub_date(),
		'desc'  => esc_html__( 'Format: September 1st 2016', 'book-database' )
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_pub_date_field' );

/**
 * Field: Pages
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_pages_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Pages', 'book-database' )
	), array(
		'id'    => 'book_pages',
		'name'  => 'pages',
		'value' => $book->get_pages(),
		'type'  => 'number'
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_pages_field' );

/**
 * Field: Goodreads URL
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_goodreads_url_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Goodreads URL', 'book-database' )
	), array(
		'id'          => 'book_goodreads_url',
		'name'        => 'goodreads_url',
		'value'       => $book->get_goodreads_url(),
		'placeholder' => 'http://',
		'type'        => 'url'
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_goodreads_url_field' );

/**
 * Field: Buy Link
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_buy_link_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Purchase Link', 'book-database' )
	), array(
		'id'          => 'book_buy_link',
		'name'        => 'buy_link',
		'value'       => $book->get_buy_link(),
		'placeholder' => 'http://',
		'type'        => 'url'
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_buy_link_field' );

/**
 * Field: Taxonomies
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_taxonomy_fields( $book ) {
	$taxonomies = bdb_get_option( 'taxonomies' );

	if ( ! $taxonomies || ! is_array( $taxonomies ) ) {
		return;
	}

	foreach ( $taxonomies as $taxonomy_options ) {

		$book_terms = bdb_get_book_terms( $book->ID, $taxonomy_options['id'], array( 'fields' => 'names' ) ); // Terms assigned to this book.

		ob_start();

		if ( 'checkbox' == $taxonomy_options['display'] ) {

			// "Categories"

			$temp_all_terms = bdb_get_terms( array(
				'number'  => - 1,
				'type'    => $taxonomy_options['id'],
				'fields'  => 'names',
				'orderby' => 'name',
				'order'   => 'ASC'
			) );
			$all_terms      = array();

			if ( ! is_array( $temp_all_terms ) ) {
				$temp_all_terms = array();
			}

			foreach ( $temp_all_terms as $term_name ) {
				$all_terms[ $term_name ] = $term_name;
			}

			$checks = book_database()->html->multicheck( array(
				'id'      => $taxonomy_options['id'],
				'name'    => 'book_terms[' . $taxonomy_options['id'] . '][]',
				'current' => $book_terms,
				'choices' => $all_terms
			) );

			?>
            <div id="dbd-checkboxes-<?php echo esc_attr( $taxonomy_options['id'] ); ?>" class="bookdb-taxonomy-checkboxes" data-type="<?php echo esc_attr( $taxonomy_options['id'] ); ?>" data-name="<?php echo esc_attr( 'book_terms[' . $taxonomy_options['id'] . '][]' ); ?>">
                <div class="bookdb-checkbox-wrap">
					<?php echo $checks; ?>
                </div>
                <div class="bookdb-new-checkbox-term">
                    <label for="bookdb-new-checkbox-term-<?php echo esc_attr( $taxonomy_options['id'] ); ?>" class="screen-reader-text"><?php printf( esc_html__( 'Enter the name of a new %s', 'book-database' ), $taxonomy_options['name'] ); ?></label>
                    <input type="text" id="bookdb-new-checkbox-term-<?php echo esc_attr( $taxonomy_options['id'] ); ?>" name="bookdb-new-term" class="regular-text bookdb-new-checkbox-term-value">
                    <input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>">
                </div>
            </div>
			<?php

		} else {

			// "Tags"
			ob_start();
			?>
            <div id="bookdb-tags-<?php echo esc_attr( $taxonomy_options['id'] ); ?>" class="bookdb-tags-wrap" data-type="<?php echo esc_attr( $taxonomy_options['id'] ); ?>">
                <div class="jaxtag">
                    <div class="nojs-tags hide-if-js">
                        <label for="bookdb-input-tag-<?php echo esc_attr( $taxonomy_options['id'] ); ?>"><?php echo apply_filters( 'book-database/book-edit/authors/tags-desc', __( 'Enter the name of the author', 'easy-content-upgrades' ) ); ?></label>
                        <textarea name="book_terms[<?php echo esc_attr( $taxonomy_options['id'] ); ?>]" rows="3" cols="20" id="bookdb-input-tag-<?php echo esc_attr( $taxonomy_options['id'] ); ?>" data-type="<?php echo esc_attr( $taxonomy_options['id'] ); ?>"><?php echo esc_textarea( implode( ', ', $book_terms ) ); ?></textarea>
                    </div>
                    <div class="bookdb-ajaxtag hide-if-no-js">
                        <p>
                            <input type="text" name="bookdb-new-term" class="form-input-tip regular-text bookdb-new-tag" size="16" autocomplete="off" value="">
                            <input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>" tabindex="3">
                        </p>
                    </div>
                </div>
                <div class="bookdb-tags-checklist"></div>
            </div>
			<?php

		}

		book_database()->html->meta_row( 'raw', array(
			'label' => esc_html( $taxonomy_options['name'] ),
			'field' => ob_get_clean()
		) );

	}
}

/**
 * Field: Synopsis
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_synopsis_field( $book ) {
	book_database()->html->meta_row( 'textarea', array(
		'label' => __( 'Synopsis', 'book-database' )
	), array(
		'id'    => 'book_synopsis',
		'name'  => 'synopsis',
		'value' => $book->get_synopsis()
	) );
}

//add_action( 'book-database/book-edit/information-fields', 'bdb_book_synopsis_field' );

/*
 * Below: Saving Functions
 */

/**
 * Save Book
 *
 * Triggers after saving a book via Book Reviews > Book Library.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_save_book() {

	$nonce = isset( $_POST['bdb_save_book_nonce'] ) ? $_POST['bdb_save_book_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_book' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit books.', 'book-database' ) );
	}

	$book_id = absint( $_POST['book_id'] );

	$book_data = array(
		'ID' => $book_id
	);

	// Title
	if ( isset( $_POST['title'] ) ) {
		$book_data['title'] = $_POST['title'];
	}

	// Index Title
	if ( isset( $_POST['index_title'] ) && 'original' != $_POST['index_title'] ) {
		$book_data['index_title'] = ( 'custom' != $_POST['index_title'] ) ? $_POST['index_title'] : $_POST['index_title_custom'];
	} elseif ( isset( $_POST['index_title'] ) && 'original' == $_POST['index_title'] && isset( $book_data['title'] ) ) {
		$book_data['index_title'] = $book_data['title'];
	}

	// Cover
	if ( isset( $_POST['cover'] ) ) {
		$book_data['cover'] = absint( $_POST['cover'] );
	}

	// Series Name
	if ( isset( $_POST['series_name'] ) ) {
		$book_data['series_name'] = $_POST['series_name'];
	}

	// Series Position
	if ( isset( $_POST['series_position'] ) ) {
		$book_data['series_position'] = $_POST['series_position'];
	}

	// Pub Date
	if ( isset( $_POST['pub_date'] ) ) {
		$book_data['pub_date'] = $_POST['pub_date'];
	}

	// Pages
	if ( isset( $_POST['pages'] ) ) {
		$book_data['pages'] = $_POST['pages'];
	}

	// Synopsis
	if ( isset( $_POST['synopsis'] ) ) {
		$book_data['synopsis'] = $_POST['synopsis'];
	}

	// Goodreads URL
	if ( isset( $_POST['goodreads_url'] ) ) {
		$book_data['goodreads_url'] = $_POST['goodreads_url'];
	}

	// Buy link
	if ( isset( $_POST['buy_link'] ) ) {
		$book_data['buy_link'] = $_POST['buy_link'];
	}

	$terms = array();

	if ( isset( $_POST['book_terms'] ) && is_array( $_POST['book_terms'] ) ) {
		foreach ( $_POST['book_terms'] as $type => $term_string ) {
			$type = bdb_sanitize_key( $type );
			if ( is_array( $term_string ) ) {
				$term_array = $term_string;
			} else {
				$term_array = $term_string ? explode( ',', $term_string ) : array();
			}
			$terms[ $type ] = array_map( 'trim', $term_array );
		}
	}

	$book_data['terms'] = $terms;

	// @todo meta

	$new_book_id = bdb_insert_book( $book_data );

	if ( ! $new_book_id || is_wp_error( $new_book_id ) ) {
		wp_die( __( 'An error occurred while inserting the book information.', 'book-database' ) );
	}

	$edit_url = add_query_arg( array(
		'bdb-message' => 'book-updated'
	), bdb_get_admin_page_edit_book( absint( $new_book_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/book/save', 'bdb_save_book' );

/**
 * Suggest Tags
 *
 * Used in tag autocomplete.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_suggest_tags() {
	$type = isset( $_REQUEST['type'] ) ? wp_strip_all_tags( $_REQUEST['type'] ) : false;

	if ( ! $type ) {
		exit;
	}

	$search = strtolower( wp_strip_all_tags( $_REQUEST['q'] ) );
	$args   = array(
		'name'   => $search,
		'fields' => 'names',
		'type'   => $type
	);
	$terms  = bdb_get_terms( $args );

	if ( $terms ) {
		foreach ( $terms as $term ) {
			echo $term . "\n";
		}
	}

	exit;
}

add_action( 'wp_ajax_bdb_suggest_tags', 'bdb_suggest_tags' );

/**
 * Suggest Series
 *
 * Used in series name autocomplete.
 *
 * @since 1.2.1
 * @return void
 */
function bdb_suggest_series() {
	$search = strtolower( wp_strip_all_tags( $_REQUEST['q'] ) );
	$series = book_database()->series->get_series( array(
		'name'   => $search,
		'fields' => 'names',
		'number' => - 1
	) );

	if ( $series ) {
		foreach ( $series as $name ) {
			echo $name . "\n";
		}
	}

	exit;
}

add_action( 'wp_ajax_bdb_suggest_series', 'bdb_suggest_series' );

/*
 * Below: Other Meta Boxes
 */

/**
 * Display Book Review Meta Table
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_review_meta_table( $book ) {
	if ( 0 == $book->ID ) {
		return;
	}

	$reviews = bdb_get_book_reviews( $book->ID );
	?>
    <div id="bdb-book-review-list" class="postbox">
        <h2><?php printf( __( '%s Reviews', 'book-database' ), bdb_get_label_singular() ); ?></h2>
        <div class="inside">
			<?php if ( $reviews ) : ?>
                <table class="wp-list-table widefat fixed posts">
                    <thead>
                    <tr>
                        <th><?php _e( 'ID', 'book-database' ); ?></th>
                        <th><?php _e( 'Date', 'book-database' ); ?></th>
                        <th><?php _e( 'Rating', 'book-database' ); ?></th>
                        <th><?php _e( 'Reviewer', 'book-database' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ( $reviews as $review ) :
						$obj = new BDB_Review( $review->ID );
						?>
                        <tr>
                            <td>
								<?php echo $review->ID; ?>
                                <a href="<?php echo esc_url( bdb_get_admin_page_edit_review( $review->ID ) ); ?>" target="_blank"><?php _e( '(Edit)', 'book-database' ); ?></a>
                            </td>
                            <td>
								<?php echo $obj->get_formatted_date(); ?>
                            </td>
                            <td>
								<?php
								if ( $review->rating ) {
									$rating = new BDB_Rating( $review->rating );
									echo $rating->format( 'text' );
								} else {
									echo '&ndash;';
								}
								?>
                            </td>
                            <td>
								<?php echo $obj->get_reviewer_name(); ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>

                <a href="<?php echo esc_url( bdb_get_admin_page_add_review( $book->ID ) ); ?>" class="button"><?php _e( 'Add Review', 'book-database' ); ?></a>
			<?php else : ?>
                <p><?php printf( __( 'No reviews for this book. Would you like to <a href="%s">add one</a>?', 'book-database' ), esc_url( bdb_get_admin_page_add_review( $book->ID ) ) ); ?></p>
			<?php endif; ?>
        </div>
    </div>
	<?php
}

add_action( 'book-database/book-edit/after-information-fields', 'bdb_book_review_meta_table' );

/**
 * Delete Book
 *
 * Processes deletions from the delete book URL.
 * @see   bdb_get_admin_page_delete_book()
 *
 * @since 1.0.0
 * @return void
 */
function bdb_delete_book_via_url() {
	if ( ! isset( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bdb_delete_book' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! isset( $_GET['ID'] ) ) {
		wp_die( __( 'Missing book ID.', 'book-database' ) );
	}

	$result = book_database()->books->delete( absint( $_GET['ID'] ) );

	$message = $result ? 'book-deleted' : 'book-delete-failed';
	$url     = add_query_arg( array(
		'bdb-message' => urlencode( $message )
	), bdb_get_admin_page_books() );

	wp_safe_redirect( $url );

	exit;
}

add_action( 'book-database/book/delete', 'bdb_delete_book_via_url' );