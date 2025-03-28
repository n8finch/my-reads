import { __ } from '@wordpress/i18n';
import { SelectControl, ToggleControl, PanelBody } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import MyReadsFilterApp from './MyReadsApp';

export default function Edit( { setAttributes, attributes } ) {
	const { layout, order, useAmazonLink } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Layout Type', 'my-reads' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Select layout type', 'my-reads' ) }
						options={ [
							{ label: 'Row', value: 'row' },
							{ label: 'List', value: 'list' },
						] }
						onChange={ ( layout ) =>
							setAttributes( { layout: layout } )
						}
						value={ layout }
					/>
					<SelectControl
						label={ __( 'Display order', 'my-reads' ) }
						options={ [
							{ label: 'Most recent first', value: 'reverse' },
							{
								label: 'Chronological (oldest first)',
								value: 'chronological',
							},
						] }
						onChange={ ( order ) =>
							setAttributes( { order: order } )
						}
						value={ order }
					/>
					<ToggleControl
						label={ __( 'Use Amazon link', 'my-reads' ) }
						onChange={ ( useAmazonLink ) =>
							setAttributes( { useAmazonLink: useAmazonLink } )
						}
						checked={ useAmazonLink || false }
						help={ __(
							'Link each read using the Amazon link instead of linking to the internal page.',
							'my-reads'
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<MyReadsFilterApp attributes={ attributes } />
			</div>
		</>
	);
}
