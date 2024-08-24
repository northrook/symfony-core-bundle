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

		append: function ( to = document.body ) {
			( to || this.context ).append( this.element );
			return this;
		},

		prepend: function ( to = document.body ) {
			( to || this.context ).prepend( this.element );
			return this;
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
function $make( html ) {
	return ElementProxy( createElement( html ) );
}

//::: Multi-node select
// let nodes = element instanceof NodeList || Array.isArray( element )
// 	? element
// 	: element instanceof HTMLElement || element instanceof SVGElement
// 		? [ element ]
// 		: context.querySelectorAll( element )
//
// if ( !nodes.length ) nodes = []