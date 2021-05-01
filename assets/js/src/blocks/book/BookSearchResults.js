import BookSearchResult from "./BookSearchResult";
import BookSearchForm from "./BookSearchForm";

const { Component } = wp.element;

const {
	Placeholder
} = wp.components;

const apiFetch = wp.apiFetch;
const { __ } = wp.i18n;

class BookSearchResults extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			search: '',
			isSearching: false,
			searchResults: []
		}

		this.handleSearchSubmit = this.handleSearchSubmit.bind( this );
		this.handleSearchResultSelection = this.handleSearchResultSelection.bind( this );
	}

	render() {
		const results = this.state.searchResults.map( ( result, index ) => {
			return (
				<BookSearchResult
					key={ result.id }
					result={ result }
					selectHandler={ this.handleSearchResultSelection }
				/>
			)
		} );

		return (
			<Placeholder
				icon="book-alt"
				label={ __( 'Book Information', 'book-database' ) }
				instructions={ __(
					'Enter a book title to find the book you want to display.',
					'book-database'
				) }
			>
				<BookSearchForm
					searchSubmitHandler={ this.handleSearchSubmit }
					value={ this.state.search }
					onChangeHandler={ ( event ) => this.setState( { search: event.target.value } ) }
					disabled={ this.state.isSearching }
				/>

				<div>
					{ results.length > 0 && (
						<p>{ __( 'Select the book you want to display.', 'book-database' ) }</p>
					) }

					{ results }
				</div>
			</Placeholder>
		)
	}

	handleSearchSubmit( event ) {
		event.preventDefault();

		if ( ! this.state.search ) {
			return;
		}

		this.setState( { isSearching: true } );

		apiFetch( {
			path: '/book-database/v1/books',
			data: {
				book_query: [ {
					field: 'title',
					value: this.state.search,
					operator: 'LIKE'
				} ]
			},
			method: 'POST'
		} ).then( ( response ) => {
			this.setState( { searchResults: response } );
		} ).catch( ( error ) => {
			console.log( 'Search error', error );
		} ).finally( () => {
			this.setState( { isSearching: false } );
		} )
	}

	handleSearchResultSelection( bookId ) {
		this.setState( {
			searchResults: []
		} );

		this.props.selectHandler( bookId );
	}
}

export default BookSearchResults;
