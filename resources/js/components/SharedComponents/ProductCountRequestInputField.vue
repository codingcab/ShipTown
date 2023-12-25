<template>
    <div>
        <barcode-input-field :placeholder="placeholder"
                             @barcodeScanned="barcodeScanned"
        />

        <b-modal @ok="submitStocktake" id="quantity-request-modal" scrollable centered no-fade hide-header
                 @shown="modalShown"
                 @hidden="setFocusElementById('barcodeInput')"
        >
            <template v-if="product">
                <div class="col-sm-12 col-lg-12">
                    <product-info-card :product="product"></product-info-card>
                </div>
                <div class="col-sm-12 col-lg-12 text-right mt-3 mb-3">
                    <number-card label="price" :number="product['prices'][this.currentUser()['warehouse']['code']]['price']" class="float-left"></number-card>
                    <number-card label="in stock" :number="product['inventory'][this.currentUser()['warehouse']['code']]['quantity']"></number-card>
<!--                    <number-card label="requested" :number="requestedQuantity"></number-card>-->

                    <slot name="custom_cards"></slot>
                </div>
                <div class="row">
                    <div class="col-12">
                        <input class="form-control" :placeholder="'quantity to add'" :class="{ 'border-danger': this.quantity < 0, 'border-success': this.quantity > 0}"
                               id="quantity-request-input"
                               dusk="quantity-request-input"
                               v-model="quantity"
                               type="number"
                               inputmode="numeric"
                               @keyup.enter="submitStocktake"
                        />
                    </div>
                </div>
            </template>
        </b-modal>

    </div>
</template>

<script>
    import loadingOverlay from '../../mixins/loading-overlay';
    import BarcodeInputField from "./../SharedComponents/BarcodeInputField";
    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";
    import url from "../../mixins/url";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            BarcodeInputField
        },

        props: {
            placeholder: '',
            requestedQuantity: null,
        },

        data: function() {
            return {
                product: null,
                quantity: null,
                recentStocktakes: [],
                stocktakeSuggestions: [],
            };
        },

        methods: {
            modalShown() {
                this.setFocusElementById(100, 'quantity-request-input', true, false);
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

                        this.$bvModal.show('quantity-request-modal');
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    });
            },

            submitStocktake: function () {
                this.$bvModal.hide('quantity-request-modal');
                if (this.quantity === null) {
                    return;
                }

                if (this.quantity === "") {
                    return;
                }

                const data = {
                    'product_sku': this.product['sku'],
                    'product_name': this.product['name'],
                    'product_id': this.product['id'],
                    'quantity':  Number(this.quantity),
                };

                this.$emit('quantityRequestResponse', data);
            },
        },
    }
</script>

<style lang="scss" scoped>
</style>
