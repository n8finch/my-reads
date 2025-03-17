if (
	typeof window.wp !== 'undefined' &&
	typeof window.wp.blocks !== 'undefined'
) {
	const { unregisterBlockType } = window.wp.blocks;

	window.wp.domReady( function () {
		unregisterBlockType( 'my-reads/my-reads-media-format' );
		unregisterBlockType( 'my-reads/my-reads-star-rating' );
	} );
}
