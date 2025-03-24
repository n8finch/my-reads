document
	.getElementById( 'regenerate-json-btn' )
	.addEventListener( 'click', function () {
		// Check if MYREADS_SETTINGS is defined.
		if ( ! window.MYREADS_SETTINGS || ! MYREADS_SETTINGS.allTheReads ) {
			return;
		}

		const apiUrl = MYREADS_SETTINGS?.allTheReads;

		fetch( apiUrl, {
			method: 'GET',
			credentials: 'include',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': MYREADS_SETTINGS?.nonce,
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
	} );
