/* globals bdbBlocks */

const {	Component, Fragment, RawHTML } = wp.element;

const {
	CheckboxControl,
	PanelBody,
	Placeholder,
	TextControl,
	RangeControl,
	SelectControl,
	Spinner,
} = wp.components;

const { __ } = wp.i18n;

const {
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
		const {
			ids, author, series, rating, "pub-date-after" : pubDateAfter, "pub-date-before" : pubDateBefore,
			"read-status" : readStatus, "reviews-only" : reviewsOnly, orderby, order, "cover-size" : coverSize,
			"per-page": perPage, 'show-ratings' : showRatings
		} = this.props.attributes;

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
			perPage !== prevProp['per-page'] ||
			showRatings !== prevProp['show-ratings']
		) {
			// Fetch new array of books when various controls are updated and store them in state.
			this.fetchBooks();
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
			'reviews-only' : reviewsOnly,
			'per-page' : perPage,
			orderby,
			order,
			'show-ratings' : showRatings,
			'show-pub-date' : showPubDate,
			'show-goodreads-link' : showGoodreadsLink,
			'show-purchase-links' : showPurchaseLinks,
			'show-review-link' : showReviewLink
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
					<CheckboxControl
						label={ __( 'Only include reviewed books', 'book-database' ) }
						checked={ reviewsOnly }
						onChange={ (reviewsOnly) => setAttributes( { 'reviews-only' : reviewsOnly } ) }
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
				<PanelBody title={ __( 'Display', 'book-database' ) }>
					<CheckboxControl
						label={ __( 'Show ratings', 'book-database' ) }
						checked={ showRatings }
						onChange={ (showRatings) => setAttributes( { 'show-ratings' : showRatings } ) }
					/>
					<CheckboxControl
						label={ __( 'Show publication dates', 'book-database' ) }
						checked={ showPubDate }
						onChange={ (showPubDate) => setAttributes( { 'show-pub-date' : showPubDate } ) }
					/>
					<CheckboxControl
						label={ __( 'Show goodreads links', 'book-database' ) }
						checked={ showGoodreadsLink }
						onChange={ (showGoodreadsLink) => setAttributes( { 'show-goodreads-link' : showGoodreadsLink } ) }
					/>
					<CheckboxControl
						label={ __( 'Show purchase links', 'book-database' ) }
						checked={ showPurchaseLinks }
						onChange={ (showPurchaseLinks) => setAttributes( { 'show-purchase-links' : showPurchaseLinks } ) }
					/>
					<CheckboxControl
						label={ __( 'Show review links', 'book-database' ) }
						checked={ showReviewLink }
						onChange={ (showReviewLink) => setAttributes( { 'show-review-link' : showReviewLink } ) }
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