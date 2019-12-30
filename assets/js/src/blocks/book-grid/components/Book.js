const Book = ( props ) => {
	return (
		<figure>
			<img src={ props.book.cover_url } />
		</figure>
	);
};

export default Book;