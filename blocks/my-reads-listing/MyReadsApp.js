import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PostList from './components/PostList';
import PostListByYear from './components/PostListByYear';
import GenreButtons from './components/GenreButtons';
import SearchForm from './components/SearchForm';

const MyReadsFilterApp = ( { attributes } ) => {
	const { layout, order, useAmazonLink, myReadsUploadsDir } = attributes;
	// postsArray is the array of all posts without the years as the key.
	const [ postsArray, setPostsArray ] = useState( [] );
	// postsJSON is the array of all posts with the years as the key.
	const [ postsJSON, setPostsJSON ] = useState( [] );
	const [ search, setSearch ] = useState( '' );
	const [ loading, setLoading ] = useState( true );
	const [ genreFilter, setGenreFilter ] = useState( 'All' );
	const allReadsEndpoint = myReadsUploadsDir + 'all-the-reads.json';
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

	const getFilteredPosts = () => {
		return postsArray.filter(
			( post ) =>
				// if the post title or categories include the search term
				post.title.toLowerCase().includes( search.toLowerCase() ) ||
				post.genres.some( ( category ) =>
					category.toLowerCase().includes( search.toLowerCase() )
				) ||
				post.year.toLowerCase().includes( search.toLowerCase() ) ||
				post.excerpt.toLowerCase().includes( search.toLowerCase() ) ||
				post._myreads_format
					.toLowerCase()
					.includes( search.toLowerCase() ) ||
				( post._myreads_isFavorite && 'Favorites' === genreFilter )
		);
	};

	const getBaseFilteredPosts = () => {
		return Object.values( postsJSON ).flat();
	};

	// This callback will update your filtering logic based on the active genre.
	const handleFilterChange = ( selectedGenre ) => {
		setGenreFilter( selectedGenre );
		// You can adjust your filtering here (for instance, if 'Favorites', filter posts by favorites,
		// or if a specific genre, filter posts whose `genres` include that genre)
		if ( selectedGenre === 'All' ) {
			setPostsArray( getBaseFilteredPosts() ); // Reset to all posts
		} else if ( selectedGenre === 'Favorites' ) {
			setPostsArray(
				getBaseFilteredPosts().filter(
					( post ) => post._myreads_isFavorite
				)
			);
		} else {
			setPostsArray(
				getBaseFilteredPosts().filter( ( post ) =>
					post.genres.includes( selectedGenre )
				)
			);
		}
	};

	return (
		<>
			<GenreButtons
				posts={ getBaseFilteredPosts() }
				onFilterChange={ handleFilterChange }
			/>
			<SearchForm
				search={ search }
				setSearch={ setSearch }
				postsArray={ postsArray }
			/>
			{ search && (
				<p>
					{ __(
						`Showing ${
							getFilteredPosts().length
						} results for "${ search }"`,
						'my-reads'
					) }
				</p>
			) }
			{ loading && (
				<p>
					<em>Loading posts...</em>
				</p>
			) }
			{ '' !== search || 'All' !== genreFilter ? (
				<PostList posts={ getFilteredPosts() } />
			) : (
				<>
					{ 'list' === layout && (
						<PostList
							posts={ getFilteredPosts() }
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
