import PostItem from './PostItem';
import { __ } from '@wordpress/i18n';

const PostList = ( { posts, layout, useAmazonLink } ) => {
	if ( ! posts || posts.length === 0 ) {
		return <p>{ __( 'No reads found.', 'my-reads' ) }</p>;
	}
	return (
		<ul>
			{ posts.map( ( post ) => (
				<PostItem
					key={ post.id }
					post={ post }
					useAmazonLink={ useAmazonLink }
					layout={ layout }
				/>
			) ) }
		</ul>
	);
};

export default PostList;
