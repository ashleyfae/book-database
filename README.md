# Book Database

WIP

Book Database is for managing a library of books, documenting book reviews, and tracking review analytics.

I have a book review plugin already, but Book Database is a fun recoding project for me. It's basically, "what would my other plugin look like if I could start over with zero backwards compatibility?" -- Basically a rewrite considering all the things I now hate about my other plugin and what I wish I could have done differently.

Things I'm focusing on:

* Fewer third party libraries. These started to really piss me off in my other plugin.
* Better job scaling, when people want to add *thousands* of books.
* Better UI and UX.
* And finally, I'm focusing more on what I want from this plugin, rather than what other people say they want.

I'm not sure if I'll ever officially release this. I'm mostly building this for myself and for funsies. Then we'll see what happens.

## Features:

* Add books to the database with book information (title, author, cover, synopsis, etc.).
* Create reviews and associate those reviews with books.
* Display book information in blog posts via a shortcode.
* Maintain a front-end archive of all your book reviews.
* See analytics about the books you've reviewed during a specific date range.

## Todo:

* Analytics: datepicker for custom range.
* Front-end shortcode statistics. Or maybe not.
* Possibly add something for tropes. Or this could just be a book term.
* Remove menu from book modal.
* Add reading list.

## Fun Queries:

Most read books:

`SELECT book_id, book.title, COUNT(*) AS count FROM `wp_bdb_reading_list` list INNER JOIN `wp_bdb_books` book on book.ID = list.book_id GROUP BY book_id ORDER BY count DESC LIMIT 25`

Get all the books and their ratings from a specific term name ("Fantasy") and within a specific time period (the year 2016):

`SELECT book.title, review.rating FROM `wp_bdb_reviews` review INNER JOIN `wp_bdb_books` book on book.ID = review.book_id INNER JOIN `wp_bdb_book_term_relationships` r on r.book_id = review.book_id INNER JOIN `wp_bdb_book_terms` term on (term.term_id = r.term_id AND term.name = 'Fantasy') WHERE `date_written` >= '2016-01-01 00:00:00' AND `date_written` <= '2016-12-31 00:00:00' ORDER BY book.title ASC`