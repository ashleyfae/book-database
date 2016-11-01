jQuery(function ($) {

    /**
     * Add Gallery Images
     */
    var bookDatabaseFrame;

    $('.bookdb-upload-image').on('click', function (event) {
        var self = $(this),
            imageField = $(this).parent().data('image'),
            imageIDField = $(this).parent().data('image-id'),
            imageSize = $(this).parent().data('image-size');

        if (!imageSize || 'undefined' === typeof imageSize) {
            imageSize = 'medium';
        }

        event.preventDefault();

        // Create the media frame.
        bookDatabaseFrame = wp.media.frames.bookDB = wp.media({
            // Set the title of the modal.
            title: self.data('choose'),
            button: {
                text: self.data('update')
            },
            states: [
                new wp.media.controller.Library({
                    title: self.data('choose'),
                    filterable: 'all',
                    multiple: false
                })
            ]
        });

        // When an image is selected, run a callback.
        bookDatabaseFrame.on('select', function () {
            var selection = bookDatabaseFrame.state().get('selection');

            selection.map(function (attachment) {
                attachment = attachment.toJSON();

                if (attachment.id) {
                    $(imageIDField).val(attachment.id);
                    var attachmentImage = attachment.sizes && attachment.sizes[imageSize] ? attachment.sizes[imageSize].url : attachment.url;

                    // Remove all image attributes.
                    if (typeof $(imageIDField).attributes != 'undefined') {
                        while ($(imageIDField).attributes.length > 0) {
                            elem.removeAttribute(elem.attributes[0].name);
                        }
                    }

                    // Update image src and alt text, then show image.
                    $(imageField).attr('src', attachmentImage).attr('alt', attachment.alt).show();

                    // Show remove button.
                    self.parent().find('.bookdb-remove-image').show();
                }
            });
        });

        // Finally, open the modal.
        bookDatabaseFrame.open();
    });

    /**
     * Remove Images
     */
    $('.bookdb-remove-image').on('click', function () {
        var self = $(this),
            imageField = $(this).parent().data('image'),
            imageIDField = $(this).parent().data('image-id');

        // Remove image attributes and hide.
        if (typeof $(imageField).attributes != 'undefined') {
            while ($(imageField).attributes.length > 0) {
                elem.removeAttribute(elem.attributes[0].name);
            }
        }

        $(imageField).hide();

        // Delete image ID value.
        $(imageIDField).val('');

        // Now hide the remove button.
        self.hide();

        return false;
    });

});