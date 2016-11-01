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

    activeEditorID: false,

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

    editingBook: 0,

    /**
     * Set Book
     *
     * @param args
     */
    setBook: function (args) {
        this.editingBook = args.bookID ? args.bookID : 0;
        this.clearBookFields();

        if (0 !== this.editingBook) {

            var button = jQuery('.wpdb-button-action');

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

        var wrap = jQuery('.bookdb-frame-content');

        wrap.find('input, textarea').each(function () {
            var self = jQuery(this);

            // Bail if this is a submit button.
            if ('submit' == self.attr('type')) {
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
         * `author` - array
         * `series_id`
         * `series_name`
         * `series_position`
         * `pub_date`
         * `synopsis`
         */

        console.log(book);

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

        if (book.series_name) {
            jQuery('#book_series_name').val(book.series_name);
        }

        if (book.series_position) {
            jQuery('#book_series_position').val(book.series_position);
        }

        if (book.pub_date) {
            jQuery('#book_pub_date').val(book.pub_date);
        }

        if (book.synopsis) {
            jQuery('#book_synopsis').val(book.synopsis);
        }

    },

    /**
     * Insert or Update Book
     *
     * @param button
     */
    insert_update_book: function (button) {

        var book = {
            ID: this.editingBook,
            cover: jQuery('#book_cover_id').val(),
            title: jQuery('#book_title').val(),
            series_name: jQuery('#book_series_name').val(),
            series_position: jQuery('#book_series_position').val(),
            pub_date: jQuery('#book_pub_date').val(),
            synopsis: jQuery('#book_synopsis').val()
        };

        var seriesID = jQuery('#series_id');

        if (seriesID.length) {
            book.series_id = seriesID.val();
        }

        var data = {
            action: 'bdb_save_book',
            nonce: bookdb_modal.nonce,
            book: book
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

                    if (0 === BookDB_Modal_Admin.editingBook) {
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
        }

    };

    BookDB_Modal.init();

});