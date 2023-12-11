<template>
    <div>
        <template v-if="getUrlParameter('hide_nav_bar', false) === false">
            <div class="row mb-1 pb-2 p-1 sticky-top bg-light">
                <div class="flex-fill">
                    <barcode-input-field placeholder="Search products using name, sku, alias or command"
                                         ref="barcode"
                                         :url_param_name="'search'"
                                         @refreshRequest="reloadProductList"
                                         @barcodeScanned="findText"
                    />
                </div>

                <button type="button" v-b-modal="'quick-actions-modal'" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
            </div>
        </template>

        <div class="row pl-2 p-0">
            <div class="col-12 text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                Products BLUE
            </div>
        </div>

        <template  v-if="isLoading === false && products !== null && products.length === 0" >
            <div class="row">
                <div class="col">
                    <div class="alert alert-info" role="alert">
                        No products found.
                    </div>
                </div>
            </div>
        </template>

        <template v-if="products" v-for="product in products">
            <div class="row p-1">
                <div class="col">
                    <product-card :product="product" :expanded="products.length === 1"/>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="quick-actions-modal" no-fade hide-footer hide-header
                 @hidden="setFocusElementById('barcodeInput', true, true)">
            <stocktake-input></stocktake-input>
            <hr>
        </b-modal>

    </div>
</template>

<script>
    import loadingOverlay from '../mixins/loading-overlay';
    import ProductCard from "./Products/ProductCard";
    import BarcodeInputField from "./SharedComponents/BarcodeInputField";
    import url from "../mixins/url";
    import api from "../mixins/api";
    import helpers from "../mixins/helpers";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            ProductCard,
            BarcodeInputField
        },

        data: function() {
            return {
                pagesLoadedCount: 1,
                reachedEnd: false,
                products: null,
                per_page: 20,
                scroll_percentage: 70,
            };
        },

        mounted() {
            window.onscroll = () => this.loadMore();

            this.reloadProductList();
        },

        methods: {
            findText(search) {
                this.setUrlParameter('search', search);
                this.reloadProductList();
            },

            reloadProductList() {
                this.products = null;

                if (this.getUrlParameter('search')) {
                    this.findProductsWithExactSku();
                }

                this.findProductsContainingSearchText();
            },

            findProductsContainingSearchText: function(page = 1) {
                this.showLoading();

                const params = { ...this.$router.currentRoute.query};
                params['filter[search]'] = this.getUrlParameter('sku') ?? this.getUrlParameter('search');
                params['filter[has_tags]'] = this.getUrlParameter('has_tags');
                params['filter[without_tags]'] = this.getUrlParameter('without_tags');
                params['include'] = 'inventory,tags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics';
                params['per_page'] = this.per_page;
                params['page'] = page;
                params['sort'] = this.getUrlParameter('sort', '-quantity');

                this.apiGetProducts(params)
                    .then(({data}) => {
                        this.products = this.products ? this.products.concat(data.data) : data.data
                        this.reachedEnd = data.data.length === 0;
                        this.pagesLoadedCount = page;

                        this.scroll_percentage = (1 - this.per_page  / this.products.length) * 100;
                        this.scroll_percentage = Math.max(this.scroll_percentage, 70);
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
                return this;
            },

            findProductsWithExactSku: function() {
                this.showLoading();

                const params = { ...this.$router.currentRoute.query};
                params['filter[sku]'] = this.getUrlParameter('sku') ?? this.getUrlParameter('search');
                params['include'] = 'inventory,tags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics';

                this.apiGetProducts(params)
                    .then(({data}) => {
                        if (data.data.length === 0) {
                            return;
                        }

                        this.products = this.products ? this.products.concat(data.data) : data.data
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            loadMore: function () {
                if (this.isMoreThanPercentageScrolled(this.scroll_percentage) && this.hasMorePagesToLoad() && !this.isLoading) {
                    this.findProductsContainingSearchText(++this.pagesLoadedCount);
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
