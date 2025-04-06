import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

export default function Edit( { context: { postType, postId } } ) {
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);

	const onChangeAuthor = ( val ) => {
		setMeta( {
			...meta,
			_myreads_author: val,
		} );
	};

	return (
		<div { ...useBlockProps() }>
			<p>
				<span>{ __( ' Author: ', 'my-reads' ) }</span>
				<RichText
					tagName="span"
					value={ meta._myreads_author }
					allowedFormats={ [] }
					onChange={ onChangeAuthor }
					placeholder={ __( 'Author...' ) }
				/>
			</p>
		</div>
	);
}
