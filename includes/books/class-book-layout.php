<?php
/**
 * Book Layout
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Layout
 * @package Book_Database
 */
class Book_Layout {

	/**
	 * Book object
	 *
	 * @var Book
	 */
	protected $book;

	/**
	 * Available book fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Enabled book fields
	 *
	 * @var array
	 */
	protected $enabled_fields = array();

	/**
	 * Rating object
	 *
	 * @var Rating|false
	 */
	protected $rating = false;

	/**
	 * Book_Layout constructor.
	 *
	 * @param $book
	 */
	public function __construct( $book ) {

		$this->book           = $book;
		$this->fields         = get_book_fields();
		$this->enabled_fields = get_enabled_book_fields();

	}

	/**
	 * Set the rating
	 *
	 * @param null|float|int $rating
	 */
	public function set_rating( $rating ) {
		$this->rating = new Rating( $rating );
	}

	/**
	 * Get the formatted book HTML
	 *
	 * @return string
	 */
	public function get_html() {

		$html = '';

		foreach ( $this->enabled_fields as $key => $field ) {

			// Make sure this is a valid field.
			if ( ! array_key_exists( $key, $this->fields ) ) {
				continue;
			}

			$template = $field['label'] ?? ''; // Value entered by the user as a templte.
			$find     = $this->fields[ $key ]['placeholder']; // Thing we need to look for and replace with a value.
			$value    = $this->get_field_value( $key );

			if ( empty( $value ) ) {
				continue;
			}

			// Add a line break if desired.
			if ( ! empty( $field['linebreak'] ) ) {
				$template .= '<br>';
			}

			// Replace the placeholder with the value.
			$final_value = str_replace( $find, $value, $template );

			// Add to the final output.
			$html .= $final_value;

		}

		// Ensure our tags are balanced.
		$html = balanceTags( $html, true );

		// Allow shortcodes.
		$html = do_shortcode( $html );

		// Wrap the entire HTML in a `<div>` with the book ID.
		$html = '<div id="book-' . esc_attr( $this->book->get_id() ) . '" class="bdb-book-info">' . $html . '</div>';

		/**
		 * Filters the final layout HTML.
		 *
		 * @param string      $html Book layout HTML.
		 * @param Book        $book Book object.
		 * @param Book_Layout $this Book layout object.
		 */
		return apply_filters( 'book-database/book/formatted-info/output', $html, $this->book, $this );

	}

	/**
	 * Get the value of a book field.
	 *
	 * @param string $field Book field key.
	 *
	 * @return string|mixed
	 */
	public function get_field_value( $field ) {

		$value = '';

		if ( method_exists( $this, 'get_field_' . $field ) ) {
			$value = call_user_func( array( $this, 'get_field_' . $field ) );
		} else {
			// Route all taxonomies not covered to `get_field_taxonomy`.
			$taxonomy = get_book_taxonomy_by( 'slug', $field );
			if ( $taxonomy instanceof Book_Taxonomy ) {
				$value = call_user_func( array( $this, 'get_field_taxonomy' ), $taxonomy );
			}
		}

		/**
		 * Filters the value for this field.
		 *
		 * @param mixed       $value Final value to be included in the layout.
		 * @param string      $field Field key.
		 * @param Book        $book  Book object.
		 * @param Book_Layout $this  Book layout object.
		 */
		return apply_filters( 'book-database/book/formatted-info/value/' . $field, $value, $field, $this->book, $this );

	}

	/**
	 * Field: cover
	 *
	 * @return string
	 */
	public function get_field_cover() {

		if ( ! $this->book->get_cover_id() ) {
			return '';
		}

		$alignment = $this->enabled_fields['cover']['alignment'] ?? $this->fields['cover']['alignment'];
		$size      = $this->enabled_fields['cover']['size'] ?? $this->fields['cover']['size'];

		// Validate the size.
		if ( ! array_key_exists( $size, get_book_cover_image_sizes() ) ) {
			$size = 'full';
		}

		$class = 'align' . sanitize_html_class( $alignment );
		$value = '<img src="' . esc_url( $this->book->get_cover_url( $size ) ) . '" alt="' . esc_attr( wp_strip_all_tags( $this->book->get_title() ) ) . '" class="' . esc_attr( $class ) . '" itemprop="image">';

		return $value;

	}

	/**
	 * Field: book title
	 *
	 * @return string
	 */
	public function get_field_title() {
		return '<span itemprop="name">' . $this->book->get_title() . '</span>';
	}

	/**
	 * Field: authors
	 *
	 * @return string
	 */
	public function get_field_author() {

		$authors = $this->book->get_authors();

		if ( empty( $authors ) ) {
			return '';
		}

		$names = array();

		foreach ( $authors as $author ) {
			$name    = '<span itemprop="author">' . esc_html( $author->get_name() ) . '</span>';
			$names[] = link_book_terms() ? '<a href="' . esc_url( get_book_term_link( $author ) ) . '">' . $name . '</a>' : $name;
		}

		return implode( ', ', $names );

	}

	/**
	 * Field: series
	 *
	 * @return string
	 */
	public function get_field_series() {

		if ( empty( $this->book->get_series_id() ) ) {
			return '';
		}

		$series = get_book_series_by( 'id', $this->book->get_series_id() );

		if ( ! $series instanceof Series ) {
			return '';
		}

		$series_name = sprintf( '%s #%s', $series->get_name(), $this->book->get_series_position() );

		return link_book_terms() ? '<a href="' . esc_url( get_book_term_link( $series ) ) . '">' . $series_name . '</a>' : $series_name;

	}

