import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PostList from './components/PostList';
import PostListByYear from './components/PostListByYear';

const SearchForm = ( { search, setSearch } ) => {
	return (
		<form>
			{ /* <label htmlFor='search'>Search:</label> */ }
			<input
				type="text"
				id="search"
				placeholder={ __( 'Search reads', 'my-reads' ) }
				value={ search }
				onChange={ ( event ) => setSearch( event.target.value ) }
			/>
		</form>
	);
};

const MyReadsFilterApp = ( { attributes } ) => {
	const { layout, order, useAmazonLink } = attributes;
	// postsArray is the array of all posts without the years as the key.
	const [ postsArray, setPostsArray ] = useState( [] );
	// postsJSON is the array of all posts with the years as the key.
	const [ postsJSON, setPostsJSON ] = useState( [] );
	const [ search, setSearch ] = useState( '' );
	const [ loading, setLoading ] = useState( true );

	// const allReadsEndpoint = '/wp-json/my-reads/v1/all-the-reads';
	const allReadsEndpoint = '/wp-content/uploads/all-the-reads.json';
	const requestOptions = {
		method: 'GET',
		headers: { 'Content-Type': 'application/json' },
	};

	useEffect( () => {
		fetch( allReadsEndpoint, requestOptions )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				setPostsJSON( data );
				const allPosts = Object.values( data ).flat();
				setPostsArray( allPosts );
				setLoading( false );
			} )
			.catch( ( error ) =>
				console.error( 'Error fetching posts:', error )
			);
	}, [] );

	return (
		<>
			<SearchForm search={ search } setSearch={ setSearch } />
			{ loading && (
				<p>
					<em>Loading posts...</em>
				</p>
			) }
			{ '' !== search ? (
				<PostList
					posts={ postsArray.filter(
						( post ) =>
							// if the post title or categories include the search term
							post.title
								.toLowerCase()
								.includes( search.toLowerCase() ) ||
							post.genres.some( ( category ) =>
								category
									.toLowerCase()
									.includes( search.toLowerCase() )
							) ||
							post.year
								.toLowerCase()
								.includes( search.toLowerCase() ) ||
							post.excerpt
								.toLowerCase()
								.includes( search.toLowerCase() ) ||
							post._my_reads_format
								.toLowerCase()
								.includes( search.toLowerCase() )
					) }
				/>
			) : (
				<>
					{ 'list' === layout && (
						<PostList
							posts={ postsArray }
							layout={ layout }
							useAmazonLink={ useAmazonLink }
						/>
					) }
					{ 'row' === layout && (
						<PostListByYear
							posts={ postsJSON }
							order={ order }
							layout={ layout }
							useAmazonLink={ useAmazonLink }
						/>
					) }
				</>
			) }
		</>
	);
};

export default MyReadsFilterApp;
