<template>
    <div v-if="order">
        <div class="card">
            <div class="row rounded p-2 ">
                <div class="col-lg-6">
                    <h5 class="text-primary">
                        <font-awesome-icon icon="copy" class="fa-xs" role="button" @click="copyToClipBoard(order['order_number'])"></font-awesome-icon>
                        <a :href="'/orders/?search=' + order['order_number']">{{ order['order_number'] }}</a>
                    </h5>

                    <div class="small font-weight-bold">{{ formatDateTime(order['order_placed_at']) }}</div>
                    <div class="small font-weight-bold">{{ order['status_code'] }}</div>
                    <div class="small">{{ order['label_template'] ? order['label_template'] : '&nbsp;' }}</div>

                    <div class="small">Shipping Numbers:
                        <template v-for="shipment in order['order_shipments']">
                            <a :href="shipment['tracking_url']" target="_blank" class="text-wrap mr-1">
                                {{ shipment['shipping_number'] }}
                            </a>
                        </template>
                    </div>
                </div>
                <div class="col-lg-6 text-right small">
                    <number-card :number="order['age_in_days']" label="age"/>
                    <div class="d-none d-md-inline-block p-1" v-bind:class="{ 'bg-warning': order['total_paid'] < 0.01 }">
                        <div class="text-center w-100 text-secondary small">
                            <small>total paid</small>
                        </div>
                        <span class="pr-0 mr-2 h5 w-100">{{
                                Math.floor(order['total_paid']) }}<span class="" style="font-size: 8pt"><template
                                v-if="(order['total_paid']) % 1 === 0"> .00</template><template
                                v-if="(order['total_paid']) % 1 > 0"> .{{ Math.floor((order['total_paid']) % 1 * 100) }} </template></span></span>
                    </div>
                    <number-card :number="order['order_products_totals']['count']" label="lines"/>
                    <number-card :number="order['order_products_totals']['quantity_ordered']" label="ordered"/>
                    <div class="d-none d-md-inline-block">
                        <number-card :number="order['order_products_totals']['quantity_picked']" label="picked" ></number-card>
                        <number-card :number="order['order_products_totals']['quantity_shipped']" label="shipped" ></number-card>
                    </div>
                    <number-card :number="order['order_products_totals']['quantity_to_ship']" label="to ship"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import helpers from "../../mixins/helpers";

    export default {
        name: "OrderDetails",
        mixins: [helpers],

        props: {
            order: Object,
        },
    }
</script>
