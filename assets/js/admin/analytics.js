(function (window, document, $, undefined) {

    var BDB_Analytics = {

        init: function () {
            // @todo set date range
            BDB_Analytics.getStats();
        },

        getStats: function () {

            var loading = '<div id="circleG"><div id="circleG_1" class="circleG"></div><div id="circleG_2" class="circleG"></div><div id="circleG_3" class="circleG"></div></div>';

            $('.bookdb-loading').html(loading);

        }

    };

    jQuery(document).ready(BDB_Analytics.init);

})(window, document, jQuery);