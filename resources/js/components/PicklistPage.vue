<template>
    <div>
        <div class="row mb-1 pb-2 pt-1 sticky-top bg-light">
            <div class="col-8 col-lg-10">
                <div class="pl-1 pr-1">
                    <barcode-input-field placeholder="Enter sku or alias to pick products"
                                         ref="barcode"
                                         :url_param_name="'search'"
                                         @barcodeScanned="pickByBarcode"
                    />
                </div>
            </div>
            <div class="col pr-2">
                <div class="row">
                    <div class="col">
                        <input ref="current_location" class="form-control w-100" placeholder="Current shelf"
                               v-model="current_shelf_location"
                               @keyup.enter="reloadPicks()"/>
                    </div>
                    <button v-b-modal="'quick-actions-modal'" type="button" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
                </div>
            </div>
        </div>

        <div class="row pl-2 p-1 font-weight-bold text-uppercase small text-secondary">
            <div class="col-6 text-left text-nowrap">
                TOOLS > PICKLIST
            </div>
            <div class="col-6 text-right text-nowrap">
                <!--                        -->
            </div>
        </div>
        <div v-if="picklist !== null && picklist.length === 0" class="row" >
            <div class="col">
                <div class="alert alert-info" role="alert">
                    No picks found
                </div>
            </div>
        </div>

        <div v-else>
            <template v-for="pick in picklist">
                <pick-card :pick="pick" :id="`pick-card-${ picklist.indexOf(pick)}`" @swipeRight="pickAll" @swipeLeft="partialPickSwiped"/>
            </template>
        </div>

        <div class="row" v-if="isLoading">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="quick-actions-modal" no-fade hide-footer hide-header
                 @shown="setFocusElementById(100,'stocktake-input', true, true)"
                 @hidden="setFocusOnBarcodeInput(100)">
            <stocktake-input></stocktake-input>
        </b-modal>

    </div>
</template>

<script>
import PickCard from "./Picks/PickCard.vue";
import loadingOverlay from '../mixins/loading-overlay';
import BarcodeInputField from "./SharedComponents/BarcodeInputField";
import PicklistConfigurationModalNew from './Picks/ConfigurationModal.vue';

import url from "../mixins/url";
import beep from "../mixins/beep";
import api from "../mixins/api";
import Vue from "vue";
import helpers from "../mixins/helpers";

