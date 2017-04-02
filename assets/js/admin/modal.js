/**
 * Modal Handler
 *
 * Lovingly borrowed from WP Recipe Maker.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

var BookDB_Modal_Admin = {

	/**
	 * Editor ID
	 */
	activeEditorID: false,

	/**
	 * Whether or not this is a new insert but for
	 * an existing book. This means they used the search function
	 * to find an existing book to insert.
	 */
	newButExistingBook: false,

	/**
	 * Shortcode Escape Map
	 */
	shortcodeEscapeMap: {
		'"': "'"
	},

	/**
	 * Disable Menu
	 */
	disableMenu: function () {
		jQuery('.bookdb-frame-menu').find('.bookdb-menu-item').hide();
	},

	/**
	 * Open Modal
	 *
	 * @param editor_id
	 * @param args
	 */
	openModal: function (editor_id, args) {
		args = args === undefined ? {} : args;

		// Reset button
		jQuery('.bookdb-button-action').text(bookdb_modal.l10n.action_button_default).show();

		// Enable menu items
		jQuery('.bookdb-menu-item').show();

		BookDB_Modal_Admin.activeEditorID = editor_id;
		jQuery('.bookdb-modal-container').show();

		// Init tabs
		var tabs = jQuery('.bookdb-router').find('.bookdb-menu-item');
		jQuery(tabs).each(function () {
			var init_callback = jQuery(this).data('init');

			if (init_callback && typeof BookDB_Modal_Admin[init_callback] == 'function') {
				BookDB_Modal_Admin[init_callback](args);
			}
		});

		// Default to first menu item
		jQuery('.bookdb-menu').find('.bookdb-menu-item').first().click();
	},

	/**
	 * Close Modal
	 */
	closeModal: function () {
		BookDB_Modal_Admin.activeEditorID = false;
		jQuery('.bookdb-menu').removeClass('visible');
		jQuery('.bookdb-modal-container').hide();
	},

	/**
	 * Shortcode Escape
	 *
	 * @param text
	 * @returns {string}
	 */
	shortcodeEscape: function (text) {
		return String(text).replace(/["]/g, function (s) {
			return BookDB_Modal_Admin.shortcodeEscapeMap[s];
		});
	},

	/**
	 * Add Text to Editor
	 *
	 * @param text
	 */
	addToEditor: function (text) {
		text = ' ' + text + ' ';

		if (BookDB_Modal_Admin.activeEditorID) {
			if (typeof tinyMCE == 'undefined' || !tinyMCE.get(BookDB_Modal_Admin.activeEditorID) || tinyMCE.get(BookDB_Modal_Admin.activeEditorID).isHidden()) {
				var current = jQuery('textarea#' + BookDB_Modal_Admin.activeEditorID).val();
				jQuery('textarea#' + BookDB_Modal_Admin.activeEditorID).val(current + text);
			} else {
				tinyMCE.get(BookDB_Modal_Admin.activeEditorID).focus(true);
				tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
			}
		}
	},

	/**
	 * Start Loader (start ajax)
	 *
	 * @param button
	 */
	startLoader: function (button) {
		button
			.prop('disabled', true)
			.css('width', button.outerWidth())
			.data('text', button.html())
			.html('...');
	},

	/**
	 * Stop Loader (stop ajax)
	 *
	 * @param button
	 */
	stopLoader: function (button) {
		button
			.prop('disabled', false)
			.css('width', '')
			.html(button.data('text'));
	},

	/** Book Specific Stuff **/

	/**
	 * ID of the book we're editing. 0 if a new book.
	 */
	editingBook: 0,

	/**
	 * Set Book
	 *
	 * @param args
	 */
	setBook: function (args) {
		this.editingBook = args.bookID ? args.bookID : 0;
		this.clearBookFields();

		// Set book fields for existing book.
		if (0 !== this.editingBook) {

			var button = jQuery('.bookdb-button-action');

			button.text(bookdb_modal.l10n.action_button_update);
			this.disableMenu();

			var data = {
				action: 'bdb_get_book',
				nonce: bookdb_modal.nonce,
				book_id: this.editingBook
			};

			this.startLoader(button);

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: "json",
				success: function (response) {

					console.log(response);

					BookDB_Modal_Admin.stopLoader(button);

					if (response.success) {

						BookDB_Modal_Admin.setBookFields(response.data);

					} else {

						if (window.console && window.console.log) {
							console.log(response);
						}

					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}
			});

		}
	},

	/**
	 * Clear Book Fields
	 *
	 * Remove all values from text inputs and reset all
	 * radio buttons and checkboxes.
	 */
	clearBookFields: function () {

		// New but existing book back to false.
		this.newButExistingBook = false;

		// Clear search results.
		jQuery('#bookdb-existing-book-results').empty();

		// Set heading back to normal.
		jQuery('.bookdb-book-details-form > h3').text(bookdb_modal.l10n.insert_new_book);

		var wrap = jQuery('.bookdb-frame-content');

		wrap.find('input, textarea').each(function () {
			var self = jQuery(this);

			// Bail if this is a submit button.
			if ('submit' == self.attr('type') || 'button' == self.attr('type')) {
				return true;
			}

			// Bail if this is a term checkbox.
			if ('checkbox' == self.attr('type')) {
				self.prop('checked', false); // uncheck
				return true;
			}

			// Clear all input values.
			self.val('');
		});

		// Hide cover image
		jQuery('#bookdb-cover-image').attr('src', '').hide();
		jQuery('.bookdb-remove-image').hide();

	},

	/**
	 * Set Book Fields
	 *
	 * Populate fields with book information.
	 *
	 * @param book
	 */
	setBookFields: function (book) {

		/*
		 * `book` contains:
		 *
		 * `ID`
		 * `cover_id`
		 * `cover_url`
		 * `title`
		 * `index_title`
		 * `index_title_choices`
		 * `author` - array
		 * `author_comma` - Comma-separated list of author names
		 * `series_id`
		 * `series_name`
		 * `series_position`
		 * `pub_date`
		 * `pages`
		 * `goodreads_url`
		 * `synopsis`
		 * `terms`
		 */

		console.log(book);

		jQuery('.bookdb-book-details-form > h3').text(bookdb_modal.l10n.editing + '"' + book.title + '"');

		if (book.cover_id && 0 != book.cover_id) {
			jQuery('#book_cover_id').val(book.cover_id);
			jQuery('.bookdb-remove-image').show();
		}

		if (book.cover_url) {
			jQuery('#bookdb-cover-image').attr('src', book.cover_url).show();
		}

		if (book.title) {
			jQuery('#book_title').val(book.title);
		}

		// Start Index Title

		var selectedIndexTitle;

		if (!book.index_title || book.index_title == book.title) {
			selectedIndexTitle = 'original';
		} else if (book.index_title in book.index_title_choices) {
			selectedIndexTitle = book.index_title;
		} else {
			selectedIndexTitle = 'custom';
		}

		if (book.index_title) {
			var indexTitleField = jQuery('#index_title');
			indexTitleField.empty().append('<option value="original">' + book.title + '</option>');

			jQuery.each(book.index_title_choices, function (value, name) {
				if ('original' != value) {
					indexTitleField.append('<option value="' + value + '">' + name + '</option>')
				}
			});

			indexTitleField.append('<option value="custom">' + bookdb_modal.l10n.custom + '</option>').val(selectedIndexTitle);
		}

		if ('custom' == selectedIndexTitle) {
			jQuery('#index_title_custom').val(book.index_title).show();
		}

		// End Index Title

		if (book.author_comma) {
			jQuery('#bookdb-input-tag-author').val(book.author_comma);
		}

		if (book.series_name) {
			jQuery('#book_series_name').val(book.series_name);
		}

		if (book.series_position) {
			jQuery('#book_series_position').val(book.series_position);
		}

		if (book.pub_date) {
			jQuery('#book_pub_date').val(book.pub_date);
		}

		if (book.pages) {
			jQuery('#book_pages').val(book.pages);
		}

		if (book.goodreads_url) {
			jQuery('#book_goodreads_url').val(book.goodreads_url);
		}

		if (book.synopsis) {
			jQuery('#book_synopsis').val(book.synopsis);
		}

		if (book.terms) {
			jQuery.each(book.terms, function (type, terms) {

				if (typeof terms === 'string') {

					// Tags
					jQuery('#bookdb-input-tag-' + type).val(terms);

				} else {

					// Categories
					var wrap = jQuery('#dbd-checkboxes-' + type);

					if (!wrap.length) {
						return true;
					}

					wrap.find('input[type="checkbox"]').each(function () {
						var thisCheckbox = jQuery(this);
						if (jQuery.inArray(thisCheckbox.val(), terms) != -1) {
							jQuery(this).prop('checked', true);
						}
					});

				}

			});
		}

		jQuery(document).trigger('bdb_modal_set_book_fields', book);

	},

	/**
	 * Insert or Update Book
	 *
	 * @param button
	 */
	insert_update_book: function (button) {

		jQuery(document).trigger('bdb_modal_before_insert_update_book');

		var bookInfo = jQuery('#bookdb-modal-form').serialize();

		var data = {
			action: 'bdb_save_book',
			nonce: bookdb_modal.nonce,
			book_id: this.editingBook,
			book_info: bookInfo
		};

		this.startLoader(button);

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			dataType: "json",
			success: function (response) {

				BookDB_Modal_Admin.stopLoader(button);

				if (response.success) {

					if (0 === BookDB_Modal_Admin.editingBook || true == BookDB_Modal_Admin.newButExistingBook) {
						BookDB_Modal_Admin.addToEditor('[book id="' + response.data + '"]');
					} else {
						// Refresh content in editor.
						if (typeof tinyMCE !== 'undefined' && tinyMCE.get(BookDB_Modal_Admin.activeEditorID) && !tinyMCE.get(BookDB_Modal_Admin.activeEditorID).isHidden()) {
							tinyMCE.get(BookDB_Modal_Admin.activeEditorID).focus(true);
							tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent());
						}
					}

					BookDB_Modal_Admin.closeModal();

				} else {

					if (window.console && window.console.log) {
						console.log(response);
					}

				}

			}
		}).fail(function (response) {
			if (window.console && window.console.log) {
				console.log(response);
			}
		});

	},

	/**
	 * Insert or Update Book Grid
	 *
	 * @param button
	 */
	insert_update_grid: function (button) {

		var params = '';

		// Author Name
		var authorName = jQuery('#grid_book_author').val();
		if (authorName != '') {
			params = params + ' author="' + authorName + '"';
		}

		// Series Name
		var seriesName = jQuery('#grid_book_series').val();
		if (seriesName != '') {
			params = params + ' series="' + seriesName + '"';
		}

		// Pub Start Date
		var startDate = jQuery('#grid_pub_date_start').val();
		if (startDate != '') {
			params = params + ' start-date="' + startDate + '"';
		}

		// Pub End Date
		var endDate = jQuery('#grid_pub_date_end').val();
		if (endDate != '') {
			params = params + ' end-date="' + endDate + '"';
		}

		// Review Start Date
		var startDate = jQuery('#grid_review_date_start').val();
		if (startDate != '') {
			params = params + ' review-start-date="' + startDate + '"';
		}

		// Review End Date
		var endDate = jQuery('#grid_review_date_end').val();
		if (endDate != '') {
			params = params + ' review-end-date="' + endDate + '"';
		}

		// Rating
		var rating = jQuery('#grid_book_rating').val();
		if (rating != 'all') {
			params = params + ' rating="' + rating + '"';
		}

		jQuery('.book-grid-term').each(function () {
			var termVal = jQuery(this).val();

			if ('all' !== termVal) {
				params = params + ' ' + jQuery(this).data('term-type') + '="' + jQuery(this).val() + '"';
			}
		});

		// Show Ratings
		if (jQuery('#grid_show_ratings').is(':checked')) {
			params = params + ' show-ratings="true"';
		}

		// Show Review Link
		if (jQuery('#grid_show_review_link').is(':checked')) {
			params = params + ' show-review-link="true"';
		}

		// Show Goodreads Link
		if (jQuery('#grid_show_goodreads_link').is(':checked')) {
			params = params + ' show-goodreads-link="true"';
		}

		// Reviews Only
		if (jQuery('#grid_reviews').is(':checked')) {
			params = params + ' reviews-only="true"';
		}

		// Book IDs
		var bookIDs = jQuery('#grid_book_ids').val();
		if (bookIDs !== '') {
			params = params + ' ids="' + bookIDs + '"';
		}

		// Orderby
		var orderBy = jQuery('#grid_order_by').val();
		if (orderBy != 'id') {
			params = params + ' orderby="' + orderBy + '"';
		}

		// Order
		var order = jQuery('#grid_order').val();
		if (order != 'DESC') {
			params = params + ' order="ASC"';
		}

		// Max Results
		var number = jQuery('#grid_number').val();
		if (number != '20' && number != '') {
			params = params + ' number="' + number + '"';
		}

		jQuery(document).trigger('bdb_modal_before_insert_update_grid', params);

		BookDB_Modal_Admin.addToEditor('[book-grid' + params + ']');
		BookDB_Modal_Admin.closeModal();

	}

};

