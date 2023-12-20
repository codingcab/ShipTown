<template>
    <div>
        <div class="bg-warning">
            <input :id="getInputId"
                   :placeholder="placeholder"
                   type=text
                   class="form-control barcode-input"
                   autocomplete="off"
                   autocapitalize="off"
                   enterkeyhint="done"
                   ref="barcode"
                   dusk="barcode-input-field"
                   v-model.trim="barcode"
                   @keyup.enter="barcodeScanned(barcode)"
            />
        </div>

      <b-modal :id="getModalID" scrollable no-fade hide-header
               @submit="updateShelfLocation"
               @shown="updateShelfLocationShown"
               @hidden="updateShelfLocationHidden">
          <div class="h5 text-center">{{ command['name'] }} : {{ command['value'] }}</div>
          <div v-if="shelfLocationModalContinuesScan" class="alert-success text-center mb-2 small">CONTINUES SCAN ENABLED</div>

          <input id="set-shelf-location-command-modal-input"
                 :placeholder="'Scan product to update shelf location: ' + command[1]"
                 type=text
                 class="form-control"
                 autocomplete="off"
                 autocapitalize="off"
                 enterkeyhint="done"
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

        mounted() {
            const isIos = () => !!window.navigator.userAgent.match(/iPad|iPhone/i);

            if (isIos()) {
                console.log('On iPhones and iPads, devices autofocus on input fields is disabled due to a bug in iOS. This works ok with external keyboards on iOS >16');
            }

            this.importValueFromUrlParam();

            if (this.autoFocusAfter > 0) {
                this.setFocusElementById(this.getInputId)
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

                this.typedInText += e.key;
            });
        },

        methods: {
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

                this.setFocusOnBarcodeInput();
            },

            updateShelfLocationShown: function (bvEvent, modalId) {
                this.shelfLocationModalShowing = true;
                this.shelfLocationModalContinuesScan = false;
                this.shelfLocationModalCommandScanCount = 0;
                this.setFocusElementById('set-shelf-location-command-modal-input')
            },

            updateShelfLocationHidden: function (bvEvent, modalId) {
                this.shelfLocationModalShowing = false;
                this.shelfLocationModalContinuesScan = false;
                this.shelfLocationModalCommandScanCount = 0;
                this.importValueFromUrlParam();
                this.setFocusElementById('barcodeInput')
                this.$emit('refreshRequest');
            },

            importValueFromUrlParam: function () {
                if (this.url_param_name) {
                    this.barcode = this.getUrlParameter(this.url_param_name);
                }
            },

            showShelfLocationModal: function () {
                this.$bvModal.show(this.getModalID);
                this.warningBeep();
                this.setFocusElementById('set-shelf-location-command-modal-input')
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

                let s = this.command['name'] + ':' + this.command['value'];

                this.notifySuccess('Scanned: ' + textEntered);
                this.notifySuccess('Command: ' + s);

                if (textEntered === s) {
                    event.target.value = '';

                    if (this.shelfLocationModalContinuesScan) {
                        this.$bvModal.hide(this.getModalID);
                        return;

                    }

                    this.shelfLocationModalContinuesScan = true;
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
                    this.setFocusElementById('set-shelf-location-command-modal-input')
                    return;
                }

                this.$bvModal.hide(this.getModalID);
            },

            setFocusOnBarcodeInput(showKeyboard = false, autoSelectAll = true, delay = 100) {
                this.setFocusElementById(this.getInputId, showKeyboard, autoSelectAll, delay)
            },
        }
    }
</script>

<style scoped>
.barcode-input::selection {
    color: black;
    background: #cce3ff;
}
</style>
