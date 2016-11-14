(function (window, document, $, undefined) {

    var BDB_Analytics = {

        /**
         * Initialize
         */
        init: function () {
            // @todo set date range
            BDB_Analytics.getStats();
        },

        /**
         * Get Stats
         */
        getStats: function () {

            var loading = '<div id="circleG"><div id="circleG_1" class="circleG"></div><div id="circleG_2" class="circleG"></div><div id="circleG_3" class="circleG"></div></div>';

            $('.bookdb-loading').html(loading).show();

            var data = {
                action: 'bdb_analytics_batch_1',
                start: 'January 1st 2015',
                end: 'December 31st 2015'
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
         *
         * These are super quick to retrieve so we do them first.
         *
         * @param response
         */
        firstBatchResponse: function (response) {

            console.log(response);

            if (true != !response.success) {
                $.each(response.data, function (id, val) {
                    var element = $('#' + id);

                    element.empty();

                    // Book list
                    if ('book-list' == id) {

                        /*element.html('<ul></ul>');
                        var list = element.find('ul');

                        $.each(val, function (review_key, review_val) {
                            list.append('<li><a href="' + review_val.edit_review_link + '" class="book-rating ' + review_val.rating_class + '" title="Edit Review">' + review_val.rating + '</a> <a href="' + review_val.edit_book_link + '" title="Edit Book">' + review_val.book + '</a> <span class="review-date">[' + review_val.date + ']</span></li>');
                        });*/

                        element.html('<table><thead><tr><th>Rating</th><th>Book</th><th>Date</th></tr></thead><tbody></tbody></table>');
                        var list = element.find('tbody');

                        $.each(val, function (review_key, review_val) {
                            list.append('<tr><td><a href="' + review_val.edit_review_link + '" class="book-rating ' + review_val.rating_class + '" title="Edit Review">' + review_val.rating + '</a></td><td><a href="' + review_val.edit_book_link + '" title="Edit Book">' + review_val.book + '</a></td><td class="review-date">[' + review_val.date + ']</td></tr>');
                        });

                    } else if ('rating-breakdown' == id) {

                        // Rating breakdown.
                        element.html('<table><thead><tr><th>Rating</th><th>Number of Books</th></tr></thead><tbody></tbody></table>');
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
         * Includes:
         *      + Rating breakdown
         *      + Genre breakdown
         *      + Publishers breakdown
         *
         * These operations are a tad more intensive so they'll take longer to load.
         *
         * @param response
         */
        secondBatchResponse: function (response) {

            $.each(response.data, function (type, html) {
                var wrap = $('#' + type + '-breakdown');

                if (!wrap.length) {
                    return true;
                }

                // Stop loader.
                wrap.parents('.bookdb-metric-inner').find('.bookdb-loading').empty().hide();

                wrap.empty().html('<table><thead><tr><th>Name</th><th>Number of Reviews</th><th>Average Rating</th></tr></thead><tbody>' + html + '</tbody></table>')
            });

        }

    };

    jQuery(document).ready(BDB_Analytics.init);

})(window, document, jQuery);