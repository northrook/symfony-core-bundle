type NumberMS = number;

type Attributes = {
    class: string | string[],
    style: string | string[],
    string: string,
    className: string,
} | {}

type AttributeName = string | 'class';

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