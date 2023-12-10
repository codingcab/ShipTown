<template>
    <div>
        <div class="bg-warning">
            <input :id="getInputId"
                   type=text
                   class="form-control"
                   autocomplete="off"
                   enterkeyhint="done"
                   :placeholder="placeholder"
                   ref="barcode"
                   dusk="barcode-input-field"
                   v-model.trim="barcode"
                   @focus="simulateSelectAll"
                   @keyup.enter="barcodeScanned(barcode)"
            />
        </div>

      <b-modal :id="getModalID" scrollable no-fade hide-header
               @submit="updateShelfLocation"
               @shown="updateShelfLocationShown"
               @hidden="updateShelfLocationHidden">
          <div class="h5 text-center">{{ command['name'] }} : {{ command['value'] }}</div>
          <div v-if="shelfLocationModalContinuesScan" class="alert-success text-center mb-2 small">CONTINUES SCAN ENABLED</div>

          <input id="set-shelf-location-command-modal-input" class="form-control" :placeholder="'Scan product to update shelf location: ' + command[1]"
               @focus="simulateSelectAll"
               @keyup.enter="updateShelfLocation"/>

          <div class="mt-2 small">
              <div>
                  <span class="text-primary font-weight-bold">Continues Scan</span><span>- scan shelf again to enable</span>
              </div>
              <div>
                  <span class="text-danger font-weight-bold">Close</span><span>- scan twice to close</span>
              </div>
          </div>
      </b-modal>

    </div>
</template>

