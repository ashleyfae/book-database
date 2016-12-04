(function (window, document, $) {

    var BDB_Analytics = {

        /**
         * Initialize
         */
        init: function () {
            BDB_Analytics.getStats();
        },

        /**
         * Set Date Range Values
         *
         * @param e
         */
        setRanges: function (e) {

            if ('custom' == $(this).val()) {

                $('#bookdb-start').val('').attr('type', 'date');
                $('#bookdb-end').val('').attr('type', 'date');

            } else {

                var selected = $(this).find('option:selected');
                $('#bookdb-start').val(selected.data('start')).attr('type', 'hidden');
                $('#bookdb-end').val(selected.data('end')).attr('type', 'hidden');

            }

        },

        /**
         * Get Stats
         */
        getStats: function () {

            var loading = '<div id="circleG"><div id="circleG_1" class="circleG"></div><div id="circleG_2" class="circleG"></div><div id="circleG_3" class="circleG"></div></div>';

            $('.bookdb-result').empty();
            $('.bookdb-loading').html(loading).show();

            var data = {
                action: 'bdb_analytics_batch_1',
                start: $('#bookdb-start').val(),
                end: $('#bookdb-end').val()
            };

            $.post(window.ajaxurl, data, BDB_Analytics.firstBatchResponse).then(function () {

                data.action = 'bdb_analytics_batch_2';
                $.post(window.ajaxurl, data, BDB_Analytics.secondBatchResponse);

            }).fail(function () {

                console.warn("ajax error");

            });

        },

        /**
         * Handle First Batch Response
         *
         * Includes:
         *      + Number of reviews
         *      + Pages read
         *      + Average rating
         *      + Rating breakdown
         *      + List of reviews written
         *      + List of books read but not reviewed
         *
         * These are super quick to retrieve so we do them first.
         *
         * @param response
         */
        firstBatchResponse: function (response) {

            console.log(response);

            if (true == response.success) {
                $.each(response.data, function (id, val) {
                    var element = $('#' + id);

                    element.empty();

                    // Book list
                    if ('book-list' == id) {

                        element.html('<table><thead><tr><th>' + bookdb_analytics.l10n.rating + '</th><th>' + bookdb_analytics.l10n.book + '</th><th>' + bookdb_analytics.l10n.date + '</th></tr></thead><tbody></tbody></table>');
                        var list = element.find('tbody');

                        $.each(val, function (review_key, review_val) {
                            list.append('<tr><td><a href="' + review_val.edit_review_link + '" class="book-rating ' + review_val.rating_class + '" title="' + bookdb_analytics.l10n.edit_review + '">' + review_val.rating + '</a></td><td><a href="' + review_val.edit_book_link + '" title="' + bookdb_analytics.l10n.edit_book + '">' + review_val.book + '</a></td><td class="review-date">[' + review_val.date + ']</td></tr>');
                        });

                    } else if ('read-not-reviewed' == id) {

                        element.html('<table><thead><tr><th>' + bookdb_analytics.l10n.rating + '</th><th>' + bookdb_analytics.l10n.book + '</th><th>' + bookdb_analytics.l10n.date + '</th></tr></thead><tbody></tbody></table>');
                        var list = element.find('tbody');

                        $.each(val, function (book_key, book_val) {
                            list.append('<tr><td class="book-rating ' + book_val.rating_class + '">' + book_val.rating + '</td><td><a href="' + book_val.edit_book_link + '" title="' + bookdb_analytics.l10n.edit_book + '">' + book_val.book + '</a></td><td class="review-date">[' + book_val.date + ']</td></tr>');
                        });

                    } else if ('rating-breakdown' == id) {

                        // Rating breakdown.
                        element.html('<table><thead><tr><th>' + bookdb_analytics.l10n.rating + '</th><th>' + bookdb_analytics.l10n.number_books + '</th></tr></thead><tbody></tbody></table>');
                        var table = element.find('tbody');

                        $.each(val, function (key, rating) {
                            table.append('<tr><td>' + rating.rating + '</td><td>' + rating.count + '</td></tr>');
                        });

                    } else {

                        element.html(val);

                    }

                    element.parents('.bookdb-metric-inner').find('.bookdb-loading').empty().hide();
                });
            }

        },

        /**
         * Handle Second Batch Response
         *
         * For taxonomies.
         *
         * Includes:
         *      + Genre breakdown
         *      + Publishers breakdown
         *      + Source breakdown
         *
         * These operations are a tad more intensive so they'll take longer to load.
         *
         * @param response
         */
        secondBatchResponse: function (response) {

            if (response.data.length == 0) {

                $('.bookdb-term-breakdown').each(function() {
                    $(this).find('.bookdb-loading').empty().hide();
                    $(this).find('.bookdb-result').empty().append('&ndash;');
                });

            }

            $.each(response.data, function (type, html) {
                var wrap = $('#' + type + '-breakdown');

                if (!wrap.length) {
                    return true;
                }

                // Stop loader.
                wrap.parents('.bookdb-metric-inner').find('.bookdb-loading').empty().hide();

                wrap.empty().html('<table><thead><tr><th>' + bookdb_analytics.l10n.name + '</th><th>' + bookdb_analytics.l10n.number_reviews + '</th><th>' + bookdb_analytics.l10n.average_rating + '</th></tr></thead><tbody>' + html + '</tbody></table>')
            });

        }

    };

    jQuery(document).ready(BDB_Analytics.init);

    // Set ranges.
    $('#bookdb-range').on('change', BDB_Analytics.setRanges);

    // Update results.
    $('#bookdb-date-range').on('click', 'button', function (e) {
        e.preventDefault();

        BDB_Analytics.getStats();
    });

})(window, document, jQuery);