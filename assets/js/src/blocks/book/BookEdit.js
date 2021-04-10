/* globals bdbBlocks */

import BookSearchResults from "./BookSearchResults";

const { Component, Fragment, RawHTML } = wp.element;

const {
	Placeholder,
	SelectControl,
	Spinner
} = wp.components;

const {
	InspectorControls,
} = wp.blockEditor;

const apiFetch = wp.apiFetch;
const { __ } = wp.i18n;

class BookEdit extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			isMounted: false,
			bookLayout: false
		}

		this.handleSearchResultSelection = this.handleSearchResultSelection.bind( this );
	}

	componentDidMount() {
		this.state.isMounted = true;
		this.maybeLoadBook();
	}

	componentWillUnmount() {
		this.state.isMounted = false;
	}

	componentDidUpdate( prevProps ) {
		const { id, rating } = this.props.attributes;

		if ( id !== prevProps.attributes.id || rating !== prevProps.attributes.rating ) {
			this.maybeLoadBook();
		}
	}

	render() {
		const {
			attributes
		} = this.props;

		const {
			id
		} = attributes;

		if ( id ) {
			// We have a book to either fetch or render.
			return this.renderBook();
		} else {
			return <BookSearchResults selectHandler={ this.handleSearchResultSelection } />
			// Still need to pick for a book.
		}
	}

	maybeLoadBook() {
		const attributes = this.props.attributes;

		if ( ! attributes.id ) {
			return;
		}

		let path = '/book-database/v1/book/' + attributes.id;
		if ( attributes.rating ) {
			path += '?rating=' + attributes.rating;
		}

		apiFetch( {
			path,
			method: 'GET'
		} ).then( ( response ) => {
			this.setState( {
				bookLayout: response
			} );
		} ).catch( ( error ) => {
			console.log( 'Error loading book', error );
		} );
	}

	renderBook() {
		if ( this.state.bookLayout ) {
			return (
				<Fragment>
					{ this.inspectorControls() }
					<RawHTML>{ this.state.bookLayout }</RawHTML>
				</Fragment>
			);
		} else {
			return (
				<Fragment>
					<Placeholder
						icon="book-alt"
						label={ __( 'Book Information', 'book-database' ) }
						instructions={ __( 'Loading book...', 'book-database' ) }
					>
						<Spinner />
					</Placeholder>
				</Fragment>
			)
		}
	}

	inspectorControls() {
		const {
			attributes,
			setAttributes
		} = this.props;

		const { rating } = attributes;
		const ratingOptions = bdbBlocks.ratings;

		// Change "All" to "None"
		ratingOptions[0].label = __( 'None', 'book-database' );

		return (
			<InspectorControls>
				<fieldset>
					<SelectControl
						label={ __( 'Rating', 'book-database' ) }
						value={ rating }
						options={ ratingOptions }
						onChange={ (rating) => setAttributes( { rating } ) }
					/>
				</fieldset>
			</InspectorControls>
		)
	}

	handleSearchResultSelection( bookId ) {
		this.props.setAttributes( { id: bookId } );
	}
}

export default BookEdit;
