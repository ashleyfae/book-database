<?php
/**
 * Books Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Books;

use Book_Database\BerlinDB;
use Book_Database\BerlinDB\Database\Queries\Tax;
use Book_Database\Models\Book;
use Book_Database\Database\Authors\AuthorsQuery;
use Book_Database\Database\Editions\EditionsQuery;
use Book_Database\Database\ReadingLogs\ReadingLogsQuery;
use Book_Database\Database\Reviews\ReviewsQuery;
use Book_Database\Database\Series\SeriesQuery;
use Book_Database\Where_Clause;
use function Book_Database\book_database;

/**
 * Class BooksQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BooksQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'books';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'book';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BooksSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'books';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Book::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'books';

    /**
     * Query constructor.
     *
     * @param  array  $args
     */
    public function __construct($args = array())
    {
        parent::__construct($args);
    }

    /**
     * Query for books
     *
     * @param  array  $args
     *
     * @return object[]|int
     */
    public function get_books($args = array())
    {

        $args = wp_parse_args($args, array(
            'author_query'      => array(),
            'book_query'        => array(),
            'series_query'      => array(),
            'reading_log_query' => array(),
            'review_query'      => array(),
            'edition_query'     => array(),
            'tax_query'         => array(),
            'unread'            => false,
            'orderby'           => 'book.id',
            'order'             => 'DESC',
            'include_rating'    => true,
            'include_review'    => false, // Whether or not to always include review data.
            'number'            => 20,
            'offset'            => 0,
            'count'             => false
        ));

        $select = $join = $where = array();

        $clause_engine = new Where_Clause();

        $tbl_books    = book_database()->get_table('books')->get_table_name();
        $tbl_author   = book_database()->get_table('authors')->get_table_name();
        $tbl_author_r = book_database()->get_table('book_author_relationships')->get_table_name();
        $tbl_ed       = book_database()->get_table('editions')->get_table_name();
        $tbl_log      = book_database()->get_table('reading_log')->get_table_name();
        $tbl_series   = book_database()->get_table('series')->get_table_name();
        $tbl_reviews  = book_database()->get_table('reviews')->get_table_name();

        // Select
        $select = array(
            'book.*',
            "GROUP_CONCAT( DISTINCT author.id SEPARATOR ',' ) as author_id",
            "GROUP_CONCAT( DISTINCT author.name SEPARATOR ',' ) as author_name",
            'series.id as series_id',
            'series.name as series_name'
        );

        // Author Join
        $join['author_query'] = "LEFT JOIN {$tbl_author_r} AS ar ON book.id = ar.book_id LEFT JOIN {$tbl_author} AS author ON ar.author_id = author.id";

        // Series Join
        $join['series_query'] = "LEFT JOIN {$tbl_series} AS series ON book.series_id = series.id";

        // Average Rating
        if (! empty($args['include_rating'])) {
            $join['average_rating_select'] = "LEFT JOIN {$tbl_log} AS avg_rating ON (book.id = avg_rating.book_id AND avg_rating.rating IS NOT NULL)";
            $select[]                      = 'ROUND( AVG( avg_rating.rating ), 2 ) as avg_rating';
        }

        /**
         * Where
         */

        // Author query
        if (! empty($args['author_query'])) {
            $clause_engine->set_table_query(new AuthorsQuery());
            $clause_engine->set_args($args['author_query']);
            $where = array_merge($where, $clause_engine->get_clauses());
        }

        // Book query
        if (! empty($args['book_query'])) {
            $clause_engine->set_table_query($this);
            $clause_engine->set_args($args['book_query']);
            $where = array_merge($where, $clause_engine->get_clauses());
        }

        // Edition query
        if (! empty($args['edition_query'])) {
            $join['edition_query'] = "INNER JOIN {$tbl_ed} AS ed ON (book.id = ed.book_id)";
            $clause_engine->set_table_query(new EditionsQuery());
            $clause_engine->set_args($args['edition_query']);
            $where = array_merge($where, $clause_engine->get_clauses());
        }

        // Reading log query
        if (! empty($args['reading_log_query'])) {
            $join['reading_log_query'] = "INNER JOIN {$tbl_log} AS log ON (book.id = log.book_id)";
            $clause_engine->set_table_query(new ReadingLogsQuery());
            $clause_engine->set_args($args['reading_log_query']);
            $where    = array_merge($where, $clause_engine->get_clauses());
            $select[] = 'log.id AS log_id, log.user_id AS log_user_id, log.date_started AS date_started, log.date_finished AS date_finished, log.percentage_complete AS percentage_complete, log.rating AS rating';
        }

        // Review query
        if (! empty($args['review_query'])) {
            $join['review_query'] = "INNER JOIN {$tbl_reviews} AS review ON (book.id = review.book_id)";
            $clause_engine->set_table_query(new ReviewsQuery());
            $clause_engine->set_args($args['review_query']);
            $where = array_merge($where, $clause_engine->get_clauses());
        } elseif (! empty($args['include_review'])) {
            // Include review data, but as a LEFT JOIN.
            $join['review_query'] = "LEFT JOIN {$tbl_reviews} AS review ON (book.id = review.book_id)";
        }

        // Unread books only
        // This is a bit "special" because we need a weird left join.
        if (! empty($args['unread'])) {
            $join['unread_query'] = "LEFT JOIN {$tbl_log} as ulog ON (book.id = ulog.book_id)";
            $where[]              = 'ulog.book_id IS NULL';
        }

        // Series query
        if (! empty($args['series_query'])) {
            $clause_engine->set_table_query(new SeriesQuery());
            $clause_engine->set_args($args['series_query']);
            $where = array_merge($where, $clause_engine->get_clauses());
        }

        // Tax query
        if (! empty($args['tax_query'])) {
            $tax_query          = new Tax($args['tax_query']);
            $clauses            = $tax_query->get_sql($this->table_alias, 'id');
            $join['tax_query']  = $clauses['join'];
            $where['tax_query'] = preg_replace('/^\s*AND\s*/', '', $clauses['where']);
        }

        // Select review data if we have a review query.
        if ((! empty($args['review_query']) || ! empty($args['include_review'])) && ! empty($join['review_query'])) {
            $select[] = 'review.id AS review_id, review.user_id AS review_user_id, review.post_id AS review_post_id, review.url AS review_url, review.date_written AS review_date_written, review.date_published AS review_date_published';
        }

        /**
         * Format and query
         */
        $select = implode(', ', $select);
        $join   = implode(' ', $join);
        $where  = ! empty($where) ? 'WHERE '.implode(' AND ', $where) : '';

        /**
         * Validate the orderby / order
         */
        $orderby = $this->validate_orderby($args['orderby'], $args);
        $order   = 'ASC' === strtoupper($args['order']) ? 'ASC' : 'DESC';

        $group_by = 'GROUP BY book.id';

        // Override select if we're counting.
        if (! empty($args['count'])) {
            $select   = 'COUNT( DISTINCT book.id )';
            $group_by = '';
        }

        if (! empty($args['count'])) {
            $query = "SELECT {$select} FROM {$tbl_books} AS book {$join} {$where}";

            $books = $this->get_db()->get_var($query);

            return absint($books);
        }

        $query = $this->get_db()->prepare("SELECT {$select} FROM {$tbl_books} AS book {$join} {$where} {$group_by} ORDER BY $orderby $order LIMIT %d,%d;",
            absint($args['offset']), absint($args['number']));

        $books = $this->get_db()->get_results($query);

        return wp_unslash($books);

    }

    /**
     * Validate the orderby
     *
     * @param  string  $orderby  Desired orderby.
     * @param  array  $args  Query arguments.
     *
     * @return string
     */
    protected function validate_orderby($orderby, $args = array())
    {

        $valid_orderbys = array(
            'author.id',
            'author.name',
            'author.slug',
            'book.id',
            'book.title',
            'book.index_title',
            'book.series_id',
            'book.series_position',
            'book.pub_date',
            'book.pages',
            'book.date_created',
            'book.date_modified',
            'series.id',
            'series.name',
            'series.slug',
            'series.number_books',
            'series.date_created',
        );
        if (! empty($args['include_rating'])) {
            $valid_orderbys = $valid_orderbys + array(
                    'avg_rating.id',
                    'avg_rating.review_id',
                    'avg_rating.user_id',
                    'avg_rating.date_started',
                    'avg_rating.date_finished',
                    'avg_rating.percentage_complete',
                    'avg_rating.rating'
                );
        }
        if (! empty($args['reading_log_query'])) {
            $valid_orderbys = $valid_orderbys + array(
                    'log.date_started',
                    'log.date_finished',
                    'log.percentage_complete'
                );
        }

        return in_array($orderby, $valid_orderbys) ? $orderby : 'book.id';

    }

}
