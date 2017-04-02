<?php
/**
 * Book Layout
 *
 * Primarily functions used in BDB_Book::get_formatted_info()
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Book Fields
 *
 * Returns an array of all the available book information fields,
 * their placeholder values, and their default labels.
 *
 * Other plugins can add their own fields using this filter:
 *  + book-database/book/available-fields
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_book_fields() {
	$fields = array(
		'cover'         => array(
			'name'        => __( 'Cover Image', 'book-database' ),
			'placeholder' => '[cover]',
			'label'       => '[cover]',
			'alignment'   => 'left', // left, center, right
			'size'        => 'full' // thumbnail, medium, large, full
		),
		'title'         => array(
			'name'        => __( 'Book Title', 'book-database' ),
			'placeholder' => '[title]',
			'label'       => '<strong>[title]</strong>',
		),
		'author'        => array(
			'name'        => __( 'Author', 'book-database' ),
			'placeholder' => '[author]',
			'label'       => sprintf( __( ' by %s', 'book-database' ), '[author]' ),
			'linebreak'   => 'on'
		),
		'series'        => array(
			'name'        => __( 'Series Name', 'book-database' ),
			'placeholder' => '[series]',
			'label'       => sprintf( __( '<strong>Series:</strong> %s', 'book-database' ), '[series]' ),
			'linebreak'   => 'on'
		),
		'pub_date'      => array(
			'name'        => __( 'Pub Date', 'book-database' ),
			'placeholder' => '[pub_date]',
			'label'       => sprintf( __( ' on %s', 'book-database' ), '[pub_date]' ),
			'linebreak'   => 'on'
		),
		'pages'         => array(
			'name'        => __( 'Pages', 'book-database' ),
			'placeholder' => '[pages]',
			'label'       => sprintf( __( '<strong>Pages:</strong> %s', 'book-database' ), '[pages]' ),
			'linebreak'   => 'on'
		),
		'goodreads_url' => array(
			'name'        => __( 'Goodreads', 'book-database' ),
			'placeholder' => '[goodreads]',
			'label'       => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', '[goodreads]', __( 'Goodreads', 'book-database' ) ),
			'linebreak'   => 'on'
		),
		'buy_link'      => array(
			'name'        => __( 'Purchase Link', 'book-database' ),
			'placeholder' => '[buy]',
			'label'       => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', '[buy]', __( 'Buy the Book', 'book-database' ) ),
			'linebreak'   => 'on'
		),
		'rating'        => array(
			'name'        => __( 'Rating', 'book-database' ),
			'placeholder' => '[rating]',
			'label'       => sprintf( __( '<strong>Rating:</strong> %s', 'book-database' ), '[rating]' ),
			'linebreak'   => 'on'
		),
		'synopsis'      => array(
			'name'        => __( 'Synopsis', 'book-database' ),
			'placeholder' => '[synopsis]',
			'label'       => '<blockquote>[synopsis]</blockquote>',
		),
	);

	return apply_filters( 'book-database/book/available-fields', $fields );
}

/**
 * Add Taxonomies to Book Layout Fields
 *
 * @param array $fields
 *
 * @since 1.2.1
 * @return array
 */
function bdb_book_layout_taxonomy_fields( $fields ) {
	$taxonomies = bdb_get_taxonomies();

	if ( is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $id => $tax ) {
			if ( array_key_exists( $id, $fields ) ) {
				continue;
			}

			$fields[ $id ] = array(
				'name'        => $tax['name'],
				'placeholder' => '[' . bdb_sanitize_key( $id ) . ']',
				'label'       => sprintf( '<strong>%1$s:</strong> [%2$s]', $tax['name'], $tax['id'] ),
				'linebreak'   => 'on'
			);
		}
	}

	return $fields;
}

add_filter( 'book-database/book/available-fields', 'bdb_book_layout_taxonomy_fields' );

