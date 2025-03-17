import { __ } from '@wordpress/i18n';
import {
	Button,
	SelectControl,
	ToggleControl,
	PanelBody,
} from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import MyReadsFilterApp from './MyReadsApp';

export default function Edit( { setAttributes, attributes } ) {
	const { layout, order, useAmazonLink } = attributes;
	const regenerateReadsJSON = () => {
		const apiUrl = '/wp-json/my-reads/v1/all-the-reads?refresh=true';

		fetch( apiUrl, {
			method: 'GET', // Adjust the method based on what your endpoint expects
			headers: {
				'Content-Type': 'application/json',
			},
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				console.log( 'Success:', data );
				alert( 'JSON Regeneration Complete!' );
			} )
			.catch( ( error ) => {
				console.error( 'Error:', error );
				alert( 'Error occurred while regenerating JSON.' );
			} );
	};

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
						checked={ attributes.useAmazonLink || false }
						help={ __(
							'Link each read using the Amazon link instead of linking to the internal page.',
							'my-reads'
						) }
					/>
					<Button variant="primary" onClick={ regenerateReadsJSON }>
						Regenerate reads JSON
					</Button>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<MyReadsFilterApp attributes={ attributes } />
			</div>
		</>
	);
}
