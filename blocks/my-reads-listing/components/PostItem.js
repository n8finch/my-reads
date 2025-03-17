import { getRatingEmojis, getFormatAndEmoji } from '../../../includes/js/utils';
import { decodeEntities } from '@wordpress/html-entities';
import AudioBookImg from '../../../includes/images/headphones.webp';
import ArticleImg from '../../../includes/images/article.webp';
import BookImg from '../../../includes/images/book.webp';
import { chevronRight } from '@wordpress/icons';
import { Icon } from '@wordpress/icons';

const PostItem = ( { post, layout, useAmazonLink } ) => {
	if ( ! post ) {
		return null;
	}

	// Set some defaults for the featured image.
	if ( ! post.featured_image ) {
		// Add default image if none is set based on post._my_reads_format
		switch ( post._my_reads_format ) {
			case 'audiobook':
				post.featured_image = AudioBookImg;
				break;
			case 'comicbook':
				post.featured_image = ArticleImg;
				break;
			case 'article':
				post.featured_image = ArticleImg;
				break;
			case 'book':
				post.featured_image = BookImg;
				break;
			default:
				post.featured_image = BookImg;
		}
	}

	return (
		<div className={ `postItem-wrapper ${ layout }` }>
			<div className="image-wrapper">
				<a
					href={
						useAmazonLink
							? post._my_reads_amazonLink
							: post.permalink
					}
				>
					{ post.featured_image && (
						<img src={ post.featured_image } alt={ post.title } />
					) }
				</a>
				{ post?._my_reads_isFavorite && (
					<span className="favorite-badge">❤️</span>
				) }
			</div>
			<div className="content-wrapper">
				<a
					href={
						useAmazonLink
							? post._my_reads_amazonLink
							: post.permalink
					}
				>
					<h3>{ decodeEntities( post.title ) }</h3>
				</a>
				<small>
					Rating:{ ' ' }
					{ getRatingEmojis(
						post._my_reads_rating,
						post._my_reads_ratingStyle
					) }{ ' ' }
					<br />
					Format: { getFormatAndEmoji( post._my_reads_format ) }
					<br />
					Categories:{ ' ' }
					<em>
						{ post.genres.map( ( category, index ) => {
							return (
								<>
									{ category }
									{ index < post.genres.length - 1 && ', ' }
								</>
							);
						} ) }
					</em>
				</small><br/>
				{ post.excerpt && (
					<>
						{ layout === 'list' && (
							<p
								className="excerpt"
								dangerouslySetInnerHTML={ {
									__html:
										post.excerpt.slice( 0, 100 ) + '...',
								} }
							></p>
						) }
						{ layout === 'row' && (
							<div className="excerpt-container">
								<a href="#" className="excerpt-link">
									<Icon icon={ chevronRight } /> Quick thought
								</a>
								<div className="excerpt-popup">
									<p
										dangerouslySetInnerHTML={ {
											__html:
												post.excerpt.slice( 0, 100 ) +
												'...',
										} }
									></p>
								</div>
							</div>
						) }
					</>
				) }
			</div>
		</div>
	);
};

export default PostItem;
