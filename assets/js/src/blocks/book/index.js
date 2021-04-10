import BookEdit from './BookEdit';

const { RawHTML } = wp.element;

const { __ } = wp.i18n;

const {
	registerBlockType,
} = wp.blocks;

registerBlockType( 'book-database/book', {
	title: __( 'Book Information', 'book-database' ),
	icon: 'book',
	category: 'book-database',
	supports: {
		multiple: true,
		customClassName: false
	},
	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'book',
				attributes: {
					id: {
						type: 'string',
						shortcode: ( atts => {
							return atts.id || '';
						} )
					},
					rating: {
						type: 'string',
						shortcode: ( atts => {
							return atts.id || '';
						} )
					}
				}
			}
		]
	},
	attributes: {
		id: {
			type: 'string',
			default: ''
		},
		rating: {
			type: 'rating',
			default: ''
		}
	},
	edit: BookEdit,
	save: ( props ) => {
		if ( ! props.attributes.id ) {
			return null;
		}

		let shortcodeArgsString = 'id="' + props.attributes.id + '"';

		if ( props.attributes.rating ) {
			shortcodeArgsString += ' rating="' + props.attributes.rating + '"';
		}

		const shortcode = '[book ' + shortcodeArgsString + ']';

		return <RawHTML>{ shortcode }</RawHTML>;
	}
} )
