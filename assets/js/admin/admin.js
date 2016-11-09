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

            $('.bookdb-book-option-toggle').click(this.toggleBookTextarea);
            $('#bookdb-book-layout-cover-changer').change(this.changeCoverAlignment);
            $('.bookdb-new-checkbox-term').on('click', '.button', this.addCheckboxTerm);
            $('.bookdb-new-checkbox-term .bookdb-new-checkbox-term-value').keypress(this.addCheckboxTerm);
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
                wrapper = ajaxtag.parents('.bookdb-tags-wrap'),
                type = wrapper.data('type');

            $('.bookdb-tags-wrap').each(function () {
                BookDB_Tags.quickClicks(wrapper);
            });

            $('.button', ajaxtag).click(function () {
                self.flushTags($(this).closest('.bookdb-tags-wrap'));
            });

            $('.bookdb-new-tag', ajaxtag).keyup(function (e) {
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

            // Save tags on save/publish.
            $('#post, #bookdb-book-page-wrapper > form').submit(function (e) {
                //e.preventDefault();
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
    });

})(jQuery);