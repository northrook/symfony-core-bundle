/**
 * @param {string} html
 * @returns {ChildNode|HTMLElement}
 */
function createElement( html ) {
	return document.createRange().createContextualFragment( html ).firstChild
}

/**
 * @param {string} tag
 * @param {Attributes} attributes
 */
function newElement( tag, attributes = {} ) {
	let [ id, ...classes ] = tag.split( '.' );
	[ tag, attributes.id ] = id.split( '#', 2 );

	attributes.className = [
		...classes, attributes.className || attributes.class
	].filter( Boolean ).join( ' ' );

	delete attributes.class;

	return Object.assign( document.createElement( tag ), attributes )
}

/**
 * @param {string}selector
 * @param {Attributes} attributes
 * @param {Document|Node} context
 * @return {HTMLElement}
 */
function getElement( selector, attributes = {}, context = document.body ) {
	return context.querySelector( selector ) || newElement( selector, attributes );
}

/**
 * @param {Element|ChildNode|string} element
 * @param {Document|Element} context
 */
function ElementProxy( element, context = document ) {

	if ( typeof element === 'string' ) element = context.querySelectorAll( element )
	if ( element instanceof NodeList && element.length === 1 ) element = element[0]

	// ::DEBUG
	if ( !element instanceof Node ) console.error(
		'The provided element is not a valid DOM Node',
		{'element': element, 'context': context}
	)
	// DEBUG::

	const instance = {
		element: element,
		context: context,

		/**
		 * @param {number} duration
		 * @param {boolean} remove
		 */
		fadeOut: function ( duration = 250, remove = false ) {
			if ( this.element ) this.proxyElementFade( duration, 1, remove )
			return this;
		},

		/**
		 * @param {number} duration
		 */
		fadeIn: function ( duration = 400 ) {
			if ( this.element ) this.proxyElementFade( duration, 0 )
			return this;
		},

		appendTo: function ( context = document.body ) {
			( context || this.context ).append( this.element );
			return this;
		},

		prependTo: function ( context = document.body ) {
			( context || this.context ).prepend( this.element );
			return this;
		},

		/**
		 * @param {string}events
		 * @param {EventListenerOrEventListenerObject}callback
		 * @param {AddEventListenerOptions|boolean}options
		 */
		on: function ( events, callback, options ) {
			events.split( ' ' ).forEach( event => this.element.addEventListener( event, callback, options ) );
			return this
		},

		/**
		 * @param {string}events
		 * @param {EventListenerOrEventListenerObject}callback
		 * @param {AddEventListenerOptions|boolean}options
		 */
		off: function ( events, callback, options ) {
			events.split( ' ' ).forEach( event => this.element.removeEventListener( event, callback, options ) );
			return this
		},


		/**
		 *
		 * @param {number}duration
		 * @param {0|1}starting
		 * @param {boolean}remove
		 */
		proxyElementFade: function ( duration = 250, starting = 1, remove = false ) {
			const {element} = this;
			let opacity = Number( element.style.opacity || starting );
			const step = ( 1000 / 60 / duration ) * ( starting ? -1 : 1 );

			( function fade() {
				element.style.opacity = opacity = NumberWithin( opacity + step );
				if ( opacity > 0 && opacity < 1 ) setTimeout( fade, 1000 / 60 );
				if ( opacity === 0 && remove ) element.remove();
			} )();
		}
	}

	return new Proxy( instance, {
		get( target, prop ) {
			if ( prop in target ) return target[prop];
			if ( target.element && prop in target.element ) {
				const value = target.element[prop];
				return ( typeof value === 'function' ) ? value.bind( target.element ) : value;
			}
			return undefined;
		},
	} );
}

/**
 * @param {Node|string} selector
 * @param {Document|Node} context
 */
function $( selector, context = document ) {
	return ElementProxy( selector, context );
}

/**
 * @param {Node|string} selector
 * @param {Document|Node} context
 */
function $all( selector, context = document ) {
	return context.querySelectorAll( selector );
}

/**
 * @param {Node|string} selector
 * @param {Attributes} attributes
 * @param {Document|Node} context
 */
function $get( selector, attributes = {}, context = document.body ) {
	return ElementProxy( getElement( selector, attributes, context ), context );
}


/**
 * @param {string} html
 */
function $new( html ) {
	return ElementProxy( createElement( html ) );
}

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

//::: Multi-node select
// let nodes = element instanceof NodeList || Array.isArray( element )
// 	? element
// 	: element instanceof HTMLElement || element instanceof SVGElement
// 		? [ element ]
// 		: context.querySelectorAll( element )
//
// if ( !nodes.length ) nodes = []