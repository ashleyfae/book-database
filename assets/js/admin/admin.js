/**
 * Admin Scripts
 *
 * @package book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license GPL2+
 */

(function ($) {

    var Book_Database = {

        /**
         * Initialize stuff
         */
        init: function () {
            this.sort();
            this.clone();

            $('.bookdb-book-option-toggle').click(this.toggleBookTextarea);
            $('#bookdb-book-layout-cover-changer').change(this.changeCoverAlignment);
            $('.bookdb-new-checkbox-term').on('click', '.button', this.addCheckboxTerm);
            $('.bookdb-new-checkbox-term .bookdb-new-checkbox-term-value').keypress(this.addCheckboxTerm);
            $('#bookdb-add-review').on('click', this.toggleAddReviewFields);
            $('#bookdb-add-review-search-book-input').keypress(this.searchForBookToReview);
            $('#bookdb-add-review-search-for-book').on('click', 'button', this.searchForBookToReview);
            $('#bookdb-add-review-fields-wrap').on('click', 'button', this.addReview);
            $('#bdb_book_reviews').on('click', '.bookdb-remove-book-review', this.removeReview);
            $('#index_title').on('change', this.toggleCustomIndexTitle);
            $('#book_title').on('keyup', this.writeOriginalIndexTitle)
                .on('blur', this.populateAltTitles);
            $(document).ready(this.toggleCustomIndexTitle);
            $('#bookdb-read-book').on('click', this.toggleReadingListFields);
            $('#bookdb-submit-reading-entry').on('click', this.submitReadingEntry);
            $('.bookdb-edit-reading-entry').on('click', this.editReadingEntry);
            $('.bookdb-delete-reading-entry').on('click', this.deleteReadingEntry);
            $(document).ready(this.associateReadingLog);
            $('#insert_reading_log').on('change', this.associateReadingLog);
        },

        /**
         * Sort
         */
        sort: function () {
            $('.bookdb-sortable').sortable({
                cancel: '.bookdb-no-sort, textarea, input, select',
                connectWith: '.bookdb-sortable',
                placeholder: 'bookdb-sortable-placeholder',
                update: function (event, ui) {
                    var currentItem = ui.item;
                    var parentID = currentItem.parent().attr('id');
                    var disabledIndicator = currentItem.find('.bookdb-book-option-disabled');
                    if ($('#' + parentID).hasClass('bookdb-sorter-enabled-column')) {
                        disabledIndicator.val('false');
                    } else {
                        disabledIndicator.val('true');
                    }
                }
            }).enableSelection();
        },

        /**
         * Add New Taxonomy Type
         */
        clone: function () {
            if ($.isFunction($.fn.relCopy)) {
                $('#bookdb-add-term').relCopy();
            }
        },

        /**
         * Open up editable textarea.
         *
         * @param e
         */
        toggleBookTextarea: function (e) {
            $(this).next().slideToggle();
        },

        /**
         * Change cover alignment.
         *
         * @param e
         */
        changeCoverAlignment: function (e) {
            var parentDiv = $('#bookdb-book-option-cover');
            parentDiv.removeClass(function (index, css) {
                return (css.match(/(^|\s)bookdb-book-cover-align-\S+/g) || []).join(' ');
            });
            parentDiv.addClass('bookdb-book-cover-align-' + $(this).val());
        },

        /**
         * Add Checkbox Term
         *
         * @param e
         * @returns {boolean}
         */
        addCheckboxTerm: function (e) {
            if ('click' == e.type) {
                e.preventDefault();
            }

            if ('keypress' == e.type && 13 != e.which) {
                return true;
            } else {
                e.preventDefault();
            }

            var wrap = $(this).parents('.bookdb-taxonomy-checkboxes'),
                checkboxName = wrap.data('name'),
                checkboxWrap = wrap.find('.bookdb-checkbox-wrap'),
                newTerm = wrap.find('.bookdb-new-checkbox-term-value');

            checkboxWrap.append('<label><input type="checkbox" name="' + checkboxName + '" class="bookdb-checkbox" value="' + newTerm.val() + '" checked="checked"> ' + newTerm.val() + '</label>');
            newTerm.val('');
        },

        /**
         * Toggle 'Add Review' Fields
         *
         * @param e
         */
        toggleAddReviewFields: function (e) {
            e.preventDefault();
            $('#bookdb-add-review-fields').slideDown();
        },

        /**
         * Search for Book to Review
         *
         * @param e
         */
        searchForBookToReview: function (e) {
            if ('click' == e.type) {
                e.preventDefault();
            }

            if ('keypress' == e.type && 13 != e.which) {
                return true;
            } else {
                e.preventDefault();
            }

            var button = $(this).parents('#bookdb-add-review-search-for-book').find('button'),
                searchWrap = button.parents('#bookdb-add-review-search-for-book'),
                searchFor = $('#bookdb-add-review-search-book-input').val(),
                searchField = $('#book-db-add-review-search-type').val(),
                resultsWrap = $('#bookdb-book-search-results'),
                bookID = $('#bookdb-book-to-add-review');

            button.attr('disabled', true);
            searchWrap.append('<span class="spinner is-active"></span>');
            bookID.val(0);

            var data = {
                action: 'bdb_search_book',
                nonce: book_database.nonce,
                search: searchFor,
                field: searchField
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    button.attr('disabled', false);
                    searchWrap.find('.spinner').remove();
                    resultsWrap.empty().append(response.data).show();

                    if (response.success) {

                        resultsWrap.on('click', 'a', function (e) {
                            e.preventDefault();

                            // Clear search.
                            $('#bookdb-add-review-search-book-input').val('');

                            // Set book ID.
                            bookID.val($(this).data('id'));

                            var data = {
                                action: 'bdb_get_book_reading_logs',
                                nonce: book_database.nonce,
                                book_id: $(this).data('id')
                            };

                            $.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: data,
                                dataType: "json",
                                success: function (response) {
                                    console.log(response);

                                    if (response.success) {

                                        var readingLog = $('#reading_log');
                                        readingLog.empty().append('<option value="">None</option>');

                                        $.each(response.data, function (key, value) {
                                            readingLog.append('<option value="' + key + '">' + value + '</option>');
                                        });

                                    } else {

                                        if (window.console && window.console.log) {
                                            console.log(response);
                                        }

                                    }

                                    // Hide results.
                                    resultsWrap.hide();

                                    // Show review fields.
                                    $('#bookdb-add-review-fields-wrap').show();

                                }
                            }).fail(function (response) {
                                if (window.console && window.console.log) {
                                    console.log(response);
                                }
                            });
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
        },

        /**
         * Add Review
         *
         * @param e
         */
        addReview: function (e) {
            e.preventDefault();

            var button = $(this),
                wrap = $('#bookdb-add-review-fields-wrap'),
                book_id = $('#bookdb-book-to-add-review'),
                reading_log = $('#reading_log'),
                user_id = $('#review_user_id'),
                table = $('#bdb_book_reviews').find('table');

            button.attr('disabled', true);
            wrap.append('<span class="spinner is-active"></span>');

            var review = {
                book_id: book_id.val(),
                reading_log: reading_log.val(),
                user_id: user_id.val(),
                post_id: $('#post_ID').val()
            };

            var data = {
                action: 'bdb_save_review',
                nonce: book_database.nonce,
                review: review
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    button.attr('disabled', false);
                    wrap.find('.spinner').remove();

                    if (response.success) {

                        user_id.val(user_id.data('current'));

                        // Update table.
                        $('#bookdb-no-book-reviews-message').remove();

                        table.find('tbody').append('<tr data-id="' + response.data.ID + '"><td>' + response.data.ID + '</td><td>' + response.data.book + '</td><td>' + response.data.rating + '</td><td><code>' + response.data.shortcode + '</code></td><td>' + response.data.remove + '</td></tr>');

                        // Hide rating fields.
                        wrap.hide();

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
         * Remove Review
         *
         * @param e
         */
        removeReview: function (e) {

            e.preventDefault();

            if (!confirm(book_database.l10n.review_remove)) {
                return false;
            }

            var button = $(this),
                wrap = $(this).parents('tr'),
                reviewID = wrap.data('id');

            if (typeof reviewID == 'undefined' || !reviewID) {
                alert(book_database.l10n.error_removing_review);

                return false;
            }

            button.attr('disabled', true);
            button.parent().append('<span class="spinner is-active" style="float:none"></span>');

            var data = {
                action: 'bdb_remove_review',
                nonce: book_database.nonce,
                review_id: reviewID
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    if (response.success) {

                        wrap.remove();

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
         * Toggle Custom Index Title
         *
         * @param e
         */
        toggleCustomIndexTitle: function (e) {

            var indexValue = $('#index_title').val();
            var customField = $('#index_title_custom');

            if ('custom' == indexValue) {
                customField.slideDown().css('display', 'block');
            } else {
                customField.slideUp();
            }

        },

        /**
         * Copies the book title to the 'original' option in the index dropdown.
         *
         * @param e
         */
        writeOriginalIndexTitle: function (e) {
            $('#index_title option[value="original"]').text($(this).val());
        },

        /**
         * Populate index titles with alternatives.
         *
         * @param e
         */
        populateAltTitles: function (e) {
            var indexTitleSelect = $('#index_title');

            var data = {
                action: 'bdb_get_alt_titles',
                nonce: book_database.nonce,
                title: $(this).val()
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    if (response.success) {

                        indexTitleSelect.find('option[value="original"]').after('<option value="' + response.data + '">' + response.data + '</option>');

                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });
        },

        /**
         * Toggle Reading List FIelds
         *
         * @param e
         */
        toggleReadingListFields: function (e) {
            e.preventDefault();

            $('#bookdb-read-book-fields').slideToggle();
        },

        /**
         * Submit Reading Entry
         *
         * @param e
         */
        submitReadingEntry: function (e) {
            e.preventDefault();

            var button = $(this);
            var wrap = $('#bookdb-read-book-fields');

            button.attr('disabled', true);

            var entry = {
                book_id: wrap.data('book-id'),
                date_started: $('#reading_start_date').val(),
                date_finished: $('#reading_end_date').val(),
                user_id: $('#reading_user_id').val(),
                review_id: $('#review_id').val(),
                complete: $('#percent_complete').val(),
                rating: $('#book_rating').val()
            };

            var data = {
                action: 'bdb_save_reading_entry',
                nonce: book_database.nonce,
                entry: entry
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    button.attr('disabled', false);

                    if (response.success) {

                        $('#bookdb-no-reading-list-entries').remove();

                        wrap.parents('.postbox').find('tbody').append(response.data);

                        $('#bookdb-read-book-fields').slideUp();

                        Book_Database.init();

                    } else {
                        console.log(response);
                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });
        },

        /**
         * Edit Reading Entry
         *
         * @param e
         * @returns {boolean}
         */
        editReadingEntry: function (e) {
            e.preventDefault();

            var button = $(this);
            var wrap = $('#bookdb-read-book-fields');
            var tr = $(this).parents('tr');
            var entryID = tr.data('entry-id');
            var entry = {};

            if (typeof entryID == 'undefined' || entryID == '') {
                return false;
            }

            // Replace 'Edit' button.
            button.addClass('button-primary').text('Save'); // @todo l10n

            tr.find('.bookdb-reading-list-display-value').hide();
            tr.find('.bookdb-reading-list-edit-value').show();

            button.on('click', function (e) {
                e.preventDefault();

                button.attr('disabled', true);

                var entry = {
                    ID: entryID,
                    book_id: wrap.data('book-id'),
                    date_started: tr.find('.bookdb-reading-list-date-started input').val(),
                    date_finished: tr.find('.bookdb-reading-list-date-finished input').val(),
                    user_id: tr.find('.bookdb-reading-list-user-id input').val(),
                    review_id: tr.find('.bookdb-reading-list-review-id input').val(),
                    complete: tr.find('.bookdb-reading-list-complete input').val(),
                    rating: tr.find('.bookdb-reading-list-rating select').val()
                };

                var data = {
                    action: 'bdb_save_reading_entry',
                    nonce: book_database.nonce,
                    entry: entry
                };

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: "json",
                    success: function (response) {
                        button.attr('disabled', false);

                        if (response.success) {
                            tr.replaceWith(response.data);
                            Book_Database.init();
                        }

                    }
                }).fail(function (response) {
                    if (window.console && window.console.log) {
                        console.log(response);
                    }
                });
            });
        },

        /**
         * Delete Reading Entry
         *
         * @param e
         */
        deleteReadingEntry: function (e) {
            e.preventDefault();

            if (!confirm(book_database.l10n.reading_entry_remove)) {
                return false;
            }

            var button = $(this);
            var tr = button.parents('tr');

            button.attr('disabled', true);

            var data = {
                action: 'bdb_delete_reading_entry',
                nonce: book_database.nonce,
                entry_id: tr.data('entry-id')
            };

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {

                    button.attr('disabled', false);

                    if (response.success) {

                        tr.remove();

                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });
        },

        /**
         * Associate reading log with review
         *
         * This shows/hides certain fields depending on which option was selected.
         *
         * @param e
         */
        associateReadingLog: function (e) {
            var selected = $('#insert_reading_log').val();

            switch(selected) {
                case 'existing' :
                    $('#bookdb-review-existing-reading-log-fields').show();
                    $('#bookdb-review-new-reading-log-fields').hide();
                    break;

                case 'create' :
                    $('#bookdb-review-existing-reading-log-fields').hide();
                    $('#bookdb-review-new-reading-log-fields').show();
                    break;

                default :
                    $('#bookdb-review-existing-reading-log-fields').hide();
                    $('#bookdb-review-new-reading-log-fields').hide();
                    break;
            }
        }

    };

    Book_Database.init();

    /**
     * Autocomplete for Tags
     *
     * @type {{init: BookDB_Tags.init, clean: BookDB_Tags.clean, parseTags: BookDB_Tags.parseTags, quickClicks: BookDB_Tags.quickClicks, flushTags: BookDB_Tags.flushTags}}
     */
    var BookDB_Tags = {

        /**
         * Initialize
         */
        init: function () {
            var self = this,
                ajaxtag = $('.bookdb-ajaxtag'),
                wrapper = ajaxtag.parents('.bookdb-tags-wrap');

            $('.bookdb-tags-wrap').each(function () {
                BookDB_Tags.quickClicks($(this));
            });

            $('.button', ajaxtag).click(function () {
                self.flushTags($(this).closest('.bookdb-tags-wrap'));
            });

            ajaxtag.each(function () {
                var newTag = $('.bookdb-new-tag', $(this));
                var type = $(this).parents('.bookdb-tags-wrap').data('type');

                newTag.keyup(function (e) {
                    if (e.which == 13) {
                        BookDB_Tags.flushTags($(this).closest('.bookdb-tags-wrap'));
                        return false;
                    }
                }).keypress(function (e) {
                    if (13 == e.which) {
                        e.preventDefault();
                        return false;
                    }
                }).suggest(ajaxurl + '?action=bdb_suggest_tags&type=' + type);
            });

            $('#book_series_name').suggest(ajaxurl + '?action=bdb_suggest_series');

            // Save tags on save/publish.
            $('#post, #bookdb-book-page-wrapper > form').submit(function (e) {
                $('.bookdb-tags-wrap').each(function () {
                    BookDB_Tags.flushTags(this, false, 1);
                });
            });

            $(document).on('bdb_modal_before_insert_update_book', function () {
                $('.bookdb-tags-wrap').each(function () {
                    BookDB_Tags.flushTags(this, false, 1);
                });
            });
        },

        /**
         * Clean Tags
         */
        clean: function (tags) {
            return tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
        },

        /**
         * Parse Tags
         */
        parseTags: function (el) {
            var id = el.id,
                num = id.split('-check-num-')[1],
                tagbox = $(el).closest('.bookdb-tags-wrap'),
                thetags = tagbox.find('textarea'),
                current_tags = thetags.val().split(','),
                new_tags = [];

            delete current_tags[num];

            $.each(current_tags, function (key, val) {
                val = $.trim(val);
                if (val) {
                    new_tags.push(val);
                }
            });

            thetags.val(this.clean(new_tags.join(',')));

            this.quickClicks(tagbox);

            return false;
        },

        /**
         * Quick Links
         *
         * Handles adding tags.
         *
         * @param el
         */
        quickClicks: function (el) {
            var thetags = $('textarea', el),
                tagchecklist = $('.bookdb-tags-checklist', el),
                id = $(el).attr('id'),
                current_tags,
                disabled;

            if (!thetags.length)
                return;

            disabled = thetags.prop('disabled');

            current_tags = thetags.val().split(',');
            tagchecklist.empty();

            $.each(current_tags, function (key, val) {
                var span, xbutton;

                val = $.trim(val);

                if (!val)
                    return;

                // Create a new span, and ensure the text is properly escaped.
                span = $('<span />').text(val);

                // If tags editing isn't disabled, create the X button.
                if (!disabled) {
                    xbutton = $('<a id="' + id + '-check-num-' + key + '" class="ntdelbutton">X</a>');
                    xbutton.click(function () {
                        BookDB_Tags.parseTags(this);
                    });
                    span.prepend('&nbsp;').prepend(xbutton);
                }

                // Append the span to the tag list.
                tagchecklist.append(span);
            });
        },

        /**
         * Flush Tags
         *
         * Called on add tag and save.
         *
         * @param el
         * @param a
         * @param f
         */
        flushTags: function (el, a, f) {
            a = a || false;

            var text,
                tags = $('textarea', el),
                newtag = $('.bookdb-new-tag', el),
                tagsval,
                newtags;

            text = a ? $(a).text() : newtag.val();

            tagsval = tags.val();
            newtags = tagsval ? tagsval + ',' + text : text;

            newtags = this.clean(newtags);
            newtags = BookDB_Tags.uniqueArray(newtags.split(',')).join(',');

            tags.val(newtags);
            this.quickClicks(el);

            if (!a)
                newtag.val('');
            if ('undefined' == typeof(f))
                newtag.focus();

            return false;
        },

        /**
         * Unique Array, No Empty
         *
         * @param array
         * @returns {Array}
         */
        uniqueArray: function (array) {
            var out = [];

            $.each(array, function (key, val) {
                val = $.trim(val);

                if (val && $.inArray(val, out) === -1) {
                    out.push(val);
                }
            });

            return out;
        }

    };

    $(document).ready(function () {
        BookDB_Tags.init();

        $(document).on('bdb_modal_set_book_fields', function () {
            BookDB_Tags.init();
        });
    });

})(jQuery);