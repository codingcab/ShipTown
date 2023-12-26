<template>
    <div>
        <div v-if="! order_id" class="row text-center mt-3" >
            <div class="col">
                <button dusk="startAutopilotButton" type="button"  class="btn btn-primary" @click.prevent="loadNextOrder">
                    Start AutoPilot Packing
                </button>
            </div>
        </div>

        <div v-if="finished" class="m-auto text-center">
            You've finished packing all orders!<br>
            <span class="small">There are no more orders to pack with specified filters</span>
        </div>

        <template v-for="order in orders">
            <packsheet-page
                :key="'order_id_' + order.id"
                :order_id="order.id"
                :previous_order_id="previous_order_id"
                @orderCompleted="loadNextOrder">
            </packsheet-page>
        </template>
    </div>
</template>

<script>
    import url from "../mixins/url";
    import api from "../mixins/api";
    import beep from "../mixins/beep";
    import Vue from "vue";
    import helpers from "../mixins/helpers";
    import loadingOverlay from "../mixins/loading-overlay";

    export default {
        mixins: [loadingOverlay, api, beep, url, helpers],

        data: function() {
            return {
                finished: false,
                orders: [],
                order_id: null,
                previous_order_id: null,
            };
        },

        mounted() {
            if(Vue.prototype.$currentUser['warehouse_id']) {
                this.setUrlParameter('inventory_source_warehouse_id', Vue.prototype.$currentUser['warehouse_id']);
            }
        },

        methods: {
            loadNextOrder() {
                if(! Vue.prototype.$currentUser['warehouse_id']) {
                    this.notifyError('User does not have warehouse assigned! Please assign in Settings->User');
                    return;
                }

                this.showLoading();

                this.orders = [];

                let params = {
                    'filter[status]': this.getUrlParameter('status',''),
                    'filter[inventory_source_warehouse_id]': this.getUrlParameter('inventory_source_warehouse_id', this.currentUser()['warehouse_id']),
                    'sort': this.getUrlParameter('sort', 'order_placed_at'),
                };

                this.apiGetPacklistOrder(params)
                    .then(({data}) => {
                        this.previous_order_id = this.order_id;

                        // we use array here so we can use v-for component
                        // and auto destroy when loading next order
                        this.orders = [data.data];
                        this.order_id = data.data['id'];
                        this.hideLoading();
                    })
                    .catch((error) => {
                        this.hideLoading();

                        let msg = error.response.data.errors;

                        if (error.response.status === 404) {
                            this.finished = true;
                            // msg = "No orders available with specified filters"
                            return;
                        }
                        this.notifyError(msg);
                        this.errorBeep();
                    })
            },
        },
    }
</script>


<style lang="scss">

</style>
