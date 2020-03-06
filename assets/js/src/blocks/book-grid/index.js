import edit from './edit';

const { __ } = wp.i18n;

const {
	registerBlockType,
} = wp.blocks;

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
		'reviews-only' : {
			type: 'boolean',
			default: false
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
		},
		'show-ratings' : {
			type: 'boolean',
			default: false
		},
		'show-pub-date' : {
			type: 'boolean',
			default: true
		},
		'show-goodreads-link' : {
			type: 'boolean',
			default: false
		},
		'show-purchase-links' : {
			type: 'boolean',
			default: false
		},
		'show-review-link' : {
			type: 'boolean',
			default: false
		},
	},
	edit,
	save: ( props ) => {
		return null;
	},
} );