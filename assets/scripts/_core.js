/**
 * @param {HTMLElement} node
 */
function elementAnimations(node)
{
    /** @type {ElementAnimations}  */
    const transitions = {
        longest : {
            property: null,
            ms      : 0
        },
        shortest: {
            property: null,
            ms      : 0
        },
    };

    if (!(node instanceof Element)) {
        console.error("Invalid DOM element provided.");
        return transitions;
    }

    const style = getComputedStyle(node);
    const delays = style.transitionDelay.split(", ").map(delay => parseFloat(delay) * 1000);
    const durations = style.transitionDuration.split(", ").map(duration => parseFloat(duration) * 1000);
    const properties = style.transitionProperty.split(", ");


    properties.forEach((property, i) => {
        const delay = delays[i];
        const duration = durations[i];
        const timing = delay + duration;

        Object.assign(transitions, {
            property: {
                name : property,
                delay,
                duration,
                total: timing
            }
        });

        if (timing > transitions.longest.ms) {
            transitions.longest.property = property;
            transitions.longest.ms = timing;
        }

        if (timing < transitions.shortest.ms) {
            transitions.shortest.property = property;
            transitions.shortest.ms = timing;
        }
    });

    return transitions;
}

/**
 * @param tag {string}
 * @param attributes
 */
function elementCreate(tag, attributes = {})
{
    if (tag.includes('#')) {
        let id;
        [tag, id] = tag.split('#', 2)
        if (id.includes('.')) id = id.split('.', 1)[0];
        Object.assign(attributes, {id: id})
    }

    return Object.assign(document.createElement(tag), attributes);
}

/**
 *
 * @param {string} html
 * @returns
 */
function elementFrom(html)
{
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    return wrapper.firstChild;
}

/**
 * @param {string}selector
 * @param {Object}attributes
 * @param {?HTMLElement} attach
 * @param {boolean} append
 * @return {HTMLElement}
 */
function elementGet(selector, attributes = {}, attach = document.body, append = false)
{
    /** @type {?HTMLElement}*/
    let element = document.querySelector(selector);

    if (!element) {
        element = elementCreate(selector, attributes);
        if (append) {
            attach?.append(element);
        } else {
            attach?.prepend(element);
        }
    }

    return element;
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
            document.documentElement.setAttribute("status", set);
        }
        return document.documentElement.getAttribute("status") || 'unknown';
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
                node.removeEventListener('DOMContentLoaded', callback);
                node.removeEventListener('htmx:afterSwap', callback);
            }
            node.addEventListener('DOMContentLoaded', callback);
            node.addEventListener('htmx:afterSwap', callback);

            App.events.set(node, callback);
        }
    }
}