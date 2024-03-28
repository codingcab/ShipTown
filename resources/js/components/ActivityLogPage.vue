<template>
    <div>
        <template v-if="getUrlParameter('hide_nav_bar', false) === false">
            <div class="row mb-3 pl-1 pr-1">
                <div class="flex-fill">
                    <barcode-input-field placeholder="Search activity"
                                         ref="barcode"
                                         @refreshRequest="reloadProducts"
                                         @barcodeScanned="findText"
                    />
                </div>

                <button disabled type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#filterConfigurationModal"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
            </div>
        </template>


        <div class="row pl-0 p-0">
            <div class="col-12 col-md-6 col-lg-6 text-nowrap text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                REPORTS > ATIVITY LOG
            </div>
            <div class="col-12 col-md-6 col-lg-6 text-nowrap">
                <date-selector-widget :dates="{'url_param_name': 'filter[created_at_between]'}"></date-selector-widget>
            </div>
        </div>

        <template  v-if="activityLog.length === 0 && !isLoading" >
            <div class="row mt-3">
                <div class="col">
                    <div class="alert alert-info" role="alert">
                        No activities found
                    </div>
                </div>
            </div>
        </template>

        <table v-if="activityLog.length > 0" class="small table-responsive text-nowrap">
            <template v-for="activity in activityLog">
                <tr>
                    <td class="pr-2">{{ formatDateTime(activity['created_at'], 'YYYY MMM D H:mm:ss') }}</td>
                    <td class="pr-2">{{ activity['causer_id'] ? activity['causer']['name'] : 'AutoPilot' }}</td>
                    <td class="pr-2">{{ activity['description'] }}</td>
                    <td class="pr-2">{{ activity['subject_type'] }}</td>
                    <td class="pr-2">{{ activity['subject_id'] }}</td>
                </tr>
            </template>
        </table>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 100px"></div>
            </div>
        </div>

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
                lastPageLoaded: 1,
                lastPage: 1,

                products: [],
                activityLog: [],
            };
        },

        mounted() {
            // window.onscroll = () => this.loadMore();

            this.loadActivityLog(1);
        },

        methods: {
            loadActivityLog: function () {
                this.statusMessageActivity = "Loading activities ...";
                let params = this.$router.currentRoute.query;

                params['include'] = 'causer';

                this.apiGetActivityLog(params)
                    .then(({data}) => {
                        this.activityLog = data.data
                    }).catch(error => {
                        this.displayApiCallError(error);
                    });
            },

            findText(search) {
                this.setUrlParameter('filter[search]', search);
                this.loadActivityLog();
            },

            reloadProducts() {
                this.products = [];
                this.loadProductList();
            },

            loadProductList: function(page = 1) {
                this.showLoading();

                const params = {
                    'filter[sku]': this.getUrlParameter('sku'),
                    'filter[search]': this.getUrlParameter('search'),
                    'filter[has_tags]': this.getUrlParameter('has_tags'),
                    'filter[without_tags]': this.getUrlParameter('without_tags'),
                    'sort': this.getUrlParameter('sort', '-quantity'),
                    'include': 'inventory,tags,prices,aliases,inventory.warehouse',
                    'per_page': this.getUrlParameter('per_page', 25),
                    'page': page
                }

                this.apiGetProducts(params)
                    .then(({data}) => {
                        if (page === 1) {
                            this.products = [];
                        }
                        this.products = this.products.concat(data.data);
                        this.lastPage = data.meta.last_page;
                        this.lastPageLoaded = page;
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
                return this;
            },

            loadMore: function () {
                if (this.isMoreThanPercentageScrolled(70) && this.hasMorePagesToLoad() && !this.isLoading) {
                    this.loadProductList(++this.lastPageLoaded);
                }
            },

            hasMorePagesToLoad: function () {
                return this.lastPage > this.lastPageLoaded;
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
