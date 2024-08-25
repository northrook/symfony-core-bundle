const App = {
	events: new Map(),

	/**
	 * Get or Set the application status indicator.
	 *
	 * @param {?string} set
	 * @return {string} App Status
	 */
	status: ( set = null ) => {
		if ( set ) document.documentElement.setAttribute( 'status', set )
		return document.documentElement.getAttribute( 'status' ) || 'unknown'
	},

	on: {
		/**
		 * Invoke a `callback` when the content has been updated.
		 *
		 * Triggers on `DOMContentLoaded` and `htmx:afterSwap`.
		 *
		 * @param {EventListenerOrEventListenerObject} callback
		 * @param {HTMLElement|Document} node
		 * @param {boolean} clear Clear any previously assigned listeners
		 */
		ContentLoaded: ( callback, node = document, clear = true ) => {
			if ( clear ) {
				node.removeEventListener( 'DOMContentLoaded', callback )
				node.removeEventListener( 'htmx:afterSwap', callback )
			}
			node.addEventListener( 'DOMContentLoaded', callback )
			node.addEventListener( 'htmx:afterSwap', callback )

			App.events.set( node, callback )
		},
	},
}