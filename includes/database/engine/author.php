<?php
/**
 * Base Custom Database Table Author Query Class.
 *
 * Taken from \WP_Tax_Query
 * @see         \WP_Tax_Query
 *
 * @package     Database
 * @subpackage  Author
 * @copyright   Copyright (c) 2019
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace Book_Database\BerlinDB\Database\Queries;

use function Book_Database\book_database;
use function Book_Database\get_book_authors;

class Author extends Tax {

	/**
	 * Set tables used in queries
	 */
	protected function set_tables() {
		$this->relationships_table_name = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$this->terms_table_name         = book_database()->get_table( 'authors' )->get_table_name();
		$this->term_id_column_name      = 'author_id';
	}

	/**
	 * Validates a single query.
	 *
	 * @param array $query The single query. Passed by reference.
	 */
	protected function clean_query( &$query ) {
		$query['terms'] = array_unique( (array) $query['terms'] );

		$this->transform_query( $query, 'id' );
	}

	/**
	 * Transforms a single query, from one field to another.
	 *
	 * Operates on the `$query` object by reference. In the case of error,
	 * `$query` is converted to a WP_Error object.
	 *
	 * @global \wpdb $wpdb            The WordPress database abstraction object.
	 *
	 * @param array  $query           The single query. Passed by reference.
	 * @param string $resulting_field The resulting field. Accepts 'slug', 'name', or 'id'.
	 *                                Default 'id'.
	 */
	public function transform_query( &$query, $resulting_field ) {
		if ( empty( $query['terms'] ) ) {
			return;
		}

		if ( $query['field'] == $resulting_field ) {
			return;
		}

		$resulting_field = sanitize_key( $resulting_field );

		// Empty 'terms' always results in a null transformation.
		$terms = array_filter( $query['terms'] );
		if ( empty( $terms ) ) {
			$query['terms'] = array();
			$query['field'] = $resulting_field;

			return;
		}

		$args = array(
			'number'                 => 99999,
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
			'fields'                 => $resulting_field
		);

		// Term query parameter name depends on the 'field' being searched on.
		switch ( $query['field'] ) {
			case 'slug':
				if ( is_array( $terms ) ) {
					$args['slug__in'] = $terms;
				} else {
					$args['slug'] = $terms;
				}
				break;
			case 'name':
				if ( is_array( $terms ) ) {
					$args['name__in'] = $terms;
				} else {
					$args['name'] = $terms;
				}
				break;
			case 'search' :
				$args['search'] = is_array( $terms ) ? implode( ' ', $terms ) : $terms;
				break;
			default:
				$args['id__in'] = wp_parse_id_list( $terms );
				break;
		}

		$query['terms'] = get_book_authors( $args );
	}

}