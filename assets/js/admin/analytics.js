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
                start: '',
                end: ''
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
                    element.html(val);
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

        }

    };

    jQuery(document).ready(BDB_Analytics.init);

})(window, document, jQuery);