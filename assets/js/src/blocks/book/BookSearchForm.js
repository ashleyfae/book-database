const { Component } = wp.element;

const {
	Button
} = wp.components;

const { __ } = wp.i18n;

class BookSearchForm extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			search: ''
		}
	}

	render() {
		return (
			<form onSubmit={ this.props.searchSubmitHandler }>
				<input
					type="text"
					className="components-placeholder__input"
					aria-label={ __( 'Enter a book title', 'book-database' ) }
					placeholder={ __( 'Enter a book title', 'book-database' ) }
					onChange={ this.props.onChangeHandler }
					value={ this.props.value || '' }
				/>
				<Button isPrimary type="submit" disabled={ this.props.disabled }>
					{ __( 'Search', 'book-database' ) }
				</Button>
			</form>
		)
	}
}

export default BookSearchForm;
