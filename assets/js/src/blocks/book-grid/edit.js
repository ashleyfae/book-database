/* globals bdbBlocks */

/** Components */
import Book from './components/Book';

const {	Component, Fragment, RawHTML } = wp.element;

const {
	PanelBody,
	Placeholder,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
	Spinner,
} = wp.components;

const { __ } = wp.i18n;

const {
	BlockControls,
	InspectorControls,
} = wp.blockEditor;

const apiFetch = wp.apiFetch;

class BookGridEdit extends Component {

	constructor() {
		super( ...arguments );

		this.state = {
			isMounted: false,
			isLoading: true,
			books: []
		};
	}

	componentDidMount() {
		this.state.isMounted = true;
		this.fetchBooks();
	}

	componentDidUpdate( prevProps ) {
		const { ids, author, series, rating, "pub-date-after" : pubDateAfter, "pub-date-before" : pubDateBefore, "read-status" : readStatus, "reviews-only" : reviewsOnly, orderby, order, "cover-size" : coverSize, "per-page": perPage } = this.props.attributes;
		const { alignWide } = wp.data.select( "core/editor" ).getEditorSettings();

		const prevProp = prevProps.attributes;

		if (
			ids !== prevProp.ids ||
			author !== prevProp.author ||
			series !== prevProp.series ||
			rating !== prevProp.rating ||
			pubDateAfter !== prevProp['pub-date-after'] ||
			pubDateBefore !== prevProp['pub-date-before'] ||
			readStatus !== prevProp['read-status'] ||
			reviewsOnly !== prevProp.reviewsOnly ||
			orderby !== prevProp.orderby ||
			order !== prevProp.order ||
			coverSize !== prevProp.coverSize ||
			perPage !== prevProp['per-page']
		) {
			// Fetch new array of books when various controls are updated and store them in state.
			this.fetchBooks();
		}

		// Clear "align" attribute if theme does not support wide images.
		// This prevents the attribute from being "stuck" on a particular setting if the theme is switched.
		if ( ! alignWide ) {
			//this.props.setAttributes( { align: undefined } );
		}
	}

	componentWillUnmount() {
		// Delete fetch requests.
		this.state.isMounted = false;
		delete this.booksRequest;
	}

	getRatingOptions() {
		return bdbBlocks.ratings;
	}

	getOrderOptions() {
		return [
			{ value: 'ASC', label: __( 'Ascending', 'book-database' ) },
			{ value: 'DESC', label: __( 'Descending', 'book-database' ) },
		];
	}

	getOrderByOptions() {
		return [
			{ value: 'author.name', label: __( 'Author Name', 'book-database' ) },
			{ value: 'book.id', label: __( 'Book ID', 'book-database' ) },
			{ value: 'book.title', label: __( 'Book Title', 'book-database' ) },
			{ value: 'book.index_title', label: __( 'Book Index Title', 'book-database' ) },
			{ value: 'book.series_position', label: __( 'Series Position', 'book-database' ) },
			{ value: 'book.pub_date', label: __( 'Publication Date', 'book-database' ) },
			{ value: 'book.pages', label: __( 'Number of Pages', 'book-database' ) },
			{ value: 'book.date_created', label: __( 'Date Created', 'book-database' ) },
			{ value: 'series.name', label: __( 'Series Name', 'book-database' ) }
		]
	}

	getReadStatusOptions() {
		return [
			{ value: '', label: __( 'All', 'book-database' ) },
			{ value: 'reading', label: __( 'Currently Reading', 'book-database' ) },
			{ value: 'read', label: __( 'Read', 'book-database' ) },
			{ value: 'unread', label: __( 'Unread', 'book-database' ) }
		]
	}

	fetchBooks() {
		const attributes = this.props.attributes;

		const request = apiFetch( {
			path: '/book-database/v1/book/grid',
			data: attributes,
			method: 'POST'
		} );

		request.then( ( books ) => {
			if ( this.booksRequest !== request ) {
				return;
			}
			console.log( 'Books', books );

			this.setState( { books, isLoading: false } );
		} ).catch( ( error ) => {
			console.log( 'Caught error', error );
		} );

		this.booksRequest = request;

	}

	renderBooks() {
		const books = this.state.books.grid;

		return (
			<RawHTML>{ books }</RawHTML>
		);
	}

	render() {
		const {
			attributes,
			setAttributes,
		} = this.props;

		const {
			author,
			series,
			rating,
			'pub-date-after' : pubDateAfter,
			'pub-date-before' : pubDateBefore,
			'read-status' : readStatus,
			'per-page' : perPage,
			orderby,
			order
		} = attributes;

		const { isLoading, books } = this.state;
		const loadingLabel = __( 'Loading books', 'book-database' );

		if ( isLoading ) {
			return (
				<Fragment>
					<Placeholder
						icon="book-alt"
						label={ loadingLabel }
					>
						<Spinner />
					</Placeholder>
				</Fragment>
			);
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Filters', 'book-database' ) }>
					<TextControl
						label={ __( 'Author', 'book-database' ) }
						value={ author }
						onChange={ (author) => setAttributes( { author } ) }
					/>
					<TextControl
						label={ __( 'Series', 'book-database' ) }
						value={ series }
						onChange={ (series) => setAttributes( { series } ) }
					/>
					<SelectControl
						label={ __( 'Rating', 'book-database' ) }
						value={ rating }
						options={ this.getRatingOptions() }
						onChange={ (rating) => setAttributes( { rating } ) }
					/>
					<TextControl
						label={ __( 'Publication Date After', 'book-database' ) }
						value={ pubDateAfter }
						onChange={ (pubDateAfter) => setAttributes( { 'pub-date-after' : pubDateAfter } ) }
					/>
					<TextControl
						label={ __( 'Publication Date Before', 'book-database' ) }
						value={ pubDateBefore }
						onChange={ (pubDateBefore) => setAttributes( { 'pub-date-before' : pubDateBefore } ) }
					/>
					<SelectControl
						label={ __( 'Read Status', 'book-database' ) }
						value={ readStatus }
						options={ this.getReadStatusOptions() }
						onChange={ (readStatus) => setAttributes( { 'read-status' : readStatus } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Limits & Ordering', 'book-database' ) }>
					<RangeControl
						label={ __( 'Books Per Page', 'book-database' ) }
						value={ perPage }
						onChange={ (perPage) => setAttributes( { 'per-page' : perPage } ) }
						min={ 1 }
						max={ 100 }
					/>

					<SelectControl
						label={ __( 'Order By', 'book-database' ) }
						value={ orderby }
						options={ this.getOrderByOptions() }
						onChange={ (orderby) => setAttributes( { orderby } ) }
					/>

					<SelectControl
						label={ __( 'Order', 'book-database' ) }
						value={ order }
						options={ this.getOrderOptions() }
						onChange={ ( order ) => setAttributes( { order } ) }
					/>
				</PanelBody>
			</InspectorControls>
		);

		const hasBooks = 'undefined' !== typeof books.grid && books.grid.length;

		if ( ! hasBooks ) {
			return (
				<Fragment>
					{ inspectorControls }
					<Placeholder
						icon="book-alt"
						label={ __( 'Loading books', 'book-database' ) }
					>
						{ ! books.grid.length ?
							<Spinner /> :
							__( 'No books found.', 'book-database' )
						}
					</Placeholder>
				</Fragment>
			);
		}

		return (
			<Fragment>
				{ inspectorControls }
				<div className={ this.props.className }>
					{ this.renderBooks() }
				</div>
			</Fragment>
		);

	}

}

export default BookGridEdit;