<template>
    <div>
        <template v-if="getUrlParameter('hide_nav_bar', false) === false">
            <div class="row mb-1 p-1 sticky-top bg-light">
                <div class="flex-fill">
                    <barcode-input-field :input_id="'barcode-input'"  :url_param_name="'search'" @barcodeScanned="findText" placeholder="Search orders using number, sku, alias or command" ref="barcode"/>
                </div>

                <button v-b-modal="'quick-actions-modal'" type="button" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
            </div>
        </template>

        <div class="row pl-2 p-0">
            <div class="col-12 text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                Orders
            </div>
        </div>

        <div class="row" v-if="orders.length === 0 && !isLoading">
            <div class="col">
                <div class="alert alert-info" role="alert">
                    No orders found.
                </div>
            </div>
        </div>

        <template v-for="order in orders">
            <div class="row">
                <div class="col">
                    <order-card :order="order" :expanded="orders.length === 1"/>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="quick-actions-modal" no-fade hide-header
                 @hidden="setFocusElementById('barcode-input')">
            <stocktake-input v-bind:auto-focus-after="100" ></stocktake-input>
            <hr>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    Cancel
                </b-button>
                <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    OK
                </b-button>
            </template>
        </b-modal>

    </div>
</template>

<script>
    import loadingOverlay from '../mixins/loading-overlay';
    import OrderCard from "./Orders/OrderCard";
    import url from "../mixins/url";
    import BarcodeInputField from "./SharedComponents/BarcodeInputField";
    import api from "../mixins/api";
    import helpers from "../mixins/helpers";
    import Vue from "vue";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            'order-card': OrderCard,
            'barcode-input-field': BarcodeInputField,
        },

        data: function() {
            return {
                per_page: 20,
                pagesLoaded: 0,
                reachedEnd: false,
                searchText: '',
                orders: [],
            };
        },

        mounted() {
            this.getUrlFilterOrSet('created_between', '-4 months,now');
            this.getUrlFilterOrSet('warehouse_id', Vue.prototype.$currentUser['warehouse_id']);
            this.per_page = this.getUrlParameter('per_page', 20);

            window.onscroll = () => this.loadMore();

            this.reloadOrders();
        },

        methods: {
            findText(param) {
                this.setUrlParameter('search', param);
                this.orders = [];
                this.reloadOrders();
            },

            reloadOrders(e) {
                this.loadOrderList();
            },

            loadOrderList: function(page = 1) {
                if (page === 1) {
                    this.orders = [];
                }

                this.showLoading();

                this.page = page;

                const params = {
                    'filter[status]': this.getUrlParameter('status'),
                    'filter[search]': this.getUrlParameter('search'),
                    'filter[has_tags]': this.getUrlParameter('has_tags'),
                    'filter[without_tags]': this.getUrlParameter('without_tags'),
                    'filter[age_in_days]': this.getUrlParameter('age_in_days'),
                    'filter[is_active]': this.getUrlParameter('is_active'),
                    'filter[is_on_hold]': this.getUrlParameter('is_on_hold'),
                    'filter[packed_between]': this.getUrlParameter('packed_between'),
                    'filter[packer_user_id]': this.getUrlParameter('packer_user_id'),
                    'filter[shipping_method_code]': this.getUrlParameter('shipping_method'),
                    'filter[created_between]': this.getUrlParameter('created_between'),
                    'sort': this.getUrlParameter('sort','-order_placed_at'),
                    'per_page': this.per_page,
                    'include': 'order_comments,order_comments.user,packer,order_products_totals,tags,shipping_address,order_shipments',
                    'page': page,
                };

                this.apiGetOrders(params)
                    .then(({ data }) => {
                        this.orders = this.orders.concat(data.data);
                        this.reachedEnd = data.data.length === 0;
                        this.pagesLoaded = page;
                    })
                    .catch((error) => {
                        this.displayApiCallError(error)
                    })
                    .finally(() => {
                        this.hideLoading();
                    });

                return this;
            },

            loadMore: function () {
                if (this.isMoreThanPercentageScrolled(70) && this.hasMorePagesToLoad() && !this.isLoading) {
                    this.loadOrderList(++this.pagesLoaded);
                }
            },

            hasMorePagesToLoad: function () {
                return this.reachedEnd === false;
            },
        },
    }
</script>

<style lang="scss" scoped>
    .row {
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
