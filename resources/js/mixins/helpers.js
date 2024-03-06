import beep from "./beep";
import moment from "moment";
import {isNull} from "lodash/lang";

function getValueOrDefault(value, defaultValue)
{
    return (value === undefined) || (value === null) ? defaultValue : value;
}

export default {
    mixins: [beep],

        methods: {

            financial(decimal) {
                return Number.parseFloat(decimal).toFixed(2);
            },

            formatDateTime(datetime, format = "YYYY MMM D HH:mm", defaultValue = "-") {
                return datetime ? moment(datetime).format(format) : defaultValue;
            },

            simulateSelectAll(e) {
                console.log(e);
                // e.target.element selectAll();
            },

            copyToClipBoard(textToCopy){
                const tmpTextField = document.createElement("textarea")
                tmpTextField.textContent = textToCopy
                tmpTextField.setAttribute("style","position:absolute; right:200%;")
                document.body.appendChild(tmpTextField)
                tmpTextField.select()
                tmpTextField.setSelectionRange(0, 99999) /*For mobile devices*/
                document.execCommand("copy")
                tmpTextField.remove()
            },

            toNumberOrDash(value, decimals = null) {

                if (isNaN(value) || value === null || Number(value) === 0) {
                    return '-';
                }

                const numberValue = Number(value);
                if (decimals || numberValue % 1 !== 0) {
                    return numberValue.toFixed(decimals ? decimals : 2);
                }
                return this.dashIfZero(numberValue).toLocaleString().replace(',', ' ');
            },

            dashIfZero(value) {
                return (value && value !== 0) ? value : '-';
            },

            setFocusElementById(elementId, showKeyboard = false, autoSelectAll = true, delay = 100) {
                const element = document.getElementById(elementId);

                if (element === null) {
                    return;
                }

                element.blur();

                const isIos = () => !!window.navigator.userAgent.match(/Mac OS|iPad|iPhone/i);

                if (isIos()) {
                    this.focusAndOpenKeyboard(element, delay, showKeyboard);
                    return;
                }

                if (showKeyboard === false) {
                    // this simple hack of setting focus when field is read only will
                    // prevent showing on screen keyboard on mobile devices
                    element.readOnly = true;
                }

                setTimeout(() => {
                    element.focus();
                    element.click();

                    element.readOnly = false;

                    if (autoSelectAll) {
                        element.select();
                    }

                }, delay);
            },

            focusAndOpenKeyboard(element, delay= 100, showKeyboard = false, autoSelectAll = true) {
                if (showKeyboard) {
                    // Align temp input element approximately where the input element is
                    // so the cursor doesn't jump around
                    var __tempEl__ = document.createElement('input');
                    __tempEl__.style.position = 'absolute';
                    __tempEl__.style.top = (element.offsetTop + 7) + 'px';
                    __tempEl__.style.left = element.offsetLeft + 'px';
                    __tempEl__.style.height = 0;
                    __tempEl__.style.opacity = 0;
                    // Put this temp element as a child of the page <body> and focus on it
                    document.body.appendChild(__tempEl__);
                    __tempEl__.focus();
                }

                // The keyboard is open. Now do a delayed focus on the target element
                setTimeout(function() {
                    element.focus();
                    element.click();

                    if (autoSelectAll) {
                        element.select();
                    }

                    if (__tempEl__) {
                        document.body.removeChild(__tempEl__);
                    }


                }, delay);
            },

            isMoreThanPercentageScrolled: function (percentage) {
                return document.documentElement.scrollTop + window.innerHeight > document.documentElement.offsetHeight * (percentage / 100);
            },

            getValueOrDefault,

            showException: function (exception, options) {
                const defaultOptions = {
                    closeOnClick: true,
                    timeout: 0,
                    buttons: [
                    {text: 'OK', action: null},
                    ]
                };

                this.$snotify.error(exception.response.data, options ?? defaultOptions);
            },

            showError: function (message, options) {
                const defaultOptions = {
                    closeOnClick: true,
                    timeout: 5 * 1000,
                    showProgressBar: true,
                    buttons: [
                        {text: 'OK', action: null},
                    ]
                };

                let finalOptions = {...defaultOptions, ...options};

                this.$snotify.error(message, finalOptions);
            },

            notifyError: function (message, options = null) {
                this.showError(message, options);
                this.errorBeep();
            },

            notifySuccess: function (message = null, beep = true, options = null) {
                const defaultOptions = {
                    timeout: 1000,
                    showProgressBar: false,
                    pauseOnHover: true,
                    icon: false,
                    buttons: []
                };

                if (message) {
                    this.$snotify.success(message, options ?? defaultOptions);
                }

                if (beep) {
                    this.beep();
                }
            },
    }
}
