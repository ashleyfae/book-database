const { Component } = wp.element;

const { __ } = wp.i18n;

class BookSearchResult extends Component {
	constructor( props ) {
		super( props );
	}

	render() {
		return (
			<li>
				<a
					href={ this.props.result.admin_uri }
					className="bdb-block-book-search-result"
					onClick={ ( e ) => this.handleClick( e ) }
				>
					{ this.props.result.title } { __( 'by', 'book-database' ) } { this.props.result.author_name }
				</a>
			</li>
		)
	}

	handleClick( event ) {
		event.preventDefault();

		this.props.selectHandler( this.props.result.id );
	}
}

export default BookSearchResult;
