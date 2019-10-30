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

## Fun Queries:

Most read books:

```mysql
SELECT
  book_id,
  book.title,
  COUNT(*) AS count
FROM `wp_bdb_reading_log` log INNER JOIN `wp_bdb_books` book ON book.id = log.book_id
GROUP BY book_id
HAVING count > 1
ORDER BY count DESC
LIMIT 25
```

Books you own two or more copies of:

```mysql
SELECT
  book_id,
  book.title,
  COUNT(*) AS number_copies
FROM `wp_bdb_owned_editions` e INNER JOIN `wp_bdb_books` book ON book.id = e.book_id
GROUP BY book_id
HAVING number_copies > 1
ORDER BY number_copies DESC
```

Get all the books and their ratings from a specific term name ("Fantasy") that were read in 2017.

```mysql
SELECT
  book.title,
  log.rating,
  log.date_finished
FROM `wp_bdb_books` book RIGHT JOIN `wp_bdb_reading_log` log ON log.book_id = book.id
  INNER JOIN `wp_bdb_book_term_relationships` r ON r.book_id = book.id
  INNER JOIN `wp_bdb_book_terms` term ON (term.term_id = r.term_id AND term.name = 'Fantasy')
WHERE 2017 = YEAR (date_finished)
ORDER BY log.date_finished ASC
```

Same as above, but checks reviews only, where the review was written in a specific year (2017).

```mysql
SELECT
  book.title,
  log.rating
FROM `wp_bdb_reviews` review RIGHT JOIN `wp_bdb_reading_log` log ON log.review_id = review.id
  INNER JOIN `wp_bdb_books` book ON book.id = review.book_id
  INNER JOIN `wp_bdb_book_term_relationships` r ON r.book_id = review.book_id
  INNER JOIN `wp_bdb_book_terms` term ON (term.term_id = r.term_id AND term.name = 'Fantasy')
WHERE 2017 = YEAR (date_written)
ORDER BY book.title ASC
```

Get books with 4 stars or higher in the genres "Contemporary" _and_ "Romance":

```mysql
SELECT
  book.title,
  author.name,
  log.rating
FROM wp_bdb_books AS book
  INNER JOIN wp_bdb_book_author_relationships AS r ON book.id = r.book_id
  INNER JOIN wp_bdb_authors AS author ON r.author_id = author.id
  INNER JOIN wp_bdb_reading_log AS log ON book.id = log.book_id
WHERE log.rating > 4
      AND book.id IN (
  SELECT book_id
  FROM wp_bdb_book_term_relationships AS r
    INNER JOIN wp_bdb_book_terms AS t ON r.term_id = t.term_id
  WHERE t.name = 'Contemporary'
        AND book_id IN (
    SELECT book_id
    FROM wp_bdb_book_term_relationships AS r2
      INNER JOIN wp_bdb_book_terms AS t2 ON r2.term_id = t2.term_id
    WHERE t2.name = 'Romance'
  )
)
GROUP BY book.id
ORDER BY log.rating DESC
```

Get a count of how many books were read in each format in a given year (2017).

```mysql
SELECT
  format,
  COUNT(*) AS count
FROM `wp_bdb_owned_editions` AS b
  INNER JOIN wp_bdb_reading_log AS l ON l.book_id = b.book_id
WHERE 2017 = YEAR (date_finished)
GROUP BY format
ORDER BY format ASC;
```

Get a count of how many books read in 2017 were published in each year.

```mysql
SELECT
  YEAR(pub_date) AS pub_year,
  COUNT(*) AS number_books
FROM wp_bdb_books AS b
  INNER JOIN wp_bdb_reading_log AS l ON l.book_id = b.id
WHERE 2017 = YEAR (date_finished)
GROUP BY YEAR(pub_date)
ORDER BY pub_year DESC;
```