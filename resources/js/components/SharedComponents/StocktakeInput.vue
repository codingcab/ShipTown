<template>
    <div>
        <input :id="input_id"
               placeholder="Scan SKU to stocktake"
               type="text"
               class="form-control"
               @keyup.enter="showStocktakeModal"
               autocomplete="off"
               enterkeyhint="done"
        >

        <b-modal @ok="submitStocktake" :id="modal_name" scrollable no-fade hide-header
                 @shown="setFocusElementById(100, 'quantity-request-input', true, false)"
                 @hidden="setFocusElementById(100, input_id, true, true)"
        >
            <template v-if="inventory">
                <product-info-card :product="inventory.product"></product-info-card>
                <div class="small" v-bind:class="{ 'bg-warning': inventory['last_counted_at'] !== null && isCountedRecently(inventory['last_counted_at'], 28)}">last counted: <strong>{{ formatDateTime(inventory['last_counted_at']) }}</strong></div>
                <div class="small">stock: {{ inventory.quantity }}</div>
                <div class="row mt-2">
                    <div class="col-6">
                        <label class="small" for="adjust-by-request-input">adjust by</label>
                        <input class="form-control " :placeholder="'Adjust by'" :class="{ 'border-danger': this.adjustByQuantity < 0, 'border-success': this.adjustByQuantity > 0}"
                               id="adjust-by-request-input"
                               dusk="adjust-by-request-input"
                               v-model="adjustByQuantity"
                               type="number"
                               inputmode="numeric"
                               @keyup.enter="submitStocktake"
                        />
                    </div>
                    <div class="col-6">
                        <label class="small" for="adjust-by-request-input">new quantity</label>
                        <input class="form-control" :placeholder="'New quantity'" :class="{ 'border-danger': this.adjustByQuantity < 0, 'border-success': this.adjustByQuantity > 0}"
                               id="quantity-request-input"
                               dusk="quantity-request-input"
                               ref="quantity-request-input"
                               name="quantity-request-input"
                               v-model="newQuantity"
                               type="number"
                               inputmode="numeric"
                               @keyup.enter="submitStocktake"
                        />
                    </div>
                </div>
                <input class="form-control mt-2" :placeholder="'Reason'" disabled
                       id="reason-request-input"
                       dusk="reason-request-input"
                       v-model="description"
                       type="text"
                       @keyup.enter="submitStocktake"
                />
            </template>
            <template #modal-footer="{ ok, cancel }" class="text-left">
                <b-button style="width: 80px" class="btn mr-auto" variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button style="width: 80px" class="btn" variant="primary" @click="ok()">Update</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";
    import url from "../../mixins/url";
    import moment from "moment";

    export default {
        mixins: [url, api, helpers],

        data: function() {
            return {
                modal_name: 'stocktake-modal',
                input_id: 'stocktake-input',
                inventory: null,
                adjustByQuantity: null,
                newQuantity: null,
                description: 'stocktake',
                recentStocktakes: [],
                stocktakeSuggestions: [],
            };
        },

        watch: {
            adjustByQuantity() {
                let adjustByValue = 0;
                let newValue = null;

                if (this.adjustByQuantity === null) {
                    if (this.newQuantity === '') {
                        return;
                    }
                    if (this.newQuantity === null) {
                        return;
                    }
                } else if (this.adjustByQuantity === '') {
                    if (this.newQuantity === '') {
                        return;
                    }
                } else {
                    adjustByValue = this.adjustByQuantity;
                }

                newValue = Number(this.inventory.quantity) + Number(adjustByValue);

                if (this.newQuantity !== newValue) {
                    this.newQuantity = newValue;
                }
            },

            newQuantity() {
                let newValue = 0;

                if (this.newQuantity === null) {
                    this.adjustByQuantity = null;
                    return;
                } else if (this.newQuantity === '') {
                    this.adjustByQuantity = null;
                    return;
                } else {
                    newValue = this.newQuantity - this.inventory.quantity;
                }

                if (this.adjustByQuantity !== newValue) {
                    this.adjustByQuantity = newValue === 0 ? '' : newValue;
                }
            }
        },

        mounted() {
            if (! this.currentUser()['warehouse_id']) {
                this.$snotify.error('You do not have warehouse assigned. Please contact administrator', {timeout: 50000});
                this.$bvModal.hide(this.modal_name);
            }

            this.setFocusElementById(100, this.input_id, true, true);
            this.focusAndOpenKeyboard(this.input_id, 100);
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
            isCountedRecently(last_counted_at, days) {
                const minDateAllowed = moment().subtract(days, 'days');

                return moment(last_counted_at).isAfter(minDateAllowed);
            },

            showStocktakeModal(event) {
                this.stocktakeSKU(event.target.value);
            },

            stocktakeSKU: async function (sku) {
                if (sku === null) {
                    return;
                }

                if (sku === "") {
                    return;
                }

                this.inventory = null;
                this.newQuantity = null;

                const params = {
                    'filter[sku_or_alias]': sku,
                    'filter[warehouse_id]': this.currentUser()['warehouse_id'],
                    'include': 'product'
                }

                this.apiInventoryGet(params)
                    .then(e => {
                        if (e.data.meta.total === 0) {
                            this.notifyError('Product not found - "' + sku + '"');
                            this.setFocusElementById(100, 'stocktake-input', true, true);
                            return;
                        }

                        this.inventory = e.data.data[0];
                        this.newQuantity = this.inventory.quantity;

                        this.$bvModal.show(this.modal_name);
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                        this.setFocusElementById(100, 'stocktake-input', true, true);
                    });
            },

            submitStocktake: function () {
                if (this.newQuantity === null) {
                    return;
                }

                if (this.newQuantity === "") {
                    return;
                }

                if (this.newQuantity < 0) {
                    this.notifyError('Minus quantity not allowed');
                    this.setFocusElementById(100, 'quantity-request-input', true, false)
                    return;
                }

                if (Number(this.newQuantity) > 99999) {
                    this.notifyError('incorrect quantity entered');
                    this.setFocusElementById(100, 'quantity-request-input', true, false)
                    return;
                }

                this.$bvModal.hide(this.modal_name);

                const stocktakeData = {
                    'product_id': this.inventory['product_id'],
                    'warehouse_id': this.currentUser()['warehouse_id'],
                    'new_quantity': this.newQuantity,
                };

                this.apiPostStocktakes(stocktakeData)
                    .then(() => {
                        this.notifySuccess('Stocktake updated');
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    })
                    .finally(() => {
                        this.$emit('stocktakeSubmitted');
                    });
            },
        },
    }
</script>

<style lang="scss" scoped>
</style>
