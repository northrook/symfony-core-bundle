type NumberMS = number;

// Animations
type ElementAnimations = {
    longest: TransitionProperty,
    shortest: TransitionProperty
}

type TransitionProperty = {
    property: string,
    ms: NumberMS

}

// Toast Notifications
interface ToastContainer extends HTMLElement {
}

interface ToastElement extends HTMLElement {
}