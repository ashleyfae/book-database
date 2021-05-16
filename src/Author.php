<?php
/**
 * Author Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Author
 * @package Book_Database
 */
class Author extends Base_Object {

	protected $name = '';

	protected $slug = '';

	protected $description = '';

	protected $image_id = null;

	protected $links = array();

	protected $book_count = 0;

	/**
	 * Get the author's name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the author slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the author description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the ID of the image attachment
	 *
	 * @return int
	 */
	public function get_image_id() {
		return ! empty( $this->image_id ) ? absint( $this->image_id ) : 0;
	}

	/**
	 * Get the author image URL
	 *
	 * @param string $size Desired image size.
	 *
	 * @return string
	 */
	public function get_image_url( $size = 'full' ) {

		$url      = '';
		$image_id = $this->get_image_id();

		if ( ! empty( $image_id ) ) {
			$url = wp_get_attachment_image_url( $image_id, $size );
		}

		return apply_filters( 'book-database/author/get/image_url', $url, $image_id, $this );

	}

	/**
	 * Get the author image HTML.
	 *
	 * @param string|array $size Desired image size.
	 * @param array        $args Arguments to use in `wp_get_attachment_image()`.
	 *
	 * @return string
	 */
	public function get_image( $size = 'full', $args = array() ) {

		$image    = '';
		$image_id = $this->get_image_id();

		if ( $image_id ) {
			$image = wp_get_attachment_image( absint( $image_id ), $size, false, $args );
		}

		return apply_filters( 'book-database/author/get/image', $image, $image_id, $this );

	}

	/**
	 * Get an array of author links
	 *
	 * @return array()
	 */
	public function get_links() {
		return $this->links;
	}

	/**
	 * Get the number of books by this author
	 *
	 * @return int
	 */
	public function get_book_count() {
		return absint( $this->book_count );
	}

}