export default {
    name: "PicksTable",

    mixins: [loadingOverlay, url, beep, api, BarcodeInputField, helpers],

    components: {
        'pick-card': PickCard,
        'barcode-input-field': BarcodeInputField,
        'picklist-configuration-modal': PicklistConfigurationModalNew   ,
    },

    mounted() {
        if (Vue.prototype.$currentUser['warehouse_id']) {
            this.setUrlParameter('warehouse_id', Vue.prototype.$currentUser['warehouse_id']);
            this.reloadPicks();
            return;
        }

        this.$snotify.error('You do not have warehouse assigned. Please contact administrator', {timeout: 50000});
    },

    watch: {
        picklist: {
            handler() {
                if (this.picklist.length === 0 && this.isLoading === false ) {
                    this.reloadPicks();
                }

            },
            deep: true
        },
        current_shelf_location() {
            this.setUrlParameter('current_shelf_location', this.current_shelf_location)
        }
    },

    data: function() {
        return {
            picklist: null,
            current_shelf_location: ''
        };
    },

    methods: {
        pickByBarcode(barcode) {
            if (barcode === '') {
                return;
            }

            let pickItem = this.findPickItem(barcode);

            if(!pickItem) {
                this.$snotify.error(`"${barcode}" not found on picklist!`);
                this.errorBeep();
                return;
            }

            this.pickAll(pickItem);
        },

        findPickItem: function (barcode) {
            if(barcode === '') {
                return null;
            }

            for (let element of this.picklist) {

                if(element['sku_ordered'] === barcode) {
                    return element;
                }

                if(element['product'] === null){
                    continue;
                }

                if(element['product']['sku'] === barcode) {
                    return element;
                }

                if(typeof element['product']['aliases'] === 'undefined') {
                    continue;
                }

                for(let alias of element['product']['aliases']) {
                    if(alias['alias'] === barcode){
                        return element;
                    }
                }


            }
            return null;
        },

        reloadPicks() {
            this.showLoading();

            this.setFocusOnBarcodeInput(300);

            const params = {
                'include': 'product,product.aliases',
                'sort': 'inventory_source_shelf_location,sku_ordered',
                'per_page': this.getUrlParameter('per_page', 3),
                'filter[in_stock_only]': this.getUrlParameter('in_stock_only', true),
                'filter[warehouse_id]': this.getUrlParameter('warehouse_id'),
                'filter[current_shelf_location]': this.getUrlParameter('current_shelf_location'),
                'filter[order.status_code]': this.getUrlParameter('order.status_code'),
                'filter[created_between]': this.getUrlParameter('created_between'),
            };

            return this.apiGetPicklist(params)
                .then( ({data}) => {
                    this.picklist = data.data;
                })
                .catch( error => {
                    this.$snotify.error('Action failed (Http code  '+ error.response.status+')');
                })
                .finally( () => {
                    this.hideLoading();
                });
        },

        postPick(pick, quantity_picked, quantity_skipped_picking) {
            let data = {
                'quantity_picked': quantity_picked,
                'quantity_skipped_picking': quantity_skipped_picking,
                'order_product_ids': pick['order_product_ids'],
            };
            return this.apiPostPicklistPick(data)
                .catch( error => {
                    this.$snotify.error('Action failed (Http code  '+ error.response.status +')');
                });
        },

        postPickUpdate(pick, quantity_picked) {
            return this.apiPostPicklistPick({
                    'quantity_picked': quantity_picked,
                    'order_product_ids': pick['order_product_ids'],
                })
                .catch( error => {
                    this.$snotify.error('Action failed (Http code  '+ error.response.status+')');
                });
        },

        deletePick(pick) {
            return this.apiDeletePick(pick['id'])
                .catch( error => {
                    this.$snotify.error('Action failed (Http code  '+ error.response.status+')');
                });
        },

        removeFromPicklist: function (pick) {
            this.picklist.splice(this.picklist.indexOf(pick), 1);
        },

        pickAll(pick) {
            this.current_shelf_location = pick['inventory_source_shelf_location'];
            this.postPick(pick, pick['quantity_required'], 0)
                .then( (response) => {
                    this.displayPickedNotification(response.data['data'][0], pick['quantity_required']);
                    this.beep();
                    this.removeFromPicklist(pick);
                    this.reloadPicks();
                });
            this.setFocusOnBarcodeInput();
        },

        skipPick: function (pick) {
            this.postPick(pick, 0, pick['quantity_required'])
                .then(() => {
                    this.$snotify.warning('Pick deleted', {
                        timeout: 1500,
                        icon: false,
                    });
                    this.warningBeep();
                })
                .then(() => {
                    this.reloadPicks();
                })
        },

        makePartialPick: function (pick, quantity) {
            this.postPickUpdate(pick, quantity)
                .then(() => {
                    this.displayPickedNotification(pick, quantity);
                    this.beep();
                    this.removeFromPicklist(pick);
                    this.reloadPicks();
                });
        },

        partialPickSwiped(pick) {
            this.$snotify.prompt('Partial pick', {
                placeholder: 'Enter quantity picked:',
                position: 'centerCenter',
                icon: false,
                buttons: [
                    {
                        text: 'Delete Pick',
                        action: (toast) => {
                            this.$snotify.remove(toast.id)
                            this.skipPick(pick);
                            this.setFocusOnBarcodeInput();
                        }
                    },
                    {
                        text: 'Pick',
                        action: (toast) => {
                            if ( isNaN(toast.value) || (toast.value <= 0) || (toast.value > Number(pick['quantity_required'])) ) {
                                toast.valid = false;
                                return false;
                            }

                            this.$snotify.remove(toast.id);
                            this.makePartialPick(pick, toast.value);
                            this.setFocusOnBarcodeInput();
                        }
                    },
                    {
                        text: 'Cancel',
                        action: (toast) => {
                            this.$snotify.remove(toast.id);
                            this.setFocusOnBarcodeInput();
                        }
                    },
                ],
            });
        },

        partialPick(pickedItem) {
            this.$snotify.prompt('Partial pick', {
                placeholder: 'Enter quantity picked:',
                position: 'centerCenter',
                icon: false,
                buttons: [
                    {
                        text: 'Pick',
                        action: (toast) => {
                            if ( isNaN(toast.value) || (toast.value <= 0)) {
                                toast.valid = false;
                                return false;
                            }
                            this.picklist.splice(this.picklist.indexOf(pickedItem), 1);
                            this.$snotify.remove(toast.id);
                            this.pick(pickedItem, toast.value)
                                .then(() => {

                                    this.picklist = [];
                                });

                        }
                    },
                    {
                        text: 'Cancel',
                        action: (toast) => {
                            // this.picklist.unshift(pickedItem);
                            this.$snotify.remove(toast.id) // default
                        }
                    },
                ],
            });
        },

        undoPick(pick) {
            this.showLoading();
            this.apiDeletePick(pick['id'])
                .then( () => {
                    this.$snotify.warning('Action reverted', {
                        timeout: 1500,
                        icon: false,
                    });
                    this.warningBeep();
                })
                .catch( error => {
                    this.displayApiCallError(error);
                })
                .then( () => {
                    this.reloadPicks();
                    this.hideLoading();
                });
        },

        displayPickedNotification: function (pick, quantity) {
            const msg =  Math.ceil(quantity) + ' x ' + pick['sku_ordered'] + ' picked';
            this.$snotify.confirm(msg, {
                timeout: 5000,
                showProgressBar: false,
                pauseOnHover: true,
                icon: false,
                buttons: [
                    {
                        text: 'Undo',
                        action: (toast) => {
                            this.$snotify.remove(toast.id);
                            this.undoPick(pick, quantity)
                        }
                    }
                ]
            });
        },
    },
}
</script>

<style scoped>

</style>
