// ::: DEBUG :::
let lastTime = Date.now() // Initialize with the current time

function logTimeSinceLastAction(actionDescription) {
    const currentTime = Date.now()
    const elapsed     = currentTime - lastTime
    console.log(`${actionDescription}: ${elapsed} ms since last action`)
    lastTime = currentTime // Update lastTime for the next action
}

logTimeSinceLastAction('init')

// ::: END DEBUG

function usleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms))
}

function NumberWithin(value, min = 0, max = 1) {
    return Math.max(min, Math.min(max, value))
}

// ** Core Element Fancy Dollar
const Core = (function Core() {
    return {
        /**
         * @param {Element|ChildNode|string} element
         * @param {Document|Element} context
         * @return {HTMLElement}
         */
        elementProxy: function (element, context = document) {
            // console.log( element );
            if (typeof element === 'string') {
                element = context.querySelectorAll(element)
                if (element instanceof NodeList && element.length === 1) {
                    element = element[0]
                }
            }

            this.element = element

            /**
             *
             * @param {number}duration
             * @param {0|1}starting
             * @param {boolean}remove
             */
            this.proxyElementFade = function (duration = 250, starting = 1, remove = false) {

                const element = this.element
                let opacity   = Number(element.style.opacity || starting)
                const tick    = 1000 / 60 // Run the animation at 60 FPS
                const step    = (tick / duration) * (starting ? -1 : 1)

                console.log(step)

                function fade() {
                    opacity               = NumberWithin(opacity + step)
                    element.style.opacity = opacity
                    if (opacity > 0 && opacity < 1) {
                        setTimeout(fade, tick)
                    }
                    if (element.style.opacity === '0' && remove) {
                        element.remove()
                    }
                }

                fade()
            }
            this.fadeOut = function (duration = 250, remove = false) {
                if (this.element) {
                    this.proxyElementFade(duration, 1, remove)
                }
                return this // Allows chaining
            }

            this.fadeIn = function (duration = 400) {
                if (this.element) {
                    this.proxyElementFade(duration, 0)
                }
                return this // Allows chaining
            }
            return this
        }, /**
         * @param {string} html
         * @returns {ChildNode|HTMLElement}
         */
        elementFrom: (html) => document.createRange().createContextualFragment(html).firstChild,

        /**
         * @param element {string}
         * @param attributes
         */
        elementCreate: function (element, attributes = {}) {

            const [tagWithId, ...classes] = element.split('.')
            const [tag, id]               = tagWithId.split('#', 2)

            if (id) {
                attributes.id = id
            }

            if ('class' in attributes) {
                attributes.className = attributes.class
                delete attributes.class
            }

            if (classes.length > 0) {
                attributes.className = attributes.className
                        ? classes.join(' ') + ' ' + attributes.className
                        : classes.join(' ')
            }

            return Object.assign(document.createElement(tag), attributes)
        }

        ,

        elementGet: (selector, attributes = {}, attach = document.body, append = false) => {
            /** @type {?HTMLElement}*/
            let element = document.querySelector(selector)

            if (!element) {
                element = Core().elementCreate(selector, attributes)
                if (append) {
                    attach?.append(element)
                } else {
                    attach?.prepend(element)
                }
            }

            return element
        },

        /**
         * @return {HTMLElement}
         */
        proxy: (instance) => new Proxy(instance, {
            get(target, prop) {
                if (prop in target) {
                    return target[prop]
                }
                if (target.element && prop in target.element) {
                    const value = target.element[prop]
                    return (typeof value === 'function') ? value.bind(target.element) : value
                }
                return undefined
            },
        }),
    }
})()

function $(selector, context = document) {
    const instance = new Core.elementProxy(selector, context)
    return Core.proxy(instance)
}

function $make(html) {
    const instance = new Core.elementProxy(document.createRange().createContextualFragment(html).firstChild)
    return Core.proxy(instance)
}

/**
 * @param {string}selector
 * @param {Object}attributes
 * @param {?HTMLElement} attach
 * @param {boolean} append
 * @return {HTMLElement}
 */
function $get(selector, attributes = {}, attach = document.body, append = false) {
    const instance = new Core.elementProxy(document.querySelector(selector) || Core.elementGet(selector, attributes))
    return Core.proxy(instance)
}

// ** End CoreFancyDollar

/**
 * @param {HTMLElement} node
 */
function elementAnimations(node) {
    /** @type {ElementAnimations}  */
    const transitions = {
        longest    : {
            property: null, ms: 0,
        }, shortest: {
            property: null, ms: 0,
        },
    }

    if (!(node instanceof Element)) {
        console.error('Invalid DOM element provided.')
        return transitions
    }

    const style      = getComputedStyle(node)
    const delays     = style.transitionDelay.split(', ').map(delay => parseFloat(delay) * 1000)
    const durations  = style.transitionDuration.split(', ').map(duration => parseFloat(duration) * 1000)
    const properties = style.transitionProperty.split(', ')

    properties.forEach((property, i) => {
        const delay    = delays[i]
        const duration = durations[i]
        const timing   = delay + duration

        Object.assign(transitions, {
            property: {
                name: property, delay, duration, total: timing,
            },
        })

        if (timing > transitions.longest.ms) {
            transitions.longest.property = property
            transitions.longest.ms       = timing
        }

        if (timing < transitions.shortest.ms) {
            transitions.shortest.property = property
            transitions.shortest.ms       = timing
        }
    })

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
    status: (set = null) => {
        if (set) {
            document.documentElement.setAttribute('status', set)
        }
        return document.documentElement.getAttribute('status') || 'unknown'
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
        ContentLoaded: (callback, node = document, clear = true) => {
            if (clear) {
                node.removeEventListener('DOMContentLoaded', callback)
                node.removeEventListener('htmx:afterSwap', callback)
            }
            node.addEventListener('DOMContentLoaded', callback)
            node.addEventListener('htmx:afterSwap', callback)

            App.events.set(node, callback)
        },
    },
}