import { __ } from '@wordpress/i18n';

const SearchForm = ( { search, setSearch, postsArray } ) => {
	return (
		<form>
			{ /* <label htmlFor='search'>Search:</label> */ }
			<input
				type="text"
				id="search"
				placeholder={ __(
					`Search all ${ postsArray.length } reads...`,
					'my-reads'
				) }
				value={ search }
				onChange={ ( event ) => setSearch( event.target.value ) }
			/>
		</form>
	);
};

export default SearchForm;
