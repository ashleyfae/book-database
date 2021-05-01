/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';
import { dateLocalToUTC, dateUTCtoLocal } from "./dates";

/**
 * Post Metabox
 */
var BDB_Post_Metabox = {

	postID: 0,

	userID: 0,

	bookID: 0,

	table: false,

	tableBody: false,

	errorWrap: false,

	searchResultsWrap: false,

	/**
	 * Initialize
	 */
	init: function() {

		if ( ! document.getElementById('bdb-post-reviews-table') ) {
			return;
		}

		this.table = $( '#bdb-post-reviews-table' );
		this.postID = this.table.data( 'post-id' );
		this.userID = this.table.data( 'user-id' );
		this.tableBody = this.table.find( 'tbody' );
		this.errorWrap = $( '#bdb-post-reviews-errors' );
		this.searchResultsWrap = $( '#bdb-book-search-results' );

		this.getReviews();

		$( '#bdb-associated-review-post' ).on( 'click', this.toggleSearch );
		$( '#bdb-search-book-title-author' ).on( 'keypress', this.searchBooks );
		$( '#bdb-search-book-fields' ).on( 'click', 'button', this.searchBooks );
		this.searchResultsWrap.on( 'click', 'a', this.selectBook );
		$( '#bdb-add-review' ).on( 'click', this.addReview );
		$( document ).on( 'click', '.bdb-disassociate-review-from-post', this.disassociateReview );
		$( document ).on( 'click', '.bdb-delete-review', this.deleteReview );

	},

	/**
	 * Get the reviews associated with this post
	 */
	getReviews: function() {

		let args = {
			rating_format: 'text',
			review_query: [ {
				field: 'post_id',
				value: BDB_Post_Metabox.postID
			} ]
		};

		apiRequest( 'v1/reviews', args, 'GET' ).then( function( response ) {

			BDB_Post_Metabox.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Post_Metabox.tableBody.append( wp.template( 'bdb-table-post-reviews-row-empty' ) );
			} else {
				$.each( response, function( key, review ) {
					BDB_Post_Metabox.tableBody.append( wp.template( 'bdb-table-post-reviews-row' )( review ) );
				} );
			}

		} ).catch( function( error ) {
			BDB_Post_Metabox.errorWrap.empty().append( error ).show();
		} );

	},

	/**
	 * Toggle the search fields
	 *
	 * @param e
	 */
	toggleSearch: function ( e ) {
		e.preventDefault();
		$( '#bdb-search-book-fields' ).slideToggle();
	},

	/**
	 * Search for books
	 *
	 * @param e
	 */
	searchBooks: function ( e ) {

		if ( 'click' === e.type ) {
			e.preventDefault();
		}

		if ( 'keypress' === e.type && 13 !== e.which ) {
			return true;
		} else {
			e.preventDefault();
		}

		let button = $( '#bdb-search-book-fields' ).find( 'button' ),
			search = $( '#bdb-search-book-title-author' ).val(),
			searchType = $( '#bdb-search-book-type' ).val(),
			args = {};

		spinButton( button );

		BDB_Post_Metabox.errorWrap.empty().hide();
		BDB_Post_Metabox.searchResultsWrap.empty();

		if ( 'author' === searchType ) {
			args.author_query = [ {
				field: 'name',
				value: search,
				operator: 'LIKE'
			} ];
		} else {
			args.book_query = [ {
				field: 'title',
				value: search,
				operator: 'LIKE'
			} ];
		}

		apiRequest( 'v1/books', args, 'GET' ).then( function( apiResponse ) {

			if ( 0 === apiResponse.length || 'undefined' === typeof apiResponse.length ) {
				BDB_Post_Metabox.searchResultsWrap.append( '<p>' + bdbVars.no_books + '</p>' );
			} else {
				let booksHTML = '';
				$.each( apiResponse, function( key, book ) {
					booksHTML = booksHTML + '<li><a href="#" data-id="' + book.id + '">' + book.title + ' ' + bdbVars.by + ' ' + book.author_name + '</a></li>';
				} );
				BDB_Post_Metabox.searchResultsWrap.append( '<ul>' + booksHTML + '</ul>' );
			}

		} ).catch( function( errorMessage ) {
			BDB_Post_Metabox.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Select a book to review
	 *
	 * @param e
	 */
	selectBook: function ( e ) {

		e.preventDefault();

		// Set the book ID.
		BDB_Post_Metabox.bookID = $( this ).data( 'id' );

		// Wipe the search results.
		BDB_Post_Metabox.searchResultsWrap.empty().append( '<p>' + bdbVars.please_wait + '</p>' );

		let args = {
			book_id: $( this ).data( 'id' )
		};

		// Get reading logs.
		apiRequest( 'v1/reading-log', args, 'GET' ).then( function( apiResponse ) {

			let logOptions = $( '#bdb-review-reading-log' );

			if ( apiResponse.length > 0 ) {
				logOptions.empty();
				$.each( apiResponse, function( key, log ) {
					logOptions.append( '<option value="' + log.id + '">' + BDB_Post_Metabox.shapeLog( log ) + '</option>' );
				} )
			}

			// Wipe the search results again.
			BDB_Post_Metabox.searchResultsWrap.empty();

			// Show the log selection.
			$( '#bdb-add-review-fields' ).show();

		} ).catch( function( errorMessage ) {
			BDB_Post_Metabox.errorWrap.append( errorMessage ).show();
		} );

	},

	/**
	 * Shape the reading log entry for display in an `<option>`
	 *
	 * @param {object} readingLog
	 * @returns {string}
	 */
	shapeLog: function ( readingLog ) {

		readingLog.date_started_formatted  = dateUTCtoLocal( readingLog.date_started, 'display' );
		readingLog.date_finished_formatted = dateUTCtoLocal( readingLog.date_finished, 'display' );
		readingLog.rating                  = null === readingLog.rating ? null : parseFloat( readingLog.rating ) + ' ' + bdbVars.stars;

		if ( ! readingLog.date_started_formatted ) {
			readingLog.date_started_formatted = '(' + bdbVars.unknown + ')';
		}
		if ( ! readingLog.date_finished_formatted ) {
			readingLog.date_finished_formatted = '(' + bdbVars.unknown + ')';
		}

		return readingLog.date_started_formatted + ' - ' + readingLog.date_finished_formatted + ' (' + readingLog.rating + ')';

	},

	/**
	 * Add a new review
	 *
	 * @param e
	 */
	addReview: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );

		let args = {
			book_id: BDB_Post_Metabox.bookID,
			reading_log_id: $( '#bdb-review-reading-log' ).val(),
			user_id: BDB_Post_Metabox.userID,
			post_id: BDB_Post_Metabox.postID
		};

		apiRequest( 'v1/review/add', args, 'POST' ).then( function( apiResponse ) {

			let args = {
				rating_format: 'text',
				review_query: [ {
					field: 'id',
					value: apiResponse.id
				} ]
			};

			return apiRequest( 'v1/reviews', args, 'GET' );

		} ).then( function( apiResponse ) {

			// Add the review(s) to the table.
			$.each( apiResponse, function( key, review ) {
				$( '#bdb-no-post-reviews' ).remove();
				BDB_Post_Metabox.tableBody.append( wp.template( 'bdb-table-post-reviews-row' )( review ) );
			} );

			// Hide the log selection.
			$( '#bdb-add-review-fields' ).hide();

		} ).catch( function( errorMessage ) {
			BDB_Post_Metabox.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Disassociate a review from this post
	 *
	 * @param e
	 * @returns {boolean}
	 */
	disassociateReview: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_remove_review_association ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );

		BDB_Post_Metabox.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let args = {
			post_id: null
		};

		apiRequest( 'v1/review/update/' + wrap.data( 'id' ), args, 'POST' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Post_Metabox.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Delete a review
	 *
	 * @param e
	 * @returns {boolean}
	 */
	deleteReview: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_delete_review ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );

		BDB_Post_Metabox.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let args = {
			post_id: null
		};

		apiRequest( 'v1/review/delete/' + wrap.data( 'id' ), args, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Post_Metabox.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_Post_Metabox }
