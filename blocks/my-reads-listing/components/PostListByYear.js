import { __ } from '@wordpress/i18n';
import PostItem from './PostItem';

const PostListByYear = ( { posts, order, layout, useAmazonLink } ) => {
	// Return early if there are no posts
	if ( ! posts || posts.length === 0 ) {
		return <p>{ __( 'No reads found.', 'my-reads' ) }</p>;
	}

	const yearsArr =
		'reverse' === order
			? Object.keys( posts ).sort( ( a, b ) => b - a )
			: Object.keys( posts );

	return (
		<div className="postListByYear-wrapper">
			{ yearsArr.map( ( year ) => {
				return (
					<div>
						<div className="year-section">
							<h2>{ year }</h2>
							<p className="year-total">
								{ posts[ year ].length } reads for the year
							</p>
						</div>
						<div className="postListByYear-grid">
							{ posts[ year ].map( ( post ) => (
								<PostItem
									key={ post.id }
									post={ post }
									useAmazonLink={ useAmazonLink }
									layout={ layout }
								/>
							) ) }
						</div>
					</div>
				);
			} ) }
		</div>
	);
};

export default PostListByYear;
