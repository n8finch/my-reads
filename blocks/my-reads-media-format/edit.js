import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { getFormatAndEmoji } from '../../includes/js/utils';

import './editor.scss';

export default function Edit( {
	attributes: { format },
	setAttributes,
	context: { postType, postId },
} ) {
	const [ meta, updateMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);

	useEffect( () => {
		const initStyle = meta?._my_reads_format
			? meta?._my_reads_format
			: 'book';
		setAttributes( {
			format: initStyle,
		} );
	}, [] );

	const onChangeMediaFormat = ( val ) => {
		updateMeta( {
			...meta,
			_my_reads_format: val,
		} );
		setAttributes( { format: val } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Media format', 'my-reads' ) }>
					<SelectControl
						label={ __( 'Media format', 'my-reads' ) }
						onChange={ onChangeMediaFormat }
						value={ format }
						options={ [
							{
								label: __( 'Book', 'my-reads' ),
								value: 'book',
							},
							{
								label: __( 'Audiobook', 'my-reads' ),
								value: 'audiobook',
							},
							{
								label: __( 'Comicbook', 'my-reads' ),
								value: 'comicbook',
							},
							{
								label: __( 'Article', 'my-reads' ),
								value: 'article',
							},
						] }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<p>
					<span className={ `rating-${ format }` }>
						{ ' ' }
						{ __( 'Media', 'my-reads' ) }:{ ' ' }
						{ getFormatAndEmoji( format ) }
					</span>
				</p>
			</div>
		</>
	);
}
