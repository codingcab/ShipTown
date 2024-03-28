<template>
    <div v-if="dataCollection">
        <template v-if="dataCollection && dataCollection['currently_running_task'] != null">
            <div class="alert alert-danger">Please wait while stock being updated</div>
        </template>

        <swiping-card>
            <template v-slot:content>
                    <div class="row setting-list">
                        <div class="col-sm-12 col-lg-6">
                            <div id="data_collection_name" class="text-primary">{{ dataCollection ? dataCollection['name'] : '' }}</div>
                            <div class="text-secondary small">{{ formatDateTime(dataCollection ? dataCollection['created_at'] : '', 'dddd - MMM D HH:mm')  }}</div>
                        </div>
                        <div class="col-sm-12 col-lg-6" v-if="dataCollection && dataCollection['deleted_at']">
                            <text-card class="fa-pull-right" :label="formatDateTime(dataCollection ? dataCollection['deleted_at'] : '', 'dddd - MMM D HH:mm')" text="ARCHIVED"></text-card>
                        </div>
                    </div>
            </template>
        </swiping-card>

        <div class="row pb-2 p-1 mt-0 sticky-top bg-light flex-nowrap" style="z-index: 10;">
            <div class="flex-fill">
                <barcode-input-field :input_id="'barcode_input'" @barcodeScanned="onBarcodeScanned" placeholder="Scan sku or alias" class="text-center font-weight-bold"></barcode-input-field>
            </div>

            <input ref="current_location" placeholder="Shelf" style="width: 60px" class="form-control text-center ml-2 font-weight-bold"
                   v-model="minShelfLocation"
                   onClick="this.select();"
                   @keyup.enter="setMinShelfLocation"/>

            <button id="showConfigurationButton" v-b-modal="'configuration-modal'" type="button" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
        </div>

        <div v-show="manuallyExpandComments" class="row mb-2 mt-1 my-1">
            <input id="comment-input" ref="newCommentInput" v-model="input_comment" class="form-control" placeholder="Add comment here" @keypress.enter="addComment"/>
        </div>

        <div class="mb-1" v-if="commentsToShow.length">
            <div class="d-flex mx-1" v-for="(comment, index) in commentsToShow" @click="toggleExpandComments">
                <div>
                    <b>{{ comment.user ? comment.user.name : 'AutoPilot' }}: </b>{{ comment.comment }}
                </div>
                <div class="ml-auto" v-if="index === 0">
                    <font-awesome-icon v-if="manuallyExpandComments" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                    <font-awesome-icon v-if="!manuallyExpandComments" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                </div>
            </div>
        </div>
        <div v-else class="row text-center text-secondary" @click="toggleExpandComments">
            <div class="col">
                <font-awesome-icon v-if="manuallyExpandComments" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                <font-awesome-icon v-if="!manuallyExpandComments" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
            </div>
        </div>

        <data-collector-quantity-request-modal @hidden="onQuantityRequestModalHidden"></data-collector-quantity-request-modal>

        <div v-if="(dataCollectionRecords !== null) && (dataCollectionRecords.length === 0)" class="text-secondary small text-center mt-3">
            No records found<br>
            Scan or type in SKU to start<br>
        </div>

        <template v-for="record in dataCollectionRecords">
            <swiping-card :disable-swipe-right="true" :disable-swipe-left="true">
                <template v-slot:content>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <product-info-card :product= "record['product']"></product-info-card>
                        </div>
                        <div class="col-12 col-md-3 text-left small">
                            <div>in stock: <strong>{{ dashIfZero(Number(record['inventory']['quantity'])) }}</strong></div>
                            <div>last counted: <strong>{{ formatDateTime(record['inventory_last_counted_at']) }}</strong></div>
                            <div><div @click="expanded = !expanded" class="d-inline">last movement at: </div><strong @click="showRecentInventoryMovementsModal(record['inventory_id'])" class="text-primary cursor-pointer">{{ formatDateTime(record['last_movement_at']) }}</strong></div>
                        </div>
                        <div class="col-12 col-md-5 text-right">
                            <number-card label="total out" :number="record['total_transferred_out']" v-if="record['total_transferred_out'] !== 0" v-bind:class="{'bg-warning': record['quantity_requested'] &&  record['quantity_requested'] < record['quantity_scanned'] + record['total_transferred_out'] + record['total_transferred_in']}"></number-card>
                            <number-card label="total in" :number="record['total_transferred_in']" v-if="record['total_transferred_in'] !== 0" v-bind:class="{'bg-warning': record['quantity_requested'] &&  record['quantity_requested'] < record['quantity_scanned'] + record['total_transferred_out'] + record['total_transferred_in']}"></number-card>
                            <number-card label="reserved" :number="record['inventory']['quantity_reserved']" v-if="record['inventory']['quantity_reserved'] > 0" class="bg-warning"></number-card>
                            <number-card label="requested" :number="record['quantity_requested']" v-bind:class="{'bg-warning': (record['quantity_requested'] ?? 0) === 0 && (record['quantity_scanned'] ?? 0) > 0 }"></number-card>
                            <number-card label="scanned" :number="record['quantity_scanned']" v-bind:class="{'bg-warning': record['quantity_scanned'] > 0 && record['quantity_requested'] &&  record['quantity_requested'] < record['quantity_scanned'] + record['total_transferred_out'] + record['total_transferred_in']}"></number-card>
                            <number-card label="to scan" :number="record['quantity_to_scan'] ?? 0" ></number-card>
                            <text-card label="shelf" :text="record['shelf_location']"></text-card>
                        </div>
                    </div>
                </template>
            </swiping-card>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="configuration-modal" no-fade hide-header
                 @shown="onShownConfigurationModal"
                 @hidden="setFocusElementById('barcode_input')"
        >
            <div v-if="dataCollection">
                <stocktake-input></stocktake-input>
                <div v-if="dataCollection['deleted_at'] === null" :class="{ 'disabled': true }">
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <div class="setting-title">Single Scan mode</div>
                            <div class="setting-desc">It will not ask for quantity when scanned <br> 1 will be used as default</div>
                        </div>
                        <div class="custom-control custom-switch m-auto text-right align-content-center float-right w-auto">
                            <input type="checkbox" @change="toggleSingleScanMode" class="custom-control-input" id="singleScanToggle" v-model="singleScanEnabled">
                            <label class="custom-control-label" for="singleScanToggle"></label>
                        </div>
                    </div>
                    <hr>
                    <button :disabled="! buttonsEnabled" @click.prevent="autoScanAll" v-b-toggle class="col btn mb-2 btn-primary">AutoScan ALL Records</button>
                    <br>
                    <br>
                    <button id="transferInButton" :disabled="! buttonsEnabled" @click.prevent="transferStockIn" v-b-toggle class="col btn mb-2 btn-primary">Transfer In</button>
                    <button :disabled="! buttonsEnabled" @click.prevent="transferToWarehouseClick" v-b-toggle class="col btn mb-2 btn-primary">Transfer To...</button>
                    <button :disabled="! buttonsEnabled" @click.prevent="importAsStocktake" v-b-toggle class="col btn mb-2 btn-primary">Import As Stocktake</button>
                    <button :disabled="! buttonsEnabled" @click.prevent="transferStockOut" v-b-toggle class="col btn mb-2 btn-primary">Deduct From Stock</button>
                    <button :disabled="! buttonsEnabled" @click.prevent="archiveCollection" v-b-toggle class="col btn mb-2 btn-primary">Archive Collection</button>
                </div>
                <br>
                <a :href="getDownloadLink"  @click.prevent="downloadFileAndHideModal" v-b-toggle class="col btn mb-1 btn-primary">Download</a>
                <div v-if="dataCollection['deleted_at'] === null">
                    <hr>
                    <vue-csv-import
                        v-model="csv"
                        headers
                        canIgnore
                        autoMatchFields
                        loadBtnText="Load"
                        :map-fields="['product_sku', 'quantity_requested', 'quantity_scanned']">

                        <template slot="hasHeaders" slot-scope="{headers, toggle}">
                            <label>
                                <input type="checkbox" id="hasHeaders" :value="headers" @change="toggle">
                                Headers?
                            </label>
                        </template>

                        <template slot="error">
                            File type is invalid
                        </template>

                        <template slot="thead">
                            <tr>
                                <th>My Fields</th>
                                <th>Column</th>
                            </tr>
                        </template>

                        <template slot="submit" slot-scope="{submit}">
                            <button @click.prevent="submit">send!</button>
                        </template>
                    </vue-csv-import>

                    <button v-if="csv" type="button" @click.prevent="postCsvRecordsToApiAndCloseModal" class="col btn mb-1 btn-primary">Import Records</button>

                </div>

            </div>

            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('configuration-modal');">
                    Cancel
                </b-button>
                <b-button variant="primary" class="float-right" @click="$bvModal.hide('configuration-modal');">
                    OK
                </b-button>
            </template>

        </b-modal>

        <b-modal id="transferToModal" no-fade hide-header @hidden="setFocusElementById('barcode_input')">
            <template v-for="warehouse in warehouses">
                <button @click.prevent="transferToWarehouse(warehouse)" v-if="dataCollection && warehouse['id'] !== dataCollection['warehouse_id']" v-b-toggle class="col btn mb-2 btn-primary">{{ warehouse.name }}</button>
            </template>

            <template #modal-footer>
                <b-button variant="outline-secondary" class="float-right" @click="$bvModal.hide('transferToModal');">Close</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import beep from '../mixins/beep';
    import loadingOverlay from '../mixins/loading-overlay';

    import FiltersModal from "./Packlist/FiltersModal";
    import url from "../mixins/url";
    import api from "../mixins/api";
    import helpers from "../mixins/helpers";
    import Vue from "vue";
    import NumberCard from "./SharedComponents/NumberCard";
    import SwipingCard from "./SharedComponents/SwipingCard";
    import { VueCsvImport } from 'vue-csv-import';

    export default {
        mixins: [loadingOverlay, beep, url, api, helpers],

        components: {
            FiltersModal,
            NumberCard,
            SwipingCard,
            VueCsvImport,
        },

        props: {
            data_collection_id: null,
        },

        data: function() {
            return {
                minShelfLocation: '',
                singleScanEnabled: false,
                scannedDataCollectionRecord: null,
                scannedProduct: null,
                dataCollection: null,
                dataCollectionRecords: [],
                nextUrl: null,
                page: 1,
                per_page: 15,
                csv: null,
                warehouses: [],
                buttonsEnabled: false,
                selectedInventoryId: null,
                manuallyExpandComments: false,
                input_comment: '',
            };
        },

        mounted() {
            if (! Vue.prototype.$currentUser['warehouse_id']) {
                this.$snotify.error('You do not have warehouse assigned. Please contact administrator', {timeout: 50000});
                return;
            }

            window.onscroll = () => this.loadMoreWhenNeeded();

            this.getUrlFilterOrSet('warehouse_code', Vue.prototype.$currentUser['warehouse']['code']);

            this.loadWarehouses();

            this.reloadDataCollection();
        },

        methods: {
            showRecentInventoryMovementsModal(inventory_id) {
                this.$modal.showRecentInventoryMovementsModal(inventory_id);
            },

            reloadDataCollection() {
                this.loadDataCollectorDetails();
                this.loadDataCollectorRecords();
            },

            onQuantityRequestModalHidden() {
                this.setFocusElementById('barcode_input');
                this.reloadDataCollection();
            },

            onShownConfigurationModal() {
                this.setFocusElementById('stocktake-input', true, true);
                this.buttonsEnabled = true;
            },

            onBarcodeScanned: function (barcode) {
                if (barcode === '') {
                    return;
                }

                if (this.dataCollection['deleted_at'] !== null) {
                    this.notifyError('This collection is already archived');
                    return;
                }

                if (this.singleScanEnabled) {
                    this.apiPostDataCollectorActionsAddProduct({
                            'data_collection_id': this.dataCollection['id'],
                            'sku_or_alias': barcode,
                            'quantity_scanned': 1,
                        })
                        .then(() => {
                            this.notifySuccess('1 x ' + barcode);
                            this.reloadDataCollection();
                        })
                        .catch((error) => {
                            this.displayApiCallError(error);
                        });

                    return;
                }

                this.$modal.showDataCollectorQuantityRequestModal(this.dataCollection['id'], barcode);
            },

            toggleSingleScanMode() {
                setTimeout(() => {
                    this.hideBvModal('configuration-modal');
                }, 200)
            },

            loadWarehouses: function () {
                this.apiGetWarehouses({'per_page': 999, 'sort': 'name'})
                    .then(response => {
                        this.warehouses = response.data.data;
                    });
            },

            loadDataCollectorDetails: function () {

                let params = {
                    'filter[id]': this.data_collection_id,
                    'filter[with_archived]': true,
                    'include': 'comments,comments.user'
                }

                this.apiGetDataCollector(params)
                    .then(response => {
                        this.dataCollection = response.data.data[0];
                    }).catch(error => {
                        console.log(error);
                        this.displayApiCallError(error);
                    });
            },

            transferToWarehouseClick() {
                this.$bvModal.hide('configuration-modal');
                this.$bvModal.show('transferToModal');
            },

            transferToWarehouse(warehouse) {
                let data = {
                    'action': 'transfer_to_scanned',
                    'destination_warehouse_id': warehouse['id'],
                }

                this.apiUpdateDataCollection(this.data_collection_id, data)
                    .then(response => {
                        this.$bvModal.hide('configuration-modal');
                        location.href = '/data-collector';
                        // setTimeout(() => {
                        //     this.reloadDataCollection();
                        // }, 500);
                    })
                    .catch(error => {
                        this.showException(error);
                    });

                this.$bvModal.hide('transferToModal');
            },

            transferStockOut() {
                this.buttonsEnabled = false;

                let data = {
                    'action': 'transfer_out_scanned',
                }

                this.apiUpdateDataCollection(this.data_collection_id, data)
                    .then(response => {
                        this.$snotify.success('Stock transferred out successfully');
                        this.$bvModal.hide('configuration-modal');
                        setTimeout(() => {
                            this.reloadDataCollection();
                        }, 1000);
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },


            transferStockIn() {
                let data = {
                    'action': 'transfer_in_scanned',
                }

                this.apiUpdateDataCollection(this.data_collection_id, data)
                    .then(response => {
                        this.$snotify.success('Stock transferred in successfully');
                        this.$bvModal.hide('configuration-modal');
                        setTimeout(() => {
                            this.reloadDataCollection();
                        }, 500);
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },

            receiveAll() {
                let data = {
                    'action': 'auto_scan_all_requested',
                }

                this.apiUpdateDataCollection(this.data_collection_id, data)
                    .then(response => {
                        this.transferStockIn();
                        this.reloadDataCollection();
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },

            importAsStocktake() {
                this.buttonsEnabled = false;

                let data = {
                    'data_collection_id': this.data_collection_id,
                }

                this.apiDataCollectorActionImportAsStocktake(data)
                    .then(response => {
                        this.$snotify.success('Stocktake imported successfully');
                        this.$bvModal.hide('configuration-modal');
                        setTimeout(() => {
                            this.reloadDataCollection();
                        }, 500);
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },

            archiveCollection() {
                this.apiDeleteDataCollection(this.data_collection_id)
                    .then(response => {
                        this.$snotify.success('Collection archived successfully');
                        this.$bvModal.hide('configuration-modal');
                        setTimeout(() => {
                            this.reloadDataCollection();
                        }, 500);
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },

            autoScanAll() {
                let data = {
                    'action': 'auto_scan_all_requested',
                }

                this.apiUpdateDataCollection(this.data_collection_id, data)
                    .then(response => {
                        this.$snotify.success('Auto scan completed successfully');
                        this.$bvModal.hide('configuration-modal');
                        setTimeout(() => {
                            this.reloadDataCollection();
                        }, 500);
                    })
                    .catch(error => {
                        this.showException(error);
                    });
            },

            loadMoreWhenNeeded() {
                if (this.isLoading) {
                    return;
                }

                if (this.isMoreThanPercentageScrolled(70) === false) {
                    return;
                }

                if (this.nextUrl === null) {
                    return;
                }

                // we double per_page every second page load to avoid hitting the API too hard
                // and we will limit it to 100-ish per_page
                if ((this.page % 2 === 0) && (this.per_page < 100)) {
                    this.page = this.page/ 2;
                    this.per_page = this.per_page * 2;
                }

                this.loadDataCollectorRecords(++this.page);
            },

            setMinShelfLocation() {
                this.setUrlParameter( "filter[shelf_location_greater_than]", this.minShelfLocation);
                this.loadDataCollectorRecords();
                this.setFocusElementById('barcode_input');
            },

            loadDataCollectorRecords(page = 1) {
                this.showLoading();

                const params = this.$router.currentRoute.query;
                params['filter[data_collection_id]'] = this.data_collection_id;
                params['include'] = 'product,inventory,product.tags,product.aliases';
                params['per_page'] = this.per_page;
                params['page'] = page;

                this.apiGetDataCollectorRecords(params)
                    .then((response) => {
                        if (page === 1) {
                            this.dataCollectionRecords = response.data.data;
                        } else {
                            this.dataCollectionRecords = this.dataCollectionRecords.concat(response.data.data);
                        }

                        this.page = response.data['meta']['current_page'];
                        this.nextUrl = response.data['links']['next'];
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            onProductCountRequestResponse(response) {
                const payload = {
                    'data_collection_id': this.data_collection_id,
                    'product_id': response['product_id'],
                    'quantity_scanned': response['quantity'],
                }

                this.apiPostDataCollectorRecords(payload)
                    .then(() => {
                        this.notifySuccess('Data collected');
                    })
                    .catch(e => {
                        this.displayApiCallError(e);
                    })
                    .finally(() => {
                        this.reloadDataCollection();
                    });
            },

            postCsvRecordsToApiAndCloseModal() {
                const data = this.csv.map(record => ({
                    'product_sku': record.product_sku,
                    'quantity_requested': record.quantity_requested,
                    'quantity_scanned': record.quantity_scanned,
                }));

                //we removing header row from csv
                data.shift();

                const payload = {
                    'data_collection_id': this.data_collection_id,
                    'data': data,
                }

                this.apiPostCsvImport(payload)
                    .then(() => {
                        this.notifySuccess('Records imported');
                        this.$bvModal.hide('configuration-modal');
                    })
                    .catch(e => {
                        this.displayApiCallError(e);
                    })
                    .finally(() => {
                        this.reloadDataCollection();
                    });
            },

            downloadFileAndHideModal() {
                window.open(this.getDownloadLink, '_blank');

                this.hideBvModal('configuration-modal')
            },

            hideBvModal(ref) {
                this.$bvModal.hide(ref);
            },

            addComment() {

                let data = {
                    "data_collection_id": this.dataCollection.id,
                    "comment": this.input_comment
                };

                // quick hack to immediately display comment
                this.dataCollection.comments.unshift(data);

                this.apiPostDataCollectionComment(data)
                    .then(() => {
                        this.loadDataCollectorDetails();
                        this.input_comment = '';
                        this.manuallyExpandComments = false;
                        this.setFocusElementById('barcode_input');

                    })
                    .catch((error) => {
                        console.log(error)
                        this.displayApiCallError(error);
                    });
            },

            toggleExpandComments() {
                this.manuallyExpandComments = !this.manuallyExpandComments;
                if(this.manuallyExpandComments){
                    this.setFocusElementById('comment-input', true);
                } else {
                    this.setFocusElementById('barcode_input', false);
                }
            },

        },

        computed: {
            getDownloadLink() {
                let routeData = this.$router.resolve({
                    path: this.$router.currentRoute.fullPath,
                    query: {
                        'select': 'product_sku,product_name,total_transferred_in,total_transferred_out,quantity_requested,quantity_to_scan,quantity_scanned,inventory_quantity,product_price,product_sale_price,product_sale_price_start_date,product_sale_price_end_date,product_cost',
                        'filter[data_collection_id]': this.data_collection_id,
                        filename: this.dataCollection['name'] +".csv"
                    }
                });

                return routeData.href;
            },

            commentsToShow() {
                return this.dataCollection.comments.length
                    ? (this.manuallyExpandComments ? this.dataCollection.comments : [this.dataCollection.comments[0]])
                    : [];
            }
        },
    }
</script>


<style lang="scss">
.setting-list{
    width: 100%;
    color: #495057;
    display: flex;
    align-items: flex-start;
    margin-bottom: 5px;
}

.setting-list:hover, .setting-list:focus {
    color: #495057;
    text-decoration: none;
    background-color: #f8f9fa;
}

.setting-icon{
    padding: 1rem;
    margin-right: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}

.setting-icon:hover{
    background-color: unset;
}

.setting-title{
    color: #3490dc;
    font-weight: bolder;
    /*font-size: 1rem;*/
    /*line-height: 1.2;*/
    margin-bottom: 2px;
}

.setting-desc{
    color: #6c757d;
    font-size: 10pt;
}
</style>
