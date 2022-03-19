<?php
/**
 * LegacyServiceProvider.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\ServiceProviders;

use Book_Database\Analytics\Analytics;
use Book_Database\Database\Authors\AuthorsQuery;
use Book_Database\Database\Authors\AuthorsSchema;
use Book_Database\Database\Authors\AuthorsTable;
use Book_Database\Database\BookAuthor\BookAuthorQuery;
use Book_Database\Database\BookAuthor\BookAuthorSchema;
use Book_Database\Database\BookAuthor\BookAuthorTable;
use Book_Database\Database\BookLinks\BookLinksQuery;
use Book_Database\Database\BookLinks\BookLinksSchema;
use Book_Database\Database\BookLinks\BookLinksTable;
use Book_Database\Database\Books\BookMetaTable;
use Book_Database\Database\Books\BooksQuery;
use Book_Database\Database\Books\BooksSchema;
use Book_Database\Database\Books\BooksTable;
use Book_Database\Database\BookTaxonomies\BookTaxonomiesQuery;
use Book_Database\Database\BookTaxonomies\BookTaxonomiesSchema;
use Book_Database\Database\BookTaxonomies\BookTaxonomiesTable;
use Book_Database\Database\BookTerm\BookTermRelationshipQuery;
use Book_Database\Database\BookTerm\BookTermRelationshipsSchema;
use Book_Database\Database\BookTerm\BookTermRelationshipsTable;
use Book_Database\Database\BookTerms\BookTermsQuery;
use Book_Database\Database\BookTerms\BookTermsSchema;
use Book_Database\Database\BookTerms\BookTermsTable;
use Book_Database\Database\Editions\EditionsQuery;
use Book_Database\Database\Editions\EditionsSchema;
use Book_Database\Database\Editions\EditionsTable;
use Book_Database\Database\ReadingLogs\ReadingLogsQuery;
use Book_Database\Database\ReadingLogs\ReadingLogsSchema;
use Book_Database\Database\ReadingLogs\ReadingLogsTable;
use Book_Database\Database\Retailers\RetailersQuery;
use Book_Database\Database\Retailers\RetailersSchema;
use Book_Database\Database\Retailers\RetailersTable;
use Book_Database\Database\Reviews\ReviewMetaTable;
use Book_Database\Database\Reviews\ReviewsQuery;
use Book_Database\Database\Reviews\ReviewsSchema;
use Book_Database\Database\Reviews\ReviewsTable;
use Book_Database\Database\Series\SeriesQuery;
use Book_Database\Database\Series\SeriesSchema;
use Book_Database\Database\Series\SeriesTable;
use Book_Database\Exceptions\Exception;
use Book_Database\Helpers\HTML;
use Book_Database\Models\Author;
use Book_Database\Models\Book;
use Book_Database\Models\BookAuthorRelationship;
use Book_Database\Models\BookLink;
use Book_Database\Models\BookTaxonomy;
use Book_Database\Models\BookTerm;
use Book_Database\Models\BookTermRelationship;
use Book_Database\Models\Edition;
use Book_Database\Models\Model;
use Book_Database\Models\ReadingLog;
use Book_Database\Models\Retailer;
use Book_Database\Models\Review;
use Book_Database\Models\Series;
use Book_Database\Plugin;
use Book_Database\REST_API\RouteRegistration;
use Book_Database\ValueObjects\Rating;
use function Book_Database\book_database;

class LegacyServiceProvider implements ServiceProvider
{

    public function register(): void
    {
        $this->includeLegacyFiles();

        if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
            $this->includeAdminFiles();
        } else {
            $this->includeFrontendFiles();
        }

        $this->registerClassAliases();
        $this->bindClasses();
    }

    public function boot(): void
    {

    }

    private function includeLegacyFiles(): void
    {
        require_once BDB_DIR.'includes/deprecated/deprecated-functions.php';

        // Database engine
        require_once BDB_DIR.'includes/database/engine/base.php';
        require_once BDB_DIR.'includes/database/engine/table.php';
        require_once BDB_DIR.'includes/database/engine/query.php';
        require_once BDB_DIR.'includes/database/engine/column.php';
        require_once BDB_DIR.'includes/database/engine/row.php';
        require_once BDB_DIR.'includes/database/engine/schema.php';
        require_once BDB_DIR.'includes/database/engine/compare.php';
        require_once BDB_DIR.'includes/database/engine/date.php';
        require_once BDB_DIR.'includes/database/engine/series.php';
        require_once BDB_DIR.'includes/database/engine/tax.php';
        require_once BDB_DIR.'includes/database/engine/author.php';
        require_once BDB_DIR.'includes/database/engine/join.php';
        require_once BDB_DIR.'includes/database/engine/book.php';
        require_once BDB_DIR.'includes/database/engine/edition.php';
        require_once BDB_DIR.'includes/database/engine/reading-log.php';
        require_once BDB_DIR.'includes/database/engine/class-where-clause.php';
        require_once BDB_DIR.'includes/database/sanitization.php';

        // Analytics
        require_once BDB_DIR.'includes/analytics/analytics-functions.php';

        // Authors
        require_once BDB_DIR.'includes/authors/author-functions.php';

        // Blocks
        require_once BDB_DIR.'includes/blocks.php';

        // Book Author Relationships
        require_once BDB_DIR.'includes/book-author-relationships/book-author-relationship-actions.php';
        require_once BDB_DIR.'includes/book-author-relationships/book-author-relationship-functions.php';

        // Books
        require_once BDB_DIR.'includes/books/book-functions.php';
        require_once BDB_DIR.'includes/books/book-layout-functions.php';
        require_once BDB_DIR.'includes/books/book-meta.php';

        // Book Links
        require_once BDB_DIR.'includes/book-links/book-link-functions.php';

        // Book Taxonomies
        require_once BDB_DIR.'includes/book-taxonomies/book-taxonomy-functions.php';

        // Book Term Relationships
        require_once BDB_DIR.'includes/book-term-relationships/book-term-relationship-actions.php';
        require_once BDB_DIR.'includes/book-term-relationships/book-term-relationship-functions.php';

        // Book Terms
        require_once BDB_DIR.'includes/book-terms/book-term-functions.php';

        // Editions
        require_once BDB_DIR.'includes/editions/edition-functions.php';

        // Ratings
        require_once BDB_DIR.'includes/ratings/rating-functions.php';

        // Reading Logs
        require_once BDB_DIR.'includes/reading-logs/reading-log-functions.php';

        // Retailers
        require_once BDB_DIR.'includes/retailers/retailer-functions.php';

        // Reviews
        require_once BDB_DIR.'includes/reviews/review-actions.php';
        require_once BDB_DIR.'includes/reviews/review-functions.php';
        require_once BDB_DIR.'includes/reviews/review-meta.php';

        // Series
        require_once BDB_DIR.'includes/series/series-functions.php';

        // Misc.
        require_once BDB_DIR.'includes/capabilities.php';
        require_once BDB_DIR.'includes/misc-functions.php';
        require_once BDB_DIR.'includes/rewrites.php';
        require_once BDB_DIR.'includes/template-functions.php';
    }

    private function includeAdminFiles(): void
    {
        require_once BDB_DIR.'includes/admin/admin-assets.php';
        require_once BDB_DIR.'includes/admin/admin-bar.php';
        require_once BDB_DIR.'includes/admin/admin-notices.php';
        require_once BDB_DIR.'includes/admin/admin-pages.php';

        // Analytics
        require_once BDB_DIR.'includes/admin/analytics/analytics-page.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/overview.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/library.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/reading.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/ratings.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/editions.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/reviews.php';
        require_once BDB_DIR.'includes/admin/analytics/tabs/terms.php';

        // Authors
        require_once BDB_DIR.'includes/admin/authors/author-actions.php';
        require_once BDB_DIR.'includes/admin/authors/author-functions.php';
        require_once BDB_DIR.'includes/admin/authors/authors-page.php';

        // Book Terms
        require_once BDB_DIR.'includes/admin/book-terms/book-term-actions.php';
        require_once BDB_DIR.'includes/admin/book-terms/book-term-functions.php';
        require_once BDB_DIR.'includes/admin/book-terms/book-terms-page.php';

        // Books
        require_once BDB_DIR.'includes/admin/books/book-actions.php';
        require_once BDB_DIR.'includes/admin/books/book-functions.php';
        require_once BDB_DIR.'includes/admin/books/books-page.php';
        require_once BDB_DIR.'includes/admin/books/edit-book-fields.php';

        // Dashboard
        require_once BDB_DIR.'includes/admin/dashboard/widgets.php';

        // Editions
        require_once BDB_DIR.'includes/admin/editions/edition-actions.php';

        // Licensing
        require_once BDB_DIR.'includes/class-edd-sl-plugin-updater.php';
        require_once BDB_DIR.'includes/admin/licensing/class-license-key.php';
        require_once BDB_DIR.'includes/admin/licensing/license-actions.php';

        // Posts
        require_once BDB_DIR.'includes/admin/posts/post-actions.php';

        // Reading Logs
        require_once BDB_DIR.'includes/admin/reading-logs/reading-log-actions.php';

        // Reviews
        require_once BDB_DIR.'includes/admin/reviews/review-actions.php';
        require_once BDB_DIR.'includes/admin/reviews/review-fields.php';
        require_once BDB_DIR.'includes/admin/reviews/review-functions.php';
        require_once BDB_DIR.'includes/admin/reviews/reviews-page.php';

        // Series
        require_once BDB_DIR.'includes/admin/series/series-actions.php';
        require_once BDB_DIR.'includes/admin/series/series-functions.php';
        require_once BDB_DIR.'includes/admin/series/series-page.php';

        // Settings
        require_once BDB_DIR.'includes/admin/settings/book-layout-functions.php';
        require_once BDB_DIR.'includes/admin/settings/register-settings.php';
        require_once BDB_DIR.'includes/admin/settings/display-settings.php';
    }

    private function includeFrontendFiles(): void
    {
        require_once BDB_DIR.'includes/assets.php';
    }

    /**
     * These classes have been renamed.
     */
    private function registerClassAliases(): void
    {
        class_alias(Plugin::class, 'Book_Database\\Book_Database');

        // Databases
        class_alias(AuthorsTable::class, 'Book_Database\\Authors_Table');
        class_alias(AuthorsQuery::class, 'Book_Database\\Authors_Query');
        class_alias(AuthorsSchema::class, 'Book_Database\\Authors_Schema');

        class_alias(BookAuthorQuery::class, 'Book_Database\\Book_Author_Relationships_Query');
        class_alias(BookAuthorSchema::class, 'Book_Database\\Book_Author_Relationships_Schema');
        class_alias(BookAuthorTable::class, 'Book_Database\\Book_Author_Relationships_Table');

        class_alias(BookLinksQuery::class, 'Book_Database\\Book_Links_Query');
        class_alias(BookLinksSchema::class, 'Book_Database\\Book_Links_Schema');
        class_alias(BookLinksTable::class, 'Book_Database\\Book_Links_Table');

        class_alias(BookTaxonomiesQuery::class, 'Book_Database\\Book_Taxonomies_Query');
        class_alias(BookTaxonomiesSchema::class, 'Book_Database\\Book_Taxonomies_Schema');
        class_alias(BookTaxonomiesTable::class, 'Book_Database\\Book_Taxonomies_Table');

        class_alias(BookTermRelationshipQuery::class, 'Book_Database\\Book_Term_Relationships_Query');
        class_alias(BookTermRelationshipsSchema::class, 'Book_Database\\Book_Term_Relationships_Schema');
        class_alias(BookTermRelationshipsTable::class, 'Book_Database\\Book_Term_Relationships_Table');

        class_alias(BookTermsQuery::class, 'Book_Database\\Book_Terms_Query');
        class_alias(BookTermsSchema::class, 'Book_Database\\Book_Terms_Schema');
        class_alias(BookTermsTable::class, 'Book_Database\\Book_Terms_Table');

        class_alias(BookMetaTable::class, 'Book_Database\\Book_Meta_Table');
        class_alias(BooksQuery::class, 'Book_Database\\Books_Query');
        class_alias(BooksSchema::class, 'Book_Database\\Books_Schema');
        class_alias(BooksTable::class, 'Book_Database\\Books_Table');

        class_alias(EditionsQuery::class, 'Book_Database\Editions_Query');
        class_alias(EditionsSchema::class, 'Book_Database\\Editions_Schema');
        class_alias(EditionsTable::class, 'Book_Database\\Editions_Table');

        class_alias(ReadingLogsQuery::class, 'Book_Database\\Reading_Logs_Query');
        class_alias(ReadingLogsSchema::class, 'Book_Database\\Reading_Logs_Schema');
        class_alias(ReadingLogsTable::class, 'Book_Database\\Reading_Logs_Table');

        class_alias(RetailersQuery::class, 'Book_Database\\Retailers_Query');
        class_alias(RetailersSchema::class, 'Book_Database\\Retailers_Schema');
        class_alias(RetailersTable::class, 'Book_Database\\Retailers_Table');

        class_alias(ReviewMetaTable::class, 'Book_Database\\Review_Meta_Table');
        class_alias(ReviewsQuery::class, 'Book_Database\\Reviews_Query');
        class_alias(ReviewsSchema::class, 'Book_Database\\Reviews_Schema');
        class_alias(ReviewsTable::class, 'Book_Database\\Reviews_Table');

        class_alias(SeriesQuery::class, 'Book_Database\\Series_Query');
        class_alias(SeriesSchema::class, 'Book_Database\\Series_Schema');
        class_alias(SeriesTable::class, 'Book_Database\\Series_Table');

        // Models
        class_alias(Model::class, 'Book_Database\\Base_Object');
        class_alias(Author::class, 'Book_Database\\Author');
        class_alias(Book::class, 'Book_Database\\Book');
        class_alias(BookAuthorRelationship::class, 'Book_Database\\Book_Author_Relationship');
        class_alias(BookLink::class, 'Book_Database\\Book_Link');
        class_alias(BookTaxonomy::class, 'Book_Database\\Book_Taxonomy');
        class_alias(BookTerm::class, 'Book_Database\\Book_Term');
        class_alias(BookTermRelationship::class, 'Book_Database\\Book_Term_Relationship');
        class_alias(ReadingLog::class, 'Book_Database\\Reading_Log');
        class_alias(Edition::class, 'Book_Database\\Edition');
        class_alias(Retailer::class, 'Book_Database\\Retailer');
        class_alias(Review::class, 'Book_Database\\Review');
        class_alias(Series::class, 'Book_Database\\Series');

        // Misc.
        class_alias(Rating::class, 'Book_Database\\Rating');
        class_alias(Exception::class, 'Book_Database\\Exception');
        class_alias(HTML::class, 'Book_Database\\HTML');
        class_alias(RouteRegistration::class, 'Book_Database\\REST_API');

        // Analytics
        class_alias(Analytics::class, 'Book_Database\\Analytics');
        class_alias( \Book_Database\Analytics\Datasets\Average_Days_Acquired_to_Read::class, '\Book_Database\Analytics\Average_Days_Acquired_to_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Average_Days_Finish_Book::class, '\Book_Database\Analytics\Average_Days_Finish_Book' );
        class_alias( \Book_Database\Analytics\Datasets\Average_Rating::class, '\Book_Database\Analytics\Average_Rating' );
        class_alias( \Book_Database\Analytics\Datasets\Books_Per_Year::class, '\Book_Database\Analytics\Books_Per_Year' );
        class_alias( \Book_Database\Analytics\Datasets\Books_Read_by_Publication_Year::class, '\Book_Database\Analytics\Books_Read_by_Publication_Year' );
        class_alias( \Book_Database\Analytics\Datasets\Books_Read_Over_Time::class, '\Book_Database\Analytics\Books_Read_Over_Time' );
        class_alias( \Book_Database\Analytics\Datasets\Edition_Format_Breakdown::class, '\Book_Database\Analytics\Edition_Format_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Edition_Genre_Breakdown::class, '\Book_Database\Analytics\Edition_Genre_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Edition_Source_Breakdown::class, '\Book_Database\Analytics\Edition_Source_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Editions_Over_Time::class, '\Book_Database\Analytics\Editions_Over_Time' );
        class_alias( \Book_Database\Analytics\Datasets\Format_Breakdown::class, '\Book_Database\Analytics\Format_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Highest_Rated_Books::class, '\Book_Database\Analytics\Highest_Rated_Books' );
        class_alias( \Book_Database\Analytics\Datasets\Library_Book_Releases::class, '\Book_Database\Analytics\Library_Book_Releases' );
        class_alias( \Book_Database\Analytics\Datasets\Library_Genre_Breakdown::class, '\Book_Database\Analytics\Library_Genre_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Longest_Book_Read::class, '\Book_Database\Analytics\Longest_Book_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Lowest_Rated_Books::class, '\Book_Database\Analytics\Lowest_Rated_Books' );
        class_alias( \Book_Database\Analytics\Datasets\Most_Read_Genres::class, '\Book_Database\Analytics\Most_Read_Genres' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Books_Added::class, '\Book_Database\Analytics\Number_Books_Added' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Different_Authors_Read::class, '\Book_Database\Analytics\Number_Different_Authors_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Different_Series_Read::class, '\Book_Database\Analytics\Number_Different_Series_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Distinct_Authors_Added::class, '\Book_Database\Analytics\Number_Distinct_Authors_Added' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Editions::class, '\Book_Database\Analytics\Number_Editions' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Reviews_Written::class, '\Book_Database\Analytics\Number_Reviews_Written' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Series_Books_Added::class, '\Book_Database\Analytics\Number_Series_Books_Added' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Signed_Editions::class, '\Book_Database\Analytics\Number_Signed_Editions' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Standalones_Added::class, '\Book_Database\Analytics\Number_Standalones_Added' );
        class_alias( \Book_Database\Analytics\Datasets\Number_Standalones_Read::class, '\Book_Database\Analytics\Number_Standalones_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Pages_Breakdown::class, '\Book_Database\Analytics\Pages_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Pages_Read::class, '\Book_Database\Analytics\Pages_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Ratings_Breakdown::class, '\Book_Database\Analytics\Ratings_Breakdown' );
        class_alias( \Book_Database\Analytics\Datasets\Reading_Overview::class, '\Book_Database\Analytics\Reading_Overview' );
        class_alias( \Book_Database\Analytics\Datasets\Reading_Track::class, '\Book_Database\Analytics\Reading_Track' );
        class_alias( \Book_Database\Analytics\Datasets\Reviews_Over_Time::class, '\Book_Database\Analytics\Reviews_Over_Time' );
        class_alias( \Book_Database\Analytics\Datasets\Reviews_Written::class, '\Book_Database\Analytics\Reviews_Written' );
        class_alias( \Book_Database\Analytics\Datasets\Shortest_Book_Read::class, '\Book_Database\Analytics\Shortest_Book_Read' );
        class_alias( \Book_Database\Analytics\Datasets\Terms_Breakdown::class, '\Book_Database\Analytics\Terms_Breakdown' );

        // Admin
        class_alias(\Book_Database\Admin\Utils\ListTable::class, '\Book_Database\List_Table');
    }

    private function bindClasses(): void
    {
        book_database()->bind(RouteRegistration::class);
        book_database()->alias(RouteRegistration::class, 'rest_api');

        book_database()->bind(HTML::class);
        book_database()->alias(HTML::class, 'html');
    }
}
