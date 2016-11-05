<?php
/**
 * Book Layout
 *
 * Primarily functions used in BDB_Book::get_formatted_info()
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		$alignment = $enabled_fields['cover']['alignment'];
		$class     = 'align' . sanitize_html_class( $alignment );
		$value     = '<img src="' . esc_url( $book->get_cover_url() ) . '" alt="' . esc_attr( wp_strip_all_tags( $book->get_title() ) ) . '" class="' . esc_attr( $class ) . '" itemprop="image">';
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
	$author = $book->get_author();

	if ( $author ) {
		$value = '<span itemprop="author">' . $book->get_author_names() . '</span>';
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
		$value = $book->get_formatted_series();
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
	$publishers = bdb_get_book_terms( $book_id, 'publisher' );

	if ( $publishers && is_array( $publishers ) ) {
		$pub_names = array();

		foreach ( $publishers as $pub ) {
			$pub_names[] = '<span itemprop="publisher" itemtype="http://schema.org/Organization" itemscope="">' . $pub->name . '</span>';
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
	$genres = bdb_get_book_terms( $book_id, 'publisher' );

	if ( $genres && is_array( $genres ) ) {
		$genre_names = array();

		foreach ( $genres as $genre ) {
			$genre_names[] = '<span itemprop="genre">' . $genre->name . '</span>';
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
	// @todo

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
	// @todo

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
function bdb_book_layout_goodreads( $value, $enabled_fields, $book_id, $book ) {
	// @todo

	return $value;
}

add_filter( 'book-database/book/formatted-info/value/goodreads', 'bdb_book_layout_goodreads', 10, 4 );

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
	// @todo

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
	return $book->get_synopsis();
}

add_filter( 'book-database/book/formatted-info/value/synopsis', 'bdb_book_layout_synopsis', 10, 4 );