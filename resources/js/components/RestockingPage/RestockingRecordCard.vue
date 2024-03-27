<template>
    <div class="card ml-0 pl-0 mb-3">
        <div class="grid-col-12 px-2 py-2 m-0 gap-none">
            <div class="col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-3">
                <product-info-card :product="record['product']"/>
            </div>
            <div class="small col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-3">
                <div class="sd-block lg:sd-none">
                    Warehouse: <b>{{ record['warehouse_code'] }}</b>
                </div>
                <div :class="{'bg-warning': record['warehouse_quantity'] <= 0 }" @click="expanded = !expanded">warehouse quantity: <b>{{ record['warehouse_quantity'] }}</b></div>
                <div @click="expanded = !expanded">reorder point: <b>{{ record['reorder_point'] }}</b></div>
                <div @click="expanded = !expanded">restock level: <b>{{ record['restock_level'] }}</b></div>
                <div class="mt-1">
                    <div @click="expanded = !expanded" class="d-inline">last sold at: </div>
                    <strong @click="showInventoryMovementModal" class="text-primary cursor-pointer">{{ formatDateTime(record['last_sold_at']) }}</strong>
                </div>
                <div>
                    <div @click="expanded = !expanded" class="d-inline">last received at:</div>
                    <strong @click="showInventoryMovementModal" class="text-primary cursor-pointer">{{ formatDateTime(record['last_received_at']) }}</strong>
                </div>
                <div @click="expanded = !expanded">last counted at: <b>{{ formatDateTime(record['last_counted_at'],'D MMM HH:MM') }}</b></div>
                <div @click="expanded = !expanded">sale price: <b>{{ pricing['sale_price'] }} ({{ formatDateTime(pricing['sale_price_start_date'], 'D MMM Y') }} - {{ formatDateTime(pricing['sale_price_end_date'], 'D MMM Y') }})</b></div>
            </div>
            <div class="text-lg-center sd-none lg:sd-block lg:col-span-1">
                <p class="small text-secondary mb-0">Warehouse</p>
                <h4>{{ record['warehouse_code'] }}</h4>
            </div>
            <div class="col-span-12 md:col-span-4 lg:col-span-5">
                <div class="row-col text-right" @click="expanded = !expanded">
                    <div class="row">
                        <div class="col-3">
                            <text-card label="price" :text="pricing['price']" :class="{ 'text-secondary': isOnSale }" ></text-card>
                        </div>
                        <div class="col-3">
                            <text-card label="sale price" :text="pricing['sale_price']" v-if="isOnSale" class="bg-warning"></text-card>
                        </div>
                        <div class="col-3">
                            <number-card label="in stock" :number="record['quantity_in_stock']" v-bind:class="{'bg-warning' : record['quantity_in_stock'] < 0 }"></number-card>
                        </div>
                        <div class="col-3">
                            <number-card label="required" :number="record['quantity_required']" v-if="record['warehouse_quantity'] > 0"></number-card>
                            <text-card class="fa-pull-right" label="required" text="N/A" v-if="Number(record['warehouse_quantity']) === 0"></text-card>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <number-card label="weeks cover" :number="weeksCover"></number-card>
                        </div>
                        <div class="col-3">
                        </div>
                        <div class="col-3">
                            <number-card label="sold 7 days" :number="record['quantity_sold_last_7_days']"></number-card>
                        </div>
                        <div class="col-3">
                            <number-card label="incoming" :number="record['quantity_incoming']"></number-card>
                        </div>
                    </div>

                    <div class="text-left d-sm-flex d-md-inline">
                    </div>
                </div>

                <div @click="expanded = !expanded" class="text-center text-secondary">
                    <font-awesome-icon v-if="expanded" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                    <font-awesome-icon v-if="!expanded" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                </div>

                <div v-if="expanded">

                    <div class="row-col text-center mt-3 small text-secondary">
                        reorder point
                    </div>

                    <div class="row-col text-nowrap">
                        <div class="input-group mb-3">
                            <button tabindex="-1" @click="minusReorderPoint" class="btn btn-danger mr-3" type="button" id="button-addon5" style="min-width: 45px">-</button>
                            <input tabindex="0"
                                   @keyup="onUpdateReorderPointEvent"
                                   v-model="newReorderPointValue"
                                   @focus="simulateSelectAll"
                                   type="number"
                                   inputmode="numeric"
                                   class="form-control text-center"
                                   style="font-size: 24px"
                            >
                            <button tabindex="-1" @click="plusReorderPoint" class="btn btn-success ml-3" type="button" id="button-addon6" style="min-width: 45px">+</button>
                        </div>
                    </div>

                    <div class="row-col text-center mt-3 small text-secondary">
                        restock level
                    </div>
                    <div class="row-col text-nowrap">
                        <div class="input-group mb-3">
                            <button tabindex="-1" @click="minusRestockLevel" class="btn btn-danger mr-3" type="button" id="button-addon3" style="min-width: 45px">-</button>
                            <input tabindex="0"
                                   @keyup="onUpdateRestockLevelEvent"
                                   v-model="newRestockLevelValue"
                                   @focus="simulateSelectAll"
                                   type="number"
                                   inputmode="numeric"
                                   class="form-control text-center"
                                   v-bind:class="{ 'alert-danger': newRestockLevelValue < newReorderPointValue }"
                                   style="font-size: 24px"
                            >
                            <button tabindex="-1" @click="plusRestockLevel" class="btn btn-success ml-3" type="button" id="button-addon4" style="min-width: 45px">+</button>
                        </div>
                    </div>
                    <template @click="expanded = !expanded" v-if="expanded">
                        <div @click="expanded = !expanded">last movement at: <b>{{ formatDateTime(record['last_movement_at'],'D MMM HH:MM') }}</b></div>
                        <div @click="expanded = !expanded">first received at: <b>{{ formatDateTime(record['first_received_at'],'D MMM HH:MM') }}</b></div>
                        <div @click="expanded = !expanded">location: <b>{{ record['warehouse_code'] }}</b></div>
                    </template>
                    <div class="row-col text-center align-bottom pb-2 m-0 font-weight-bold text-uppercase small text-secondary">
                        Incoming
                    </div>

                    <div v-for="dataCollectionRecord in dataCollectorRecords" :key="dataCollectionRecord['id']">
                        <div class="row col">
                            <div class="text-primary">
                                <a :href="'/data-collector/' + dataCollectionRecord['data_collection']['id']">
                                    {{ dataCollectionRecord['data_collection']['name'] }}
                                </a>
                            </div>
                        </div>
                        <div class="row col">
                            <div class="flex-fill">
                                <a class="text-secondary small" :href="'/data-collector/' + dataCollectionRecord['data_collection']['id']">
                                    {{ formatDateTime(dataCollectionRecord['data_collection']['created_at']) }}
                                </a>
                            </div>
                            <div class="">
                                <number-card label="requested" :number="dataCollectionRecord['quantity_requested']"></number-card>
                                <number-card label="outstanding" :number="dataCollectionRecord['quantity_requested'] - dataCollectionRecord['total_transferred_in']"></number-card>
                            </div>
                        </div>
                        <hr />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import helpers from "../../mixins/helpers";
