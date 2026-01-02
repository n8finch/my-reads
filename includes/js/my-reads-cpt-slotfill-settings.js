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
	let postType = 'myreads';
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
	const allowedPostTypes = [ 'myreads' ];

	// If the post type is not viewable or not in the allowed list, do not render the plugin.
	if ( ! isViewable || ! allowedPostTypes.includes( postTypeName ) ) {
		return null;
	}

	const [ postId ] = useEntityProp( 'postType', 'myreads', 'id' );
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);
	const [ loading, setLoading ] = useState( false );

	const handleFetchAmazonData = async () => {
		if ( ! meta._myreads_amazonLink ) {
			alert( 'Please enter an Amazon URL.' );
			return;
		}

		if ( ! window.MYREADS_CPT || ! window.MYREADS_CPT.nonce ) {
			alert( 'There is no nonce to check.' );
			return;
		}

		setLoading( true );

		try {
			// Make an AJAX request to your custom PHP endpoint
			const response = await apiFetch( {
				path: '/my-reads/v1/fetch-amazon-data',
				method: 'POST',
				data: { url: meta._myreads_amazonLink },
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
					help={ __( 'Mark this read as a favorite.' ) }
					checked={ meta._myreads_isFavorite }
					onChange={ ( checked ) =>
						setMeta( { ...meta, _myreads_isFavorite: checked } )
					}
				/>
				<ToggleControl
					label={ __( 'Currently reading' ) }
					help={ __(
						'Show that you are currently reading this read.'
					) }
					checked={ meta._myreads_currentlyReading }
					onChange={ ( checked ) =>
						setMeta( {
							...meta,
							_myreads_currentlyReading: checked,
						} )
					}
				/>
				<br />
				<InputControl
					label={ __( 'Amazon URL' ) }
					value={ meta._myreads_amazonLink }
					onChange={ ( value ) =>
						setMeta( { ...meta, _myreads_amazonLink: value } )
					}
					disabled={ loading }
          help={ __( 'Enter the Amazon URL for this read to fetch the image and title.' ) }
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
