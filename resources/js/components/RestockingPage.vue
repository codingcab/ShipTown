<template>
    <div class="container dashboard-widgets">

        <div class="row mb-3 pl-1 pr-1" v-if="currentUser['warehouse'] !== null">
            <div class="flex-fill">
                <barcode-input-field placeholder='Search products using name, sku, alias or command'
                                     :url_param_name="'filter[search]'"
                                     @barcodeScanned="findText"></barcode-input-field>
            </div>

            <button v-b-modal="'configuration-modal'"  id="config-button" type="button" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
        </div>

        <b-modal id="configuration-modal" centered no-fade hide-footer title="Data Collection">
            <button type="button" @click.prevent="downloadFileAndHideModal" class="col btn mb-1 btn-primary">Download</button>
        </b-modal>

        <template v-for="record in data">
            <div class="row mb-3">
                <div class="col ml-0 pl-0">
                    <div class="card ml-0 pl-0">
                        <div class="card-body pt-2 pl-2">
                            <div class="row mt-0 small">
                                <div class="col-lg-6">
                                    <div class="text-primary h5">{{ record['product_name'] }}</div>
                                    <div>
                                        sku: <b><a target="_blank" :href="'/products?hide_nav_bar=true&search=' + record['product_sku']">{{ record['product_sku'] }}</a></b>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="row pt-1">
                                        <div class="col-6">
                                            <div >
                                                incoming: <b>{{ record['quantity_incoming'] }}</b>
                                            </div>
                                            <div >
                                                warehouse: <b>{{ record['warehouse_quantity'] }}</b>
                                            </div>
                                            <div :class=" record['reorder_point'] <= 0 ? 'bg-warning' : ''">
                                                reorder_point: <b>{{ record['reorder_point'] }}</b>
                                            </div>
                                            <div :class="record['reorder_point'] <= 0 ? 'bg-warning' : ''">
                                                restock_level: <b>{{ record['restock_level'] }}</b>
                                            </div>
                                            <div>
                                                warehouse_code: <b>{{ record['warehouse_code'] }}</b>
                                            </div>
                                        </div>
                                        <div :class="'col-3 text-center' + record['quantity_available'] <= 0 ? 'bg-warning' : '' ">
                                            <small>available</small>
                                            <h3>{{ record['quantity_available'] }}</h3>
                                        </div>
                                        <div class="col-3 text-center">
                                            <small>required</small>
                                            <h3>{{ record['quantity_required'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 100px"></div>
            </div>
        </div>
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

        props: {
            initial_data: null,
        },

        components: {
        },

        data: function() {
            return {
                data: [],
                reachedEnd: false,
                pagesLoaded: 0,
            };
        },

        mounted() {
            this.loadData();

            window.onscroll = () => this.loadMore();
        },

        methods: {
            downloadFileAndHideModal() {
                let routeData = this.$router.resolve({
                    path: this.$router.currentRoute.fullPath,
                    query: {filename: "restocking-"+ this.getUrlParameter('filter[warehouse_code]')+".csv"}
                });
                window.open(routeData.href, '_blank');

                this.$bvModal.hide('configuration-modal');
            },

            loadMore() {
                if (this.isLoading) {
                    return;
                }

                if (! this.isMoreThanPercentageScrolled(70)) {
                    return;
                }

                if (this.reachedEnd) {
                    return;
                }

                this.loadData(++this.pagesLoaded);
            },

            loadData(page = 1) {
                this.showLoading();

                const params = this.$router.currentRoute.query;
                params['page'] = page;

                this.apiGetRestocking(params)
                    .then((response) => {
                        if (page === 1) {
                            this.data = [];
                        }
                        this.reachedEnd = response.data.data.length === 0;

                        this.data = this.data.concat(response.data.data);
                        this.pagesLoaded = page;
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            findText() {
                this.data = [];
                this.loadData();
            },
        },
    }
</script>

<style lang="scss" scoped>
    //.row {
    //    display: flex;
    //    justify-content: center;
    //    align-items: center;
    //}
</style>