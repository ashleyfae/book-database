<?php
/**
 * Retailer Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Retailer
 * @package Book_Database
 */
class Retailer extends Base_Object {

	/**
	 * @var string Name of the retailer
	 */
	protected $name = '';

	/**
	 * @var string Template, for use in the book layout
	 */
	protected $template = '';

	/**
	 * Get the name of the retailer
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the template
	 *
	 * @return string
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Build a link
	 *
	 * This injects the provided URL into the template, replacing the `[url]` placeholder.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function build_link( $url ) {

		$template = $this->get_template();

		if ( empty( $template ) ) {
			$template = '<a href="[url]" target="_blank">' . esc_html( $this->get_name() ) . '</a>';
		}

		return str_replace( '[url]', esc_url( $url ), $template );

	}

}
