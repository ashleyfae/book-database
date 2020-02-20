import edit from './edit';

const { __ } = wp.i18n;

const {
	registerBlockType,
} = wp.blocks;

const {	RawHTML } = wp.element;

registerBlockType( 'book-database/book-grid', {
	title: __( 'Book Grid', 'gutenberg-examples' ),
	icon: 'grid-view',
	category: 'widgets',
	supports: {
		multiple: true,
		customClassName: false
	},
	attributes: {
		author: {
			type: 'string',
			default: ''
		},
		series: {
			type: 'string',
			default: ''
		},
		rating: {
			type: 'rating',
			default: ''
		},
		'pub-date-after': {
			type: 'string',
			default: ''
		},
		'pub-date-before': {
			type: 'string',
			default: ''
		},
		'read-status' : {
			type: 'string',
			default: ''
		},
		'per-page': {
			type: 'number',
			default: 20
		},
		orderby: {
			type: 'string',
			default: 'book.id',
		},
		order: {
			type: 'string',
			default: 'DESC'
		}
	},
	edit,
	save: ( props ) => {
		let shortcodeArgs = '';

		Object.keys( props.attributes ).map( ( key, index ) => {
			shortcodeArgs += ' ' + key + '="' + props.attributes[ key ] + '"';
		} );

		const shortcode = '[book-grid' + shortcodeArgs + ']';

		return <RawHTML>{ shortcode }</RawHTML>;
	},
} );