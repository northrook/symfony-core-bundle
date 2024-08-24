/**
 * @param {HTMLElement} node
 */
function elementAnimations( node ) {
	/** @type {ElementAnimations}  */
	const transitions = {
		longest    : {
			property: null, ms: 0,
		}, shortest: {
			property: null, ms: 0,
		},
	}

	if ( !( node instanceof Element ) ) {
		console.error( 'Invalid DOM element provided.' )
		return transitions
	}

	const style = getComputedStyle( node )
	const delays = style.transitionDelay.split( ', ' ).map( delay => parseFloat( delay ) * 1000 )
	const durations = style.transitionDuration.split( ', ' ).map( duration => parseFloat( duration ) * 1000 )
	const properties = style.transitionProperty.split( ', ' )

	properties.forEach( ( property, i ) => {
		const delay = delays[i]
		const duration = durations[i]
		const timing = delay + duration

		Object.assign( transitions, {
			property: {
				name: property, delay, duration, total: timing,
			},
		} )

		if ( timing > transitions.longest.ms ) {
			transitions.longest.property = property
			transitions.longest.ms = timing
		}

		if ( timing < transitions.shortest.ms ) {
			transitions.shortest.property = property
			transitions.shortest.ms = timing
		}
	} )

	return transitions
}

const App = {
	events: new Map(),

	/**
	 * Get or Set the application status indicator.
	 *
	 * @param {?string} set
	 * @return {string} App Status
	 */
	status: ( set = null ) => {
		if ( set ) {
			document.documentElement.setAttribute( 'status', set )
		}
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