import api from "../../mixins/api";
import url from "../../mixins/url";
import ProductCard from "../Products/ProductCard";
import BarcodeInputField from "../SharedComponents/BarcodeInputField";
import moment from "moment";

export default {
        name: "RestockingRecord",
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            ProductCard,
            BarcodeInputField,
        },

        props: {
            record: null,
        },

        watch: {
            expanded: function (newValue, oldValue) {
                if (newValue) {
                    let params = {
                        'filter[product_id]': this.record['product_id'],
                        'filter[warehouse_id]': this.record['warehouse_id'],
                        'per_page': 10,
                        'include': 'dataCollection'
                    };

                    this.apiGetDataCollectorRecords(params)
                        .then((response) => {
                            this.dataCollectorRecords = response.data.data;
                        });
                }
            }
        },

        data: function() {
            return {
                expanded: false,
                dataCollectorRecords: [],
            };
        },

        computed: {
            weeksCover: {
                get: function() {
                    if (this.record['quantity_sold_last_7_days'] === null) {
                        return 0;
                    }

                    if (this.record['quantity_sold_last_7_days'] === 0) {
                        return 0;
                    }

                    if (this.record['quantity_in_stock'] <= 0) {
                        return 0;
                    }

                    return Math.floor(this.record['quantity_in_stock'] / (this.record['quantity_sold_last_7_days']));
                },
            },

            pricing: {
                get: function() {
                    return this.record['product']['prices'][this.currentUser()['warehouse']['code']];
                },
            },
            isOnSale: {
                get: function() {
                    const pricingRecord = this.record['product']['prices'][this.currentUser()['warehouse']['code']];

                    const salePriceIsCorrect = pricingRecord['sale_price'] !== null && pricingRecord['sale_price'] < pricingRecord['price'];
                    const startDateInPast = moment(pricingRecord['sale_price_start_date']).isSameOrBefore(moment());
                    const endDateInFuture = moment(pricingRecord['sale_price_end_date']).isSameOrAfter(moment().subtract(1, 'day'));

                    return salePriceIsCorrect && startDateInPast && endDateInFuture;
                },
            },

            isSaleComing: {
                get: function () {
                    const pricingRecord = this.record['product']['prices'][this.currentUser()['warehouse']['code']];

                    const startDateInFuture = moment(pricingRecord['sale_price_start_date']).isAfter(moment());
                    const startDateWithin7Days = moment(pricingRecord['sale_price_start_date']).isBefore(moment().add(7, 'days'));

                    return startDateInFuture && startDateWithin7Days;
                },
            },

            newRestockLevelValue: {
                get: function() {
                    return Number(this.record['restock_level']);
                },
                set: function(newValue) {
                    this.record['restock_level'] = Number(newValue);
                }
            },

            newReorderPointValue: {
                get: function() {
                    return Number(this.record['reorder_point']);
                },
                set: function(newValue) {
                    this.record['reorder_point'] = Number(newValue);
                }
            },
        },

        methods: {
            minusRestockLevel() {
                if (Number(this.newRestockLevelValue) - 1 < Number(this.newReorderPointValue)) {
                    this.updateRestockLevel(Number(this.record['reorder_point']));
                    return;
                }

                this.updateRestockLevel(Number(this.record['restock_level']) - 1);
            },

            plusRestockLevel() {
                if (Number(this.newRestockLevelValue) < Number(this.newReorderPointValue)) {
                    this.updateRestockLevel(Number(this.record['reorder_point']));
                    return;
                }

                this.updateRestockLevel(Number(this.record['restock_level']) + 1);
            },

            minusReorderPoint() {
                if (Number(this.record['reorder_point']) === 0) {
                    this.updateReorderPoint(Math.ceil(Number(this.record['quantity_in_stock']) / 3));
                    return;
                }

                this.updateReorderPoint(Number(this.record['reorder_point']) - 1);
            },

            plusReorderPoint() {
                this.updateReorderPoint(Number(this.record['reorder_point']) + 1);
            },

            onUpdateRestockLevelEvent(keyboard_event) {
                this.updateRestockLevel(keyboard_event.target.value);
            },

            onUpdateReorderPointEvent(keyboard_event) {
                this.updateReorderPoint(keyboard_event.target.value);
            },

            postInventoryUpdate() {
                const originalQuantityRequired = Number(this.record['quantity_required']);
                const originalRestockLevel = Number(this.record['restock_level']);
                const originalReorderPoint = Number(this.record['reorder_point']);

                this.apiInventoryPost({
                        'id': this.record['inventory_id'],
                        'restock_level': this.record['restock_level'],
                        'reorder_point': this.record['reorder_point'],
                    })
                    .then(response => {
                        this.record['quantity_required'] = response.data.data[0]['quantity_required'];
                    })
                    .catch(error => {
                        this.record['quantity_required'] = originalQuantityRequired;
                        this.record['restock_level'] = originalRestockLevel;
                        this.record['reorder_point'] = originalReorderPoint;
                        this.notifyError(error);
                    });
            },

            updateRestockLevel(value) {
                if (this.record['reorder_point'] > value) {
                    return;
                }

                this.record['restock_level'] = value;

                this.postInventoryUpdate();
            },

            updateReorderPoint(value) {
                let newValue = Math.max(0, Number(value));

                if (this.record['reorder_point'] > 0) {
                    let ratio = this.record['restock_level'] / this.record['reorder_point'];

                    if (ratio < 1) {
                        ratio = 3;
                    }

                    this.record['restock_level'] = Math.ceil(newValue * ratio);
                }

                this.record['reorder_point'] = newValue;

                this.postInventoryUpdate();
            },

            showInventoryMovementModal() {
                this.$emit('showModalMovement', this.record['inventory_id'])
            },
        },
    }

</script>
