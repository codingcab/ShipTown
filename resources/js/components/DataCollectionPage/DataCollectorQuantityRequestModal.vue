<template>
    <div>
        <b-modal @ok="submitCount" id="data-collector-quantity-request-modal" scrollable no-fade hide-header
                 @shown="modalShown"
                 @hidden="onHidden"
        >
            <div class="row">
                <div class="col-lg-6">
                    <product-info-card :product="product"></product-info-card>
                </div>
                <div class="col">
                    <div class="row text-right mt-0 mb-3">
                        <div class="col">
                            <number-card label="price" :number="prices ? prices['price'] : 0"></number-card>
                            <number-card :class="{ 'bg-warning': productNew && dataCollectionRecord && dataCollectionRecord['quantity_requested'] >= productNew['inventory'][this.currentUser()['warehouse']['code']]['quantity']}" label="in stock" :number="productNew ? productNew['inventory'][this.currentUser()['warehouse']['code']]['quantity'] : 0"></number-card>
                            <text-card label="shelf" :text="productNew ? productNew['inventory'][this.currentUser()['warehouse']['code']]['shelf_location'] : 0"></text-card>
                        </div>
                    </div>
                    <div class="row-col text-right mt-3 mb-3">
                        <number-card label="requested" :number="dataCollectionRecord ? dataCollectionRecord['quantity_requested'] : 0"></number-card>
                        <number-card :class="{ 'bg-warning': productNew && dataCollectionRecord && dataCollectionRecord['quantity_scanned'] > dataCollectionRecord['quantity_requested']}" label="scanned" :number="dataCollectionRecord ? dataCollectionRecord['quantity_scanned'] : 0"></number-card>
                        <number-card :class="{ 'bg-warning': productNew && dataCollectionRecord && dataCollectionRecord['quantity_scanned'] > 0 && dataCollectionRecord['quantity_to_scan'] > 0}" label="to scan" :number="dataCollectionRecord ? dataCollectionRecord['quantity_to_scan'] : 0"></number-card>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <input class="form-control" :placeholder="'quantity to add'" :class="{ 'border-danger': this.quantity < 0, 'border-success': this.quantity > 0}"
                           id="data-collection-record-quantity-request-input"
                           name="data-collection-record-quantity-request-input"
                           ref="data-collection-record-quantity-request-input"
                           dusk="data-collection-record-quantity-request-input"
                           v-model="quantity"
                           type="number"
                           inputmode="numeric"
                           @keyup.enter="submitCount"
                    />
                </div>
            </div>

            <template #modal-footer="{ ok, cancel }" class="text-left">
                <b-button style="width: 75px" class="btn mr-auto" variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button style="width: 75px" class="btn" variant="primary" @click="ok()">OK</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import loadingOverlay from '../../mixins/loading-overlay';
    import BarcodeInputField from "../SharedComponents/BarcodeInputField";
    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";
    import url from "../../mixins/url";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            BarcodeInputField
        },

        props: {
            dataCollection: null,
            dataCollectionRecord: null,
            product: null,
            placeholder: '',
            requestedQuantity: null,
        },

        data: function() {
            return {
                productNew: null,
                quantity: null,
                prices: null,
            };
        },

        mounted() {
            this.$root.$on('barcodeScanned', function () {
                this.$bvModal.show('data-collector-quantity-request-modal');
            });
        },

        watch: {
            product: function (newProduct, oldDataCollectionRecord) {
                if (newProduct === null) {
                    this.prices = null;
                    return;
                }

                this.apiGetProducts({
                    'filter[id]': newProduct['id'],
                    'include': 'prices,inventory',
                }).then(response => {
                    this.productNew = response.data.data[0];
                    this.prices = response.data.data[0]['prices'][this.currentUser()['warehouse']['code']];
                });
            }
        },

        methods: {
            onHidden() {
                this.prices = null;
                this.quantity = null;
                this.$emit('hidden');
            },

            modalShown() {
                this.setFocusElementById('data-collection-record-quantity-request-input', true, false);
            },

            barcodeScanned: async function (barcode) {
                if (barcode === null) {
                    return;
                }

                if (barcode === "") {
                    return;
                }

                this.quantity = null;

                const params = {
                    'filter[sku_or_alias]': barcode,
                    'include': 'inventory,prices',
                };

                this.apiGetProducts(params)
                    .then(response => {
                        if (response.data.data.length === 0) {
                            this.notifyError('No product found with barcode: ' + barcode);
                            return;
                        }
                        this.product = response.data.data[0];

                        this.$bvModal.show('data-collector-quantity-request-modal');
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    });
            },

            submitCount: function () {
                if (this.quantity === null) {
                    return;
                }

                if (this.quantity === "") {
                    return;
                }

                if (this.quantity.length > 7) {
                    this.notifyError('Quantity is too large', {'timeout': 5000});
                    this.setFocusElementById('data-collection-record-quantity-request-input', true, false);
                    return;
                }

                this.$bvModal.hide('data-collector-quantity-request-modal');

                const data = {
                    'data_collection_id': this.dataCollection['id'],
                    'product_id': this.product['id'],
                    'quantity_scanned':  Number(this.quantity),
                };

                this.$emit('productCountCollected', data);
            },
        },
    }
</script>

<style lang="scss" scoped>
</style>