/**
 * Book Cover Alignment Options
 *
 * Returns an array of book cover alignment options.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_book_alignment_options() {
	$options = array(
		'left'   => __( 'Left', 'book-database' ),
		'center' => __( 'Centered', 'book-database' ),
		'right'  => __( 'Right', 'book-database' )
	);

	return apply_filters( 'book-database/book/cover-alignment-options', $options );
}

/**
 * Default Book Layout Keys
 *
 * Meta enabled by default for the book layout.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_default_book_layout_keys() {
	$default_keys = array(
		'cover',
		'title',
		'author',
		'series',
		'pub_date',
		'pages',
		'goodreads_url',
		'buy_link',
		'rating',
		'synopsis'
	);

	return apply_filters( 'book-database/settings/default-layout-keys', $default_keys );
}

/**
 * Get Default Book Field Values
 *
 * Returns the array of default fields. These are the ones used if no settings have
 * been changed. They're loaded on initial install or when the 'Book Layout' tab
 * is reset to the default.
 *
 * @uses  bdb_get_default_book_layout_keys()
 *
 * @param array|null $all_fields
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_default_book_field_values( $all_fields = null ) {
	if ( ! is_array( $all_fields ) ) {
		$all_fields = bdb_get_book_fields();
	}
	$default_keys   = bdb_get_default_book_layout_keys();
	$default_values = array();

	if ( ! is_array( $default_keys ) ) {
		return array();
	}

	foreach ( $default_keys as $key ) {
		if ( ! array_key_exists( $key, $all_fields ) ) {
			continue;
		}

		$key_value = $all_fields[ $key ];

		if ( array_key_exists( 'placeholder', $key_value ) ) {
			unset( $key_value['placeholder'] );
		}

		$default_values[ $key ] = $key_value;
	}

	return $default_values;
}

/**
 * Value: Cover
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_cover( $value, $enabled_fields, $book_id, $book ) {
	if ( $book->get_cover_id() ) {
		$default_fields = bdb_get_book_fields();
		$alignment      = isset( $enabled_fields['cover']['alignment'] ) ? $enabled_fields['cover']['alignment'] : $default_fields['cover']['alignment'];
		$size           = isset( $enabled_fields['cover']['size'] ) ? $enabled_fields['cover']['size'] : $default_fields['cover']['size'];

		// Sanitize size.
		if ( ! array_key_exists( $size, bdb_get_image_sizes() ) ) {
			$size = 'full';
		}

		$class = 'align' . sanitize_html_class( $alignment );
		$value = '<img src="' . esc_url( $book->get_cover_url( $size ) ) . '" alt="' . esc_attr( wp_strip_all_tags( $book->get_title() ) ) . '" class="' . esc_attr( $class ) . '" itemprop="image">';
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/cover', 'bdb_book_layout_cover', 10, 4 );

/**
 * Value: Title
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_title( $value, $enabled_fields, $book_id, $book ) {
	return '<span itemprop="name">' . $book->get_title() . '</span>';
}

add_filter( 'book-database/book/formatted-info/value/title', 'bdb_book_layout_title', 10, 4 );

/**
 * Value: Author
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_author( $value, $enabled_fields, $book_id, $book ) {
	$authors = $book->get_term_type( 'author' );

	if ( $authors ) {
		$names = array();

		foreach ( $authors as $obj ) {
			$name    = '<span itemprop="author">' . esc_html( $obj->name ) . '</span>';
			$names[] = bdb_link_terms() ? '<a href="' . esc_url( bdb_get_term_link( $obj ) ) . '">' . $name . '</a>' : $name;
		}

		$name = implode( ', ', $names );

		$value = $name;
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/author', 'bdb_book_layout_author', 10, 4 );

/**
 * Value: Series
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_series( $value, $enabled_fields, $book_id, $book ) {
	$series = $book->get_series_id();

	if ( $series ) {
		$value = $book->get_formatted_series( bdb_link_terms() );
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/series', 'bdb_book_layout_series', 10, 4 );

/**
 * Value: Publisher
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_publisher( $value, $enabled_fields, $book_id, $book ) {
	$publishers = $book->get_term_type( 'publisher' );

	if ( $publishers && is_array( $publishers ) ) {
		$pub_names = array();

		foreach ( $publishers as $pub ) {
			$name        = '<span itemprop="publisher">' . $pub->name . '</span>';
			$pub_names[] = bdb_link_terms() ? '<a href="' . esc_url( bdb_get_term_link( $pub ) ) . '">' . $name . '</a>' : $name;
		}

		$value = implode( ', ', $pub_names );
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/publisher', 'bdb_book_layout_publisher', 10, 4 );

/**
 * Value: Pub Date
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_pub_date( $value, $enabled_fields, $book_id, $book ) {
	$pub_date = $book->get_formatted_pub_date();

	if ( $pub_date ) {
		$value = '<span itemprop="datePublished" content="' . esc_attr( $book->get_formatted_pub_date( 'Y-m-d' ) ) . '">' . $pub_date . '</span>';
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/pub_date', 'bdb_book_layout_pub_date', 10, 4 );

/**
 * Value: Genre
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_genre( $value, $enabled_fields, $book_id, $book ) {
	$genres = $book->get_term_type( 'genre' );

	if ( $genres && is_array( $genres ) ) {
		$genre_names = array();

		foreach ( $genres as $genre ) {
			$name          = '<span itemprop="genre">' . $genre->name . '</span>';
			$genre_names[] = bdb_link_terms() ? '<a href="' . esc_url( bdb_get_term_link( $genre ) ) . '">' . $name . '</a>' : $name;
		}

		$value = implode( ', ', $genre_names );
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/genre', 'bdb_book_layout_genre', 10, 4 );

/**
 * Value: Pages
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_pages( $value, $enabled_fields, $book_id, $book ) {
	$pages = $book->get_pages();

	if ( $pages ) {
		$value = '<span itemprop="numberOfPages">' . $pages . '</span>';
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/pages', 'bdb_book_layout_pages', 10, 4 );

/**
 * Value: Source
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_source( $value, $enabled_fields, $book_id, $book ) {
	$sources = $book->get_term_type( 'source' );

	if ( $sources && is_array( $sources ) ) {
		$source_names = array();

		foreach ( $sources as $source ) {
			$source_names[] = bdb_link_terms() ? '<a href="' . esc_url( bdb_get_term_link( $source ) ) . '">' . $source->name . '</a>' : $source->name;
		}

		$value = implode( ', ', $source_names );
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/source', 'bdb_book_layout_source', 10, 4 );

/**
 * Value: Goodreads
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_goodreads_url( $value, $enabled_fields, $book_id, $book ) {
	return $book->get_goodreads_url();
}

add_filter( 'book-database/book/formatted-info/value/goodreads_url', 'bdb_book_layout_goodreads_url', 10, 4 );

/**
 * Value: Buy Link
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_buy_link( $value, $enabled_fields, $book_id, $book ) {
	return $book->get_buy_link();
}

add_filter( 'book-database/book/formatted-info/value/buy_link', 'bdb_book_layout_buy_link', 10, 4 );

/**
 * Value: Rating
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_rating( $value, $enabled_fields, $book_id, $book ) {
	if ( null !== $book->get_rating() ) {
		$rating       = new BDB_Rating( $book->get_rating() );
		$fa_stars     = $rating->format( 'font_awesome' );
		$actual_value = is_numeric( $book->get_rating() ) ? $book->get_rating() : 0;

		$value = '<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
		$value .= '<span class="bookdb-font-awesome-star-wrap">' . $fa_stars . '</span>';
		$value .= '<span class="bookdb-actual-rating-values"><span itemprop="ratingValue">' . esc_html( $actual_value ) . '</span>/<span itemprop="bestRating">' . esc_html( $rating->max ) . '</span></span>';
		$value .= '</span>';

		if ( bdb_link_terms() ) {
			$value = '<a href="' . esc_url( bdb_get_term_link( $book->get_rating(), 'rating' ) ) . '">' . $value . '</a>';
		}
	}

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/rating', 'bdb_book_layout_rating', 10, 4 );

/**
 * Value: Synopsis
 *
 * @param mixed    $value
 * @param array    $enabled_fields
 * @param int      $book_id
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_synopsis( $value, $enabled_fields, $book_id, $book ) {
	return wpautop( $book->get_synopsis() );
}

add_filter( 'book-database/book/formatted-info/value/synopsis', 'bdb_book_layout_synopsis', 10, 4 );

/**
 * Book Layout Wrapper
 *
 * Wraps the entire HTML in a `<div>` with the book ID.
 *
 * @param string   $html Formatted book info.
 * @param BDB_Book $book Book object.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_layout_wrapper( $html, $book ) {
	if ( empty( $html ) ) {
		return $html;
	}

	return '<div id="book-' . esc_attr( $book->ID ) . '" class="bookdb-book-info">' . $html . '</div>';
}

add_filter( 'book-database/book/formatted-info/output', 'bdb_book_layout_wrapper', 10, 2 );