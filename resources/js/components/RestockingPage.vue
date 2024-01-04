<template>
    <div class="container dashboard-widgets">

        <div class="row mb-1 pb-2 p-1 sticky-top bg-light" style="z-index: 10;" v-if="currentUser['warehouse'] !== null">
            <div class="flex-fill">
                <barcode-input-field placeholder='Search products using name, sku, alias or command'
                                     :url_param_name="'filter[search]'"
                                     @barcodeScanned="findText"></barcode-input-field>
            </div>

            <button v-b-modal="'configuration-modal'"  id="config-button" type="button" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
        </div>

        <div class="row pl-2 p-0">
            <div class="col-12 text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                TOOLS > RESTOCKING
            </div>
        </div>

        <b-modal id="configuration-modal" no-fade hide-footer hide-header
                 @shown="setFocusElementById('stocktake-input')"
                 @hidden="focusOnInputAndReload">
            <stocktake-input></stocktake-input>
            <hr>
            <button type="button" @click.prevent="downloadFileAndHideModal" class="col btn mb-1 btn-primary">Download</button>
        </b-modal>

        <template v-for="record in data">
            <restocking-record-card :record="record" :key="record.id" @showModalMovement=showModalMovement></restocking-record-card>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" dusk="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <modal-inventory-movement :product_sku="showMovementSku"/>
    </div>

</template>

<script>
import loadingOverlay from '../mixins/loading-overlay';
import url from "../mixins/url";
import api from "../mixins/api";
import helpers from "../mixins/helpers";
import ModalInventoryMovement from './SharedComponents/ModalInventoryMovement';

export default {
        components: {
            ModalInventoryMovement
        },

        mixins: [loadingOverlay, url, api, helpers],

        props: {
            initial_data: null,
        },

        data: function() {
            return {
                data: [],
                per_page: 40,
                pageScrollPercentage: 70,
                reachedEnd: false,
                pagesLoaded: 0,
                selectedRecord: null,
                newReorderPoint: null,
                newRestockLevel: null,
                newQuantityInStock: null,
                showMovementSku: null,
            };
        },

        mounted() {
            this.getUrlFilterOrSet('filter[warehouse_code]', this.currentUser()['warehouse']['code']);
            this.getUrlFilterOrSet('filter[warehouse_has_stock]', true);
            this.getUrlFilterOrSet('sort', '-warehouse_has_stock,-quantity_required,-quantity_incoming,-warehouse_quantity');

            this.loadRestockingRecords();

            window.onscroll = () => this.loadMore();

            if (this.initial_data) {
                // this is dirty trick to "simulate" data reload for better UX
                setTimeout(() => {
                    if (this.data.length > 0) {
                        return;
                    }
                    this.data = this.initial_data;
                }, 100);
            }
        },

        methods: {
            loadRestockingRecords(page = 1) {
                this.showLoading();

                const params = this.$router.currentRoute.query;
                params['include'] = 'product,product.tags,product.prices';
                params['per_page'] = this.per_page;
                params['page'] = page;

                // params['cache_name'] = page === 1 ? params['cache_name'] : '';

                this.apiGetRestocking(params)
                    .then((response) => {
                        if (page === 1) {
                            this.data = [];
                        }
                        this.reachedEnd = response.data.data.length < this.per_page;

                        this.data = this.data.concat(response.data.data);
                        this.pagesLoaded = page;
                    })
                    .catch((error) => {
                        this.showException(error);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            focusOnInputAndReload() {
                this.setFocusElementById(100,'barcodeInput', true, true);
                this.loadRestockingRecords();
            },

            downloadFileAndHideModal() {
                let routeData = this.$router.resolve({
                    path: this.$router.currentRoute.fullPath,
                    query: {filename: "restocking-"+ this.getUrlParameter('filter[warehouse_code]')+".csv"}
                });
                window.open(routeData.href, '_blank');

                this.$bvModal.hide('configuration-modal');
            },

            showUpdateRestockingInfoModal(restocking_record) {
                this.selectedRecord = restocking_record;
                this.newReorderPoint = restocking_record['reorder_point'];
                this.newRestockLevel = restocking_record['restock_level'];
                this.newQuantityInStock = restocking_record['quantity_in_stock'];
                this.$bvModal.show('update-restocking-info-modal');
            },

            loadMore() {
                if (this.isLoading) {
                    return;
                }

                if (! this.isMoreThanPercentageScrolled(this.pageScrollPercentage)) {
                    return;
                }

                if (this.reachedEnd) {
                    return;
                }

                // we double per_page every second page load to avoid hitting the API too hard
                // and we will limit it to 100-ish per_page
                if ((this.per_page < 160) && (this.pagesLoaded % 2 === 0)) {
                    this.pagesLoaded = this.pagesLoaded / 2;
                    this.per_page = this.per_page * 2;
                }

                this.pageScrollPercentage = 90;
                this.loadRestockingRecords(++this.pagesLoaded);
            },

            findText() {
                this.setUrlParameter('filter[warehouse_quantity_between]', '');
                this.setUrlParameter('filter[warehouse_has_stock]', null);
                this.setUrlParameter('sort', '-warehouse_quantity');
                this.data = [];
                this.loadRestockingRecords();
            },

            showModalMovement(product_sku) {
                this.showMovementSku = product_sku
                this.$bvModal.show('show-inventory-movements')
            }
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