jQuery(document).ready(function ($) {

	var BookDB_Modal = {

		/**
		 * Initialize all the things.
		 */
		init: function () {
			$(document).on('click', '.bookdb-modal-button', this.openModal);
			$('.bookdb-modal-container')
				.on('click', '.bookdb-modal-close, .bookdb-modal-backdrop', BookDB_Modal_Admin.closeModal)
				.on('click', 'bookdb-frame-title', this.mobileMenu);
			$('.bookdb-menu').on('click', '.bookdb-menu-item', this.menu);
			$('.bookdb-router').on('click', '.bookdb-menu-item', this.modalTabs);
			$('.bookdb-button-action').on('click', this.buttonCallback);
			$('#bookdb-search-existing-book').on('click', this.searchExistingBook);
		},

		/**
		 * Open Modal
		 * @param e
		 */
		openModal: function (e) {
			var editor_id = $(this).data('editor');
			BookDB_Modal_Admin.openModal(editor_id);
		},

		/**
		 * Menu Handler
		 */
		menu: function () {
			var menu_item = $(this),
				menu_target = menu_item.data('menu'),
				menu_tab = menu_item.data('tab'),
				menu = $('.bookdb-menu');

			// Hide Menu if on Mobile
			menu.removeClass('visible');

			// Set clicked on tab as the active one
			menu.find('.bookdb-menu-item').removeClass('active');
			menu_item.addClass('active');

			// Show correct menu
			$('.bookdb-frame-router').find('.bookdb-router').removeClass('active');
			$('.bookdb-frame-router').find('#bookdb-menu-' + menu_target).addClass('active');

			// Show the first tab as active or whichever tab was passed along
			var active_tab = false;
			$('.bookdb-router').find('.bookdb-menu-item').removeClass('active');
			$('.bookdb-frame-router').find('#bookdb-menu-' + menu_target).find('.bookdb-menu-item').each(function (index) {
				if (index === 0 || $(this).data('tab') == menu_tab) {
					active_tab = $(this);
				}
			});

			if (active_tab) {
				active_tab.click();
			}

			// Change main title
			$('.bookdb-frame-title').find('h1').text(menu_item.text());
		},

		/**
		 * Mobile Menu Visibility
		 */
		mobileMenu: function () {
			$('.book-db-menu').toggleClass('visible');
		},

		/**
		 * Modal Tabs
		 */
		modalTabs: function (e) {
			e.preventDefault();

			var menu_item = $(this),
				tab_target = menu_item.data('tab'),
				frameContent = $('.bookdb-frame-content');

			// Set clicked on tab as the active one
			$('.bookdb-router').find('.bookdb-menu-item').removeClass('active');
			menu_item.addClass('active');

			// Hide action button if no callback is set
			if (menu_item.data('callback')) {
				$('.bookdb-button-action').show();
			} else {
				$('.bookdb-button-action').hide();
			}

			// Show correct tab
			frameContent.find('.bookdb-frame-content-tab').removeClass('active');
			frameContent.find('#bookdb-tab-' + tab_target).addClass('active');
		},

		/**
		 * Insert/Update Button Callback
		 */
		buttonCallback: function () {
			var active_tab = $('.bookdb-router.active').find('.bookdb-menu-item.active'),
				callback = active_tab.data('callback');

			if (typeof BookDB_Modal_Admin[callback] == 'function') {
				BookDB_Modal_Admin[callback]($(this));
			}
		},

		/**
		 * Search for an Existing Book
		 *
		 * @param e
		 */
		searchExistingBook: function (e) {
			e.preventDefault();

			var button = $(this),
				resultsWrap = $('#bookdb-existing-book-results');

			button.attr('disabled', true);
			resultsWrap.empty().append('<span class="spinner is-active"></span>');

			var data = {
				action: 'bdb_search_book',
				nonce: bookdb_modal.nonce,
				search: $('#bookdb-search-existing').val(),
				field: $('#bookdb-search-field').val()
			};

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: "json",
				success: function (response) {

					button.attr('disabled', false);
					resultsWrap.empty().append(response.data);

					if (response.success) {

						resultsWrap.on('click', 'a', function () {
							BookDB_Modal_Admin.setBook({bookID: $(this).data('id')});
							BookDB_Modal_Admin.newButExistingBook = true;
						});

					} else {

						if (window.console && window.console.log) {
							console.log(response);
						}

					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}
			});
		}

	};

	BookDB_Modal.init();

});