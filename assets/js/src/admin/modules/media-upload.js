/* global $, bdbVars, wp */

/**
 * Interface with the WP media modal
 */
var BDB_Media = {

	frame: false,

	/**
	 * Initialize
	 */
	init: function() {
		jQuery( '.bdb-upload-image' ).on( 'click', this.createFrame );
		jQuery( '.bdb-remove-image' ).on( 'click', this.removeImage );
	},

	/**
	 * Create and open the media frame
	 *
	 * @param e
	 */
	createFrame: function( e ) {
		e.preventDefault();

		let button = jQuery( this ),
			imageField = jQuery( this ).parent().data( 'image' ),
			imageIDField = jQuery( this ).parent().data( 'image-id' ),
			imageSize = jQuery( this ).parent().data( 'image-size' );

		if ( ! imageSize || 'undefined' === typeof imageSize ) {
			imageSize = 'medium';
		}

		// Create the media frame.
		BDB_Media.frame = wp.media.frames.bookDB = wp.media( {
			title: button.data( 'choose' ),
			button: {
				text: button.data( 'update' )
			},
			states: [
				new wp.media.controller.Library( {
					title: button.data( 'choose' ),
					filterable: 'all',
					multiple: false
				} )
			]
		} );

		// When an image is selected, run a callback.
		BDB_Media.frame.on( 'select', function() {
			let selection = BDB_Media.frame.state().get( 'selection' );

			selection.map( function( attachment ) {
				attachment = attachment.toJSON();

				if ( attachment.id ) {
					jQuery( imageIDField ).val( attachment.id );

					let attachmentImage = attachment.sizes && attachment.sizes[imageSize] ? attachment.sizes[imageSize].url : attachment.url;

					// Remove all image attributes.
					if ( 'undefined' !== typeof jQuery( imageField ).attributes ) {
						while ( jQuery( imageField ).attributes.length > 0 ) {
							elem.removeAttribute( elem.attributes[0].name );
						}
					}

					// Update image src and alt text, then show image.
					jQuery( imageField ).attr( 'src', attachmentImage ).attr( 'alt', attachment.alt ).show();

					// Show remove button.
					button.parent().find( '.bdb-remove-image' ).show();
				}
			} );
		} );

		// Finally, open the modal.
		BDB_Media.frame.open();
	},

	/**
	 * Remove the chosen image
	 *
	 * @param e
	 */
	removeImage: function ( e ) {

		e.preventDefault();

		let button = jQuery( this ),
			imageField = button.parent().data( 'image' ),
			imageIDField = button.parent().data( 'image-id' );

		// Remove image attributes and hide.
		if ( 'undefined' !== typeof jQuery( imageField ).attributes ) {
			while ( jQuery( imageField ).attributes.length > 0 ) {
				elem.removeAttribute( elem.attributes[0].name );
			}
		}

		jQuery( imageField ).hide();

		// Delete image ID value.
		jQuery( imageIDField ).val( '' );

		// Now hide the remove button.
		button.hide();

	}

};

export { BDB_Media }
