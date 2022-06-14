=== Book Database ===
Author URI: https://www.nosegraze.com
Plugin URI: https://shop.nosegraze.com/product/book-database/
Requires at least: 4.4
Tested up to: 5.9.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Maintain a database of books and reviews.

== Description ==

Maintain a database of books and reviews.

== Installation ==

1. Go to Plugins > Add New > Upload Plugin and upload the book-database.zip file.
2. Activate the plugin.

( more TK )

== Changelog ==

= 1.3.1 - 14 June, 2022 =

* Fix: Widgets not being loaded.

= 1.3 - 18 April, 2022 =

* Requirements: Minimum PHP version is now 7.1.
* New Anonymous server data is now collected for analytics (PHP version, WP version, etc.).
* Refactor: Update deprecated `block_categories` filter usage.
* Dev: Set up Composer / autoloading.
* Dev: Add Alpine.js.

= 1.2.2 - 13 February, 2022 =

* Fix: Admin "Unowned Books" filter not working.
* Fix: When adding a new book, the publication date was pre-populated with "January 1, 1970"

= 1.2.1 - 11 December, 2021 =

* New: Added taxonomy support to the `[book-grid]` shortcode.
* Fix: Book publication dates not saving correctly with certain date formats.
* Fix: Fatal error on dashboard widget if you have a reading log associated with a deleted book.

= 1.2 - 1 May, 2021 =

* New: Added a Gutenberg block for displaying book information.
* Fix: When associating a review with a post, the reading log dropdown wasn't clearing in between searches.

= 1.1.2 - 31 January 2021 =

* Fix: Excessive REST API requests coming from the Book Grid block.
* Fix: REST API error "array_intersect_key() expected parameter 1 to be an array, string given".

= 1.1.1 - 26 May 2020 =

* New: Add "New Book" link to admin bar (under existing "New" menu).
* Fix: Grid & review templates not using rating format settings.
* Fix: Admin series page incorrectly counting rereads in "Books Read" column.

= 1.1 - 2 May 2020 =

* New: Redesigned Analytics page - now with sections, graphs, charts, and a whole lot more data.
* New: Added Gutenberg block for displaying a book grid (<code>[book-grid]</code> shortcode equivalent).
* New: Added two widgets: Reviews and Reading Log.

= 1.0.7 - 14 April 2020 =

* Tweak: Increase maximum taxonomy term results in reviews shortcode to 300.
* Fix: Saving a taxonomy term that includes an apostrophe could result in a second, duplicate term getting added instead of using the existing one.

= 1.0.6 - 1 March 2020 =

* Improvement: Uninstall logic & protection against errors.
* Fix: Datepicker values not being loaded when editing a second date field on the same page. Example: edit a reading log, then edit a second reading log on the same page.
* Fix: Unable to remove terms from a book if you're removing all terms of the same taxonomy.
* Fix: Unable to add a new source value while adding a new owned edition.
* Fix: Terms page may not load the first taxonomy and you had to explicitly click on one to load it.
* Database: `source_id` now defaults to `null` in `wp_bdb_owned_editions` table.
* Database: Remove all instances of `0000-00-00 00:00:00` default values in schemas.
* Database: Allow `date_acquired` to be `null`.

= 1.0.5 - 20 February 2020 =

* New: When adding a reading log entry you can now link it to a specific edition.
* Fix: Validate boolean values on shortcode attributes. This fixes problems with things like `show-pub-date="false"` not working right.
* Fix: `rating` parameter not working in `[book-grid] shortcode.
* Fix: "Read Review" link not showing up on `[book-grid]` shortcode when unread books appear in the grid.

= 1.0.4 - 8 February 2020 =

* New: Added "Audiobook" to book format dropdown.
* Fix: Syncing review publish date to post publish date doesn't use the correct timezone.
* Fix: Unable to unset/remove reading log finish date.

= 1.0.3 - 9 January 2020 =

* New: Add unit tests.
* Tweak: Change datepicker library.
* Tweak: Split off moment.js into separate file.
* Fix: Issues with admin UI on smaller screens - particularly when updating reading progress via Edit Book page.
* Fix: Division by zero error in analytics under certain conditions.
* Fix: [book-reviews] taxonomy filter doesn't display which term is selected.
* Fix: Displaying books attached to a certain term doesn't work (Book Library > Terms > clicking on "Book Count").
* Fix: Unable to use decimals in series position field.
* Fix: Adding a book to an existing series that contains an apostrophe incorrectly creates a second series entry.
* Fix: unique_book_slug() not returning alternate slug.
* Fix: Admin table bulk actions not working.

= 1.0.2 - 11 December 2019 =

* New: Bring back old `[book-grid]` shortcode attributes.
* Tweak: Load assets on all pages but allow filter override.
* Tweak: Don't add pagination HTML to shortcodes if there's only one page.
* Tweak: Order "Books in Series" by series position.

= 1.0.1 - 28 November 2019 =

* New: Allow specifying a page number instead of percentage when updating reading progress.
* Tweak: Analytics - Change "Total Books Finished" label to "Books Finished".
* Fix: Analytics - "read not reviewed" table not pulling correct books.
* Fix: Book query values being double escaped in LIKE clauses.
* Fix: UI issues on WP 5.3.
* Fix: Round percentages on display to nearest integer.

= 1.0 - 6 November 2019 =

* Initial release.