<script>
    import helpers from "../../mixins/helpers";
    import url from "../../mixins/url";
    import FiltersModal from "../Packlist/FiltersModal";
    import api from "../../mixins/api";

    export default {
        name: "BarcodeInputField",

        mixins: [helpers, url, FiltersModal, api],

        props: {
            input_id: null,
            url_param_name: null,
            placeholder: '',
            autoFocusAfter: {
                type: Number,
                default: 100,
            },
        },

        computed: {
            getInputId() {
                if (this.input_id) {
                    return this.input_id;
                }

                return `barcode-input-field-${Math.floor(Math.random() * 10000000)}`;
            },
            getModalID() {
                return `set-shelf-location-command-modal-${Math.floor(Math.random() * 10000000)}`;
            }
        },

        data: function() {
            return {
                typedInText: '',
                currentLocation: '',
                barcode: '',
                command: ['',''],

                shelfLocationModalCommandScanCount: 0,
                shelfLocationModalShowing: false,
                shelfLocationModalContinuesScan: false,
            }
        },


        shown() {
            console.log('shown');
        },

        mounted() {
            const isIos = () => !!window.navigator.userAgent.match(/iPad|iPhone/i);

            if (isIos()) {
                console.log('On iPhones and iPads, devices autofocus on input fields is disabled due to a bug in iOS. This works ok with external keyboards on iOS >16');
            }

            console.log(window.navigator.userAgent);
            console.log(isIos());
            this.resetInputValue();
            // window.addEventListener('pageshow', (e) => {
            //     this.setFocusOnBarcodeInput(200);
            //     console.log('pageshow');
            // });


            // Usage example
            var myElement = document.getElementById(this.getInputId);
            var modalFadeInDuration = 300;

            if (this.autoFocusAfter > 0) {
                // this.focusAndOpenKeyboard(myElement, modalFadeInDuration);
                this.setFocusElementById(this.autoFocusAfter, this.getInputId, true, true)
            }

            window.addEventListener('keydown', (e) => {
                if (e.target.nodeName !== 'BODY') {
                    return;
                }

                if (e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) {
                    return;
                }

                if (e.key === 'Enter') {
                    this.barcode = this.typedInText;
                    this.barcodeScanned(this.typedInText);
                    return;
                }

                // document.getElementById('barcodeInput').value += e.key;
                this.typedInText += e.key;
            });
        },

        methods: {

             focusAndOpenKeyboard(el, timeout) {
                if(!timeout) {
                    timeout = 100;
                }
                if(el) {
                    // Align temp input element approximately where the input element is
                    // so the cursor doesn't jump around
                    var __tempEl__ = document.createElement('input');
                    __tempEl__.style.position = 'absolute';
                    __tempEl__.style.top = (el.offsetTop + 7) + 'px';
                    __tempEl__.style.left = el.offsetLeft + 'px';
                    __tempEl__.style.height = 0;
                    __tempEl__.style.opacity = 0;
                    // Put this temp element as a child of the page <body> and focus on it
                    document.body.appendChild(__tempEl__);
                    __tempEl__.focus();
                    console.log('test');

                    // The keyboard is open. Now do a delayed focus on the target element
                    setTimeout(function() {
                        el.focus();
                        el.click();
                        // Remove the temp element
                        document.body.removeChild(__tempEl__);
                    }, timeout);
                }
            },

             simulateClick(control) {
                if (document.all) {
                    control.click();
                } else {
                    var evObj = document.createEvent('MouseEvents');
                    evObj.initMouseEvent('click', true, true, window, 1, 12, 345, 7, 220, false, false, true, false, 0, null );
                    control.dispatchEvent(evObj);
                }
            },

            barcodeScanned(barcode) {
                if (barcode && barcode !== '') {
                    this.apiPostActivity({
                      'log_name': 'search',
                      'description': barcode,
                    })
                    .catch((error) => {
                        this.displayApiCallError(error)
                    });
                }

                if (this.tryToRunCommand(barcode)) {
                    this.barcode = '';
                    this.typedInText = '';
                    return;
                }

                if(this.url_param_name) {
                    this.setUrlParameter(this.url_param_name, barcode);
                }

                this.$emit('barcodeScanned', barcode);
                this.typedInText = '';
                this.barcode = barcode;

                this.setFocusOnBarcodeInput(100, true);
            },

            updateShelfLocationShown: function (bvEvent, modalId) {
                this.shelfLocationModalShowing = true;
                this.shelfLocationModalContinuesScan = false;
                this.shelfLocationModalCommandScanCount = 0;
                this.setFocusElementById(100, 'set-shelf-location-command-modal-input', true, true)
            },

            updateShelfLocationHidden: function (bvEvent, modalId) {
                this.shelfLocationModalShowing = false;
                this.shelfLocationModalContinuesScan = false;
                this.shelfLocationModalCommandScanCount = 0;
                this.resetInputValue();
                this.setFocusElementById(300, 'barcodeInput', true, true)
                this.$emit('refreshRequest');
            },

            resetInputValue: function () {
                if (this.url_param_name) {
                    this.barcode = this.getUrlParameter(this.url_param_name);
                }
            },

            showShelfLocationModal: function () {
                this.$bvModal.show(this.getModalID);
                this.warningBeep();
                this.setFocusElementById(1, 'set-shelf-location-command-modal-input')
            },

            tryToRunCommand: function (textEntered) {
                if (textEntered === null || textEntered === '') {
                    return false;
                }

                this.lastCommand = textEntered;

                let command = this.lastCommand.split(':');

                if(command.length < 2) {
                    return false;
                }

                this.command['name'] = command[0];
                this.command['value'] = command[1];

                switch (this.command['name'].toLowerCase())
                {
                    case 'shelf':
                        this.showShelfLocationModal(this.lastCommand);
                        return true;
                    case 'goto':
                        this.runGotoCommand();
                        return true;
                }

                return false;
            },

            runGotoCommand() {
                window.location.href = this.command['value'];
            },

            updateShelfLocation(event)
            {
                const textEntered = event.target.value;

                if (textEntered === "") {
                    return;
                }

                if (textEntered === this.command['name'] + ':' + this.command['value']) {
                    this.shelfLocationModalCommandScanCount = this.shelfLocationModalCommandScanCount + 1;

                    switch (this.shelfLocationModalCommandScanCount)
                    {
                        case 1:
                            this.shelfLocationModalContinuesScan = true;
                            break;
                        case 2:
                            this.$bvModal.hide('set-shelf-location-command-modal');
                            break;
                    }

                    event.target.value = '';
                    return ;
                }

                this.apiInventoryGet({
                        'filter[sku_or_alias]': textEntered,
                        'filter[warehouse_id]': this.currentUser()['warehouse_id'],
                    })
                    .then((response) => {
                        if (response.data['meta']['total'] !== 1) {
                            this.notifyError('SKU "'+ event.target.value +'" not found ');
                            return;
                        }

                        const inventory = response.data.data[0];
                        this.apiInventoryPost({
                                'id': inventory['id'],
                                'shelve_location': this.command['value'],
                            })
                            .then(() => {
                                this.notifySuccess('Shelf updated');
                            })
                            .catch((error) => {
                                this.displayApiCallError(error)
                            });
                    })
                    .catch((error) => {
                        this.displayApiCallError(error)
                    });

                if(this.shelfLocationModalContinuesScan) {
                    this.setFocusElementById(1, 'set-shelf-location-command-modal-input', true, true)
                    return;
                }

                this.$bvModal.hide(this.getModalID);
            },

            setFocusOnBarcodeInput(delay = 100, autoSelectAll = false, hideOnScreenKeyboard = false) {
                this.setFocusElementById(delay, this.getInputId, autoSelectAll, hideOnScreenKeyboard)
            },
        }
    }
</script>

<style scoped>

</style>
