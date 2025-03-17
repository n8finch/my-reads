import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	Button,
	__experimentalInputControl as InputControl,
	ToggleControl,
} from '@wordpress/components';
import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import { post } from '@wordpress/icons';
/**
 * The component to be rendered  as part of the plugin.
 */
const MyReadsCPTSettings = () => {
	let postType = 'my_reads';
	// Retrieve information about the current post type.
	const { isViewable, postTypeName } = useSelect( ( select ) => {
		postType = select( editorStore ).getCurrentPostType();
		const postTypeObject = select( coreStore ).getPostType( postType );
		return {
			isViewable: postTypeObject?.viewable,
			postTypeName: postType,
		};
	}, [] );

	// The list of post types that are allowed to render the plugin.
	const allowedPostTypes = [ 'my_reads' ];

	// If the post type is not viewable or not in the allowed list, do not render the plugin.
	if ( ! isViewable || ! allowedPostTypes.includes( postTypeName ) ) {
		return null;
	}

	const [ postId ] = useEntityProp( 'postType', 'my_reads', 'id' );
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);
	const [ loading, setLoading ] = useState( false );

	const handleFetchAmazonData = async () => {
		if ( ! meta._my_reads_amazonLink ) {
			alert( 'Please enter an Amazon URL.' );
			return;
		}

		setLoading( true );

		try {
			// Make an AJAX request to your custom PHP endpoint
			const response = await apiFetch( {
				path: '/my-reads/v1/fetch-amazon-data',
				method: 'POST',
				data: { url: meta._my_reads_amazonLink },
			} );

			if ( response.error ) {
				alert( response.error );
				return;
			}
			// Assuming the response contains the title and attachmentId
			const { title, attachmentId } = response?.data;

			// Update the post title
			dispatch( 'core/editor' ).editPost( { title } );

			// Set the featured image
			dispatch( 'core/editor' ).editPost( {
				featured_media: attachmentId,
			} );

			alert( 'Amazon data fetched and applied successfully!' );
		} catch ( error ) {
			console.error( 'Error fetching Amazon data:', error );
			alert( 'Failed to fetch Amazon data.' );
		} finally {
			setLoading( false );
		}
	};

	return (
		<PluginDocumentSettingPanel
			name="custom-panel"
			title={ __( 'My Reads Settings' ) }
			className="my-reads-panel"
		>
			<>
				<ToggleControl
					label={ __( 'Mark as favorite' ) }
					help={ __( 'Check to mark this post as a favorite' ) }
					checked={ meta._my_reads_isFavorite }
					onChange={ ( checked ) =>
						setMeta( { ...meta, _my_reads_isFavorite: checked } )
					}
				/>
				<br />
				<InputControl
					label={ __( 'Amazon URL' ) }
					value={ meta._my_reads_amazonLink }
					onChange={ ( value ) =>
						setMeta( { ...meta, _my_reads_amazonLink: value } )
					}
					disabled={ loading }
				/>
				<br />
				<Button
					variant="primary"
					onClick={ handleFetchAmazonData }
					disabled={ loading }
				>
					{ loading
						? __( 'Fetching...' )
						: __( 'Fetch Amazon Data' ) }
				</Button>
			</>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'my-reads-cpt-slotfill-settings', {
	render: MyReadsCPTSettings,
} );
