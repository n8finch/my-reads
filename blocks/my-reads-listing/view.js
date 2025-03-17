import { createRoot } from 'react-dom/client';
import MyReadsFilterApp from './MyReadsApp';

const roots = document.querySelectorAll( '#my-reads-filter' );

window.addEventListener(
	'load',
	function () {
		for ( const root of roots ) {
			// Get attributes from the block element, this occurs before the React app is mounted.
			const attributes = root
				? JSON.parse( root.getAttribute( 'data-attributes' ) )
				: {};
			const reactRoot = createRoot( root );
			reactRoot.render( <MyReadsFilterApp attributes={ attributes } /> );
		}
	},
	false
);
