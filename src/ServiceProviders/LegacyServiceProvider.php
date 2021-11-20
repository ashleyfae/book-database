<?php
/**
 * LegacyServiceProvider.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\ServiceProviders;

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
use Book_Database\HTML;
use Book_Database\Plugin;
use Book_Database\REST_API;
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

        // Database - reading_log
        require_once BDB_DIR.'includes/database/reading-logs/class-reading-logs-table.php';
        require_once BDB_DIR.'includes/database/reading-logs/class-reading-logs-schema.php';
        require_once BDB_DIR.'includes/database/reading-logs/class-reading-logs-query.php';

        // Database - retailers
        require_once BDB_DIR.'includes/database/retailers/class-retailers-table.php';
        require_once BDB_DIR.'includes/database/retailers/class-retailers-schema.php';
        require_once BDB_DIR.'includes/database/retailers/class-retailers-query.php';

        // Database - reviews
        require_once BDB_DIR.'includes/database/reviews/class-reviews-table.php';
        require_once BDB_DIR.'includes/database/reviews/class-reviews-schema.php';
        require_once BDB_DIR.'includes/database/reviews/class-reviews-query.php';
        require_once BDB_DIR.'includes/database/reviews/class-review-meta-table.php';

        // Database - series
        require_once BDB_DIR.'includes/database/series/class-series-table.php';
        require_once BDB_DIR.'includes/database/series/class-series-schema.php';
        require_once BDB_DIR.'includes/database/series/class-series-query.php';

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
        require_once BDB_DIR.'includes/shortcodes.php';
        require_once BDB_DIR.'includes/template-functions.php';
    }

    private function includeAdminFiles(): void
    {
        require_once BDB_DIR.'includes/admin/abstract-class-list-table.php';
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
    }

    private function bindClasses(): void
    {
        book_database()->bind(REST_API::class);
        book_database()->alias(REST_API::class, 'rest_api');

        book_database()->bind(HTML::class);
        book_database()->alias(HTML::class, 'html');
    }
}
