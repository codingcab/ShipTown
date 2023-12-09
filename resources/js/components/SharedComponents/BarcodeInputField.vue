<template>
    <div>
        <input class="form-control"
               autocomplete="off"
               enterkeyhint="done"
               :placeholder="placeholder"
               ref="barcode"
               id="barcodeInput"
               dusk="barcode-input-field"
               v-model.trim="barcode"
               @focus="simulateSelectAll"
               @keyup.enter="barcodeScanned(barcode)"
        />

      <b-modal :id="getModalID" @submit="updateShelfLocation" @shown="updateShelfLocationShown" @hidden="updateShelfLocationHidden" scrollable no-fade hide-header>
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
            url_param_name: null,
            placeholder: '',
        },


        computed: {
            getModalID() {
                return `set-shelf-location-command-modal-${Math.floor(Math.random() * 10000000)}`;
            }
        },

        data: function() {
            return {
                currentLocation: '',
                barcode: '',
                command: ['',''],

                shelfLocationModalCommandScanCount: 0,
                shelfLocationModalShowing: false,
                shelfLocationModalContinuesScan: false,
            }
        },


        mounted() {
            this.resetInputValue();
            this.setFocusOnBarcodeInput(1000);
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
                    return;
                }

                if(this.url_param_name) {
                    this.setUrlParameter(this.url_param_name, barcode);
                }

                this.$emit('barcodeScanned', barcode);

                this.setFocusOnBarcodeInput();
                this.simulateSelectAll();
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

            setFocusOnBarcodeInput(delay = 1) {
                setTimeout(() => {
                    this.setFocus(document.getElementById('barcodeInput'), true,true)
                }, delay);
            },
        }
    }
</script>

<style scoped>

</style>
