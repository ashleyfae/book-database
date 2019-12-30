/** Components */
import Book from './components/Book';

const {	Component, Fragment } = wp.element;

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
		const { ids, author, series, rating, pubDateAfter, pubDateBefore, readStatus, reviewsOnly, orderby, order, coverSize, "per-page": perPage } = this.props.attributes;
		const { alignWide } = wp.data.select( "core/editor" ).getEditorSettings();

		const prevProp = prevProps.attributes;

		if (
			ids !== prevProp.ids ||
			author !== prevProp.author ||
			series !== prevProp.series ||
			rating !== prevProp.rating ||
			pubDateAfter !== prevProp.pubDateAfter ||
			pubDateBefore !== prevProp.pubDateBefore ||
			readStatus !== prevProp.readStatus ||
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

	fetchBooks() {
		const attributes = this.props.attributes;

		let queryArgs = {
			orderby: attributes.orderby,
			order: attributes.order,
			number: attributes['per-page']
		};

		queryArgs.number = queryArgs['per-page'];

		if ( attributes.author.length ) {
			queryArgs.author_query = [
				{
					field: 'name',
					value: attributes.author
				}
			]
		}

		if ( attributes.series.length ) {
			queryArgs.series_query = [
				{
					field: 'name',
					value: attributes.series
				}
			]
		}

		// @todo pub-date-before and pub-date-after

		const request = apiFetch( {
			path: '/book-database/v1/books',
			data: queryArgs,
			method: 'POST'
		} );

		request.then( ( books ) => {
			if ( this.booksRequest !== request || ! this.state.isMounted ) {
				return;
			}

			this.setState( { books, isLoading: false } );
		} ).catch( ( error ) => {
			console.log( 'Caught error', error );
		} );

		this.booksRequest = request;

	}

	renderBooks() {
		const books = this.state.books;
		const attributes = this.props;
		const classNames = 'book-database-grid';

		return (
			<div className={ classNames }>
				{ books.map( ( book ) => <Book book={book} key={book.id.toString()} attributes={attributes} /> ) }
			</div>
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
			'pub-date-after' : pubDateAfter,
			'pub-date-before' : pubDateBefore,
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

		const hasBooks = Array.isArray( books ) && books.length;

		if ( ! hasBooks ) {
			return (
				<Fragment>
					{ inspectorControls }
					<Placeholder
						icon="book-alt"
						label={ __( 'Loading books', 'book-database' ) }
					>
						{ ! Array.isArray( books ) ?
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