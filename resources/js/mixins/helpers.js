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
            formatDateTime(datetime, format = "YYYY MMM D HH:mm", defaultValue = "-") {
                return datetime ? moment(datetime).format(format) : defaultValue;
            },

            simulateSelectAll() {
                setTimeout(() => {
                    document.execCommand('selectall', false, 'tet');
                }, 50);
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
                if (isNaN(value)) {
                    return '-';
                }
                if (value === null) {
                    return '-';
                }

                const numberValue = Number(value);

                if (numberValue === 0) {
                    return '-';
                }

                if (decimals || numberValue % 1 !== 0) {
                    return numberValue.toFixed(decimals);
                }

                return this.dashIfZero(Number(value)).toLocaleString().replace(',', ' ');
            },

            dashIfZero(value) {
                return (value && value !== 0) ? value : '-';
            },

            setFocus: function (input, autoSelectAll = false, hideOnScreenKeyboard = false, delay = 100) {
                setTimeout(() => {
                    if (input === null) {
                        return;
                    }

                    if (hideOnScreenKeyboard) {
                        // this simple hack of setting focus when field is read only will
                        // prevent showing on screen keyboard on mobile devices
                        input.readOnly = true;
                    }

                    input.focus();

                    if (hideOnScreenKeyboard) {
                        input.readOnly = false;
                    }

                    if (autoSelectAll) {
                        document.execCommand('selectall', false);
                    }

                    }, delay);
            },

            setFocusElementById(delay = 1, elementId, autoSelectAll = false, hideOnScreenKeyboard = false) {
                if (hideOnScreenKeyboard) {
                    // this simple hack of setting focus when field is read only will
                    // prevent showing on screen keyboard on mobile devices
                    document.getElementById(elementId).readOnly = true;
                }
                this.setFocus(document.getElementById(elementId), autoSelectAll, hideOnScreenKeyboard, delay);
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
                    timeout: 15 * 1000,
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
