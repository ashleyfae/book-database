/**
 * TinyMCE Shortcode Preview
 *
 * Taken from WP Recipe Maker.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

(function ($) {

    tinymce.PluginManager.add('bookdatabase', function (editor, url) {

        /**
         * Replace Shortcodes
         *
         * @param content
         * @returns {*}
         */
        function replaceShortcodes(content) {
            return content.replace(/\[book ([^\]]*)\]/g, function (match) {
                return html(match);
            });
        }

        /**
         * HTML
         *
         * @param data
         * @returns {string}
         */
        function html(data) {
            var id = data.match(/id="?'?(\d+)/i);
            data = window.encodeURIComponent(data);

            var ajax_data = {
                action: 'bdb_shortcode_preview',
                nonce: bookdb_modal.nonce,
                book_id: id[1]
            };

            $.post(ajaxurl, ajax_data, function (response) {
                var content = jQuery(editor.iframeElement).contents().find('#tinymce').html();
                content = content.replace('>Loading Book #' + id[1] + '<', '>' + response.data + '<');
                editor.setContent(content);
            });

            // @todo: add in max width, possible float, etc.
            return '<div class="bookdb-shortcode" style="display: block; cursor: pointer; margin: 5px; padding: 10px; border: 1px solid #999;" contentEditable="false" ' +
                'data-bookdb-book="' + id[1] + '" data-bookdb-shortcode="' + data + '" data-mce-resize="false" data-mce-placeholder="1">Loading Book #' + id[1] + '</div>';
        }

        /**
         * Restore Shortcodes
         * 
         * @param content
         * @returns {*}
         */
        function restoreShortcodes(content) {
            function getAttr(str, name) {
                name = new RegExp(name + '=\"([^\"]+)\"').exec(str);
                return name ? window.decodeURIComponent(name[1]) : '';
            }

            content = content.replace(/<p><span class="bookdb-(?=(.*?span>))\1\s*<\/p>/g, '');
            content = content.replace(/<span class="bookdb-.*?span>/g, '');

            return content.replace(/(?:<p(?: [^>]+)?>)*(<div [^>]+>.*?<\/div>)(?:<\/p>)*/g, function (match, div) {
                var data = getAttr(div, 'data-bookdb-shortcode');

                if (data) {
                    return '<p>' + data + '</p>';
                }

                return match;
            });
        }

        editor.on('mouseup', function(event) {
            var dom = editor.dom,
                node = event.target,
                shortcode = jQuery(node).hasClass('bookdb-shortcode') ? jQuery(node) : jQuery(node).parents('.bookdb-shortcode');

            if (event.button !== 2 && shortcode.length > 0) {
                if (dom.getAttrib(node, 'data-bookdb-book-remove')) {
                    if (confirm(bookdb_modal.l10n.shortcode_remove)) {
                        editor.dom.remove(node.parentNode);
                    }
                } else {
                    var id = jQuery(shortcode).data('bookdb-book');
                    BookDB_Modal_Admin.openModal(editor.id, {
                        bookID: id
                    });
                }
            }
        });

        editor.on('BeforeSetContent', function(event) {
            //console.log(event.content);
            event.content = event.content.replace(/(<p>)?\s*<span class="bookdb-placeholder" data-mce-contenteditable="false">&nbsp;<\/span>\s*(<\/p>)?/gi,'');
            event.content = event.content.replace(/^(\s*<p>)(\s*\[book)/, '$1 $2');
            event.content = replaceShortcodes(event.content);
        });

        editor.on('PostProcess', function(event) {
            if (event.get) {
                event.content = restoreShortcodes(event.content);
            }
        });

    });

})(jQuery);