	/**
	 * Field: publisher
	 *
	 * @return string
	 */
	public function get_field_publisher() {

		$publishers = get_attached_book_terms( $this->book->get_id(), 'publisher' );

		if ( empty( $publishers ) ) {
			return '';
		}

		$pub_names = array();

		foreach ( $publishers as $publisher ) {
			$name        = '<span itemprop="publisher">' . esc_html( $publisher->get_name() ) . '</span>';
			$pub_names[] = link_book_terms() ? '<a href="' . esc_url( get_book_term_link( $publisher ) ) . '">' . $name . '</a>' : $name;
		}

		return implode( ', ', $pub_names );

	}

	/**
	 * Field: publication date
	 *
	 * @return string
	 */
	public function get_field_pub_date() {

		if ( empty( $this->book->get_pub_date() ) ) {
			return '';
		}

		return '<span itemprop="datePublished" content="' . esc_attr( $this->book->get_pub_date( true, 'Y-m-d' ) ) . '">' . esc_html( $this->book->get_pub_date( true ) ) . '</span>';

	}

	/**
	 * Field: genre
	 *
	 * @return string
	 */
	public function get_field_genre() {

		$genres = get_attached_book_terms( $this->book->get_id(), 'genre' );

		if ( empty( $genres ) ) {
			return '';
		}

		$genre_names = array();

		foreach ( $genres as $genre ) {
			$name          = '<span itemprop="genre">' . esc_html( $genre->get_name() ) . '</span>';
			$genre_names[] = link_book_terms() ? '<a href="' . esc_url( get_book_term_link( $genre ) ) . '">' . $name . '</a>' : $name;
		}

		return implode( ', ', $genre_names );

	}

	/**
	 * Field: pages
	 *
	 * @return string
	 */
	public function get_field_pages() {

		if ( empty( $this->book->get_pages() ) ) {
			return '';
		}

		return '<span itemprop="numberOfPages">' . esc_html( $this->book->get_pages() ) . '</span>';

	}

	/**
	 * Field: Goodreads URL
	 *
	 * @return string
	 */
	public function get_field_goodreads_url() {
		return $this->book->get_goodreads_url();
	}

	/**
	 * Field: buy link
	 *
	 * @return string
	 */
	public function get_field_buy_link() {

		$final_links = array();
		$links       = get_book_links( array(
			'book_id' => $this->book->get_id()
		) );

		if ( ! empty( $links ) ) {
			foreach ( $links as $link ) {
				$final_links[] = $link->format();
			}
		}

		/**
		 * Filters the buy link separator.
		 *
		 * @param string      $link_separator Separator string between links.
		 * @param Book_Link[] $links          Array of Book_Link objects.
		 * @param Book        $book           Book object.
		 * @param Book_Layout $this           Book layout object.
		 *
		 * @since 1.0
		 */
		$link_separator = apply_filters( 'book-database/book/formatted-info/buy-links/separator', ', ', $links, $this->book, $this );

		return implode( $link_separator, $final_links );

	}

	/**
	 * Field: rating
	 *
	 * @return string
	 */
	public function get_field_rating() {

		if ( ! $this->rating instanceof Rating || null === $this->rating->get_rating() ) {
			return '';
		}

		if ( ! is_numeric( $this->rating->get_rating() ) ) {
			$rating = $this->rating->get_rating();

			if ( 'dnf' === $rating ) {
				$rating = __( 'Did Not Finish', 'book-database' );
			}

			return $rating;
		}

		$rating_format = bdb_get_option( 'rating_display', 'html_stars' );
		$actual_value  = $this->rating->get_rating();
		$value         = '<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
		$value         .= '<span class="bdb-' . sanitize_html_class( str_replace( '_', '-', $rating_format ) ) . '-star-wrap">' . $this->rating->format( $rating_format ) . '</span>';
		$value         .= '<span class="bdb-actual-rating-values"><span itemprop="ratingValue">' . esc_html( $actual_value ) . '</span>/<span itemprop="bestRating">' . esc_html( $this->rating->get_max_rating() ) . '</span></span>';
		$value         .= '</span>';

		if ( link_book_terms() ) {
			$value = '<a href="' . esc_url( get_book_term_link( $this->rating ) ) . '">' . $value . '</a>';
		}

		return $value;

	}

	/**
	 * Field: synopsis
	 *
	 * @return string
	 */
	public function get_field_synopsis() {
		return wpautop( $this->book->get_synopsis() );
	}

	/**
	 * Field: taxonomies
	 *
	 * This covers the `source` taxonomy and all custom taxonomies.
	 *
	 * @param Book_Taxonomy $taxonomy
	 *
	 * @return string
	 */
	public function get_field_taxonomy( $taxonomy ) {

		$terms = get_attached_book_terms( $this->book->get_id(), $taxonomy->get_slug() );

		if ( empty( $terms ) ) {
			return '';
		}

		$term_names = array();

		foreach ( $terms as $term ) {
			$term_names[] = link_book_terms() ? '<a href="' . esc_url( get_book_term_link( $term ) ) . '">' . $term->get_name() . '</a>' : $term->get_name();
		}

		return implode( ', ', $term_names );

	}

}