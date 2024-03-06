<template>

    <div class="swiper-container">
        <div class="swiper-wrapper">
            <div class="swiper-slide p-1">
                <div class="card">
                    <div class="row p-2 pl-2 rounded">

                        <div class="col-lg-5">
                            <h5 class="text-primary">
                                <font-awesome-icon icon="copy" class="fa-xs" role="button"
                                                   @click="copyToClipBoard(order['order_number'])"></font-awesome-icon>
                                <a :href="'/orders/?search=' + order['order_number']">{{ order['order_number'] }}</a>
                            </h5>

                            <div>
                                <div class="small font-weight-bold">{{ order['status_code'] }}</div>
                                <div class="small">{{ order['label_template'] }}</div>

                                <div class="small">Shipping Numbers:
                                    <template v-for="shipment in order['order_shipments']">
                                        <a :href="shipment['tracking_url']" target="_blank" class="text-wrap mr-1">
                                            {{ shipment['shipping_number'] }}
                                        </a>
                                    </template>
                                </div>
                                <template v-for="tag in order.tags">
                                    <a class="badge text-uppercase" :key="tag.id" :href="'orders?has_tags=' + tag.name">
                                        {{ tag.name }} </a>
                                </template>
                            </div>
                        </div>

                        <div class="col-lg-7 align-text-top mt-1">
                            <div v-if="order['order_products_totals']" class="col text-center" @click="toggleOrderDetails">
                                <div class="d-flex flex-nowrap justify-content-between">
                                    <div>
                                        <number-card label="age" :number="order['age_in_days']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card label="lines" :number="order['order_products_totals']['count']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="d-none d-md-block" v-bind:class="{ 'bg-warning': order['total_paid'] < 0.01 }">
                                        <div class="text-center w-100 text-secondary small">
                                            <small>total paid</small>
                                        </div>
                                        <h4 class="text-center">
                                            {{ financial(order.total_paid).split('.')[0] }}<!--
                                                --><span style="font-size: 8pt">.{{ financial(order.total_paid).split('.')[1] }}</span>
                                        </h4>
                                    </div>
                                    <div class="d-none d-sm-block">
                                        <number-card label="ordered" :number="order['order_products_totals']['quantity_ordered']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="bg-warning" v-if="Number(order['order_products_totals']['quantity_split']) > 0">
                                        <number-card label="split" :number="order['order_products_totals']['quantity_split']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card label="picked" :number="order['order_products_totals']['quantity_picked']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="bg-warning" v-if="Number(order['order_products_totals']['quantity_skipped_picking']) > 0">
                                        <number-card label="skipped" :number="order['order_products_totals']['quantity_skipped_picking']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="d-none d-md-block">
                                        <number-card label="shipped" :number="order['order_products_totals']['quantity_shipped']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card label="to ship" :number="order['order_products_totals']['quantity_to_ship']" :min-width="'50px'"></number-card>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 small"
                                 v-if="order['order_comments'].length > 0 && orderDetailsVisible === false">
                                <b>{{
                                        order['order_comments'][0]['user'] ? order['order_comments'][0]['user']['name'] : 'AutoPilot'
                                    }}:</b> {{ order['order_comments'][0]['comment'] }}
                            </div>

                            <div class="row text-center text-secondary"
                                 @click="toggleOrderDetails">
                                <div class="col">
                                    <font-awesome-icon v-if="orderDetailsVisible" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                                    <font-awesome-icon v-if="!orderDetailsVisible" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                                </div>
                            </div>

                            <div v-if="orderDetailsVisible">
                                <div class="row mb-2 mt-1">
                                    <input ref="newCommentInput" v-model="input_comment" class="form-control"
                                           placeholder="Add comment here" @keypress.enter="addComment"/>
                                </div>

                                <template v-for="order_comment in order['order_comments']">
                                    <div class="row mb-2">
                                        <div class="col">
                                            <b>{{
                                                    order_comment['user'] ? order_comment['user']['name'] : 'AutoPilot'
                                                }}:</b> {{ order_comment['comment'] }}
                                        </div>
                                    </div>
                                </template>

                                <div class="row tabs-container mb-2 mt-2 small">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                               @click.prevent="currentTab = 'productsOrdered'">
                                                Products
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                               @click.prevent="currentTab = 'orderDetails'">
                                                Details
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                               @click.prevent="currentTab = 'shippingAddress'">
                                                Address
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                               @click.prevent="currentTab = 'orderActivities'">
                                                Activity
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" target="_blank"
                                               :href="'/order/packsheet/' + order['id'] + '?hide_nav_bar=true'">
                                                Packsheet
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a v-if="sharingAvailable()" class="nav-link p-0 pl-1 pr-1"
                                               @click.prevent="shareLink" href="#">
                                                <font-awesome-icon icon="share-alt"
                                                                   class="fas fa-sm"></font-awesome-icon>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div v-if="currentTab === 'productsOrdered'">
                                    <template v-for="order_product in order_products">

                                        <div class="row text-left mb-2">
                                            <div class="col-12">
                                                <small>{{ order_product['name_ordered'] }} &nbsp;</small>
                                                <div class="small"><a v-if="order_product['product_id']" target="_blank" :href="getProductLink(order_product)">{{order_product['sku_ordered'] }}</a><div v-if="order_product['product_id'] === null" class="bg-warning">{{order_product['sku_ordered'] }}</div>&nbsp;
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="row text-center">
                                                    <div class="col">
                                                        <number-card label="ordered"
                                                                     :number="order_product['quantity_ordered']"></number-card>
                                                    </div>
                                                    <div class="col bg-warning"
                                                         v-if="Number(order_product['quantity_split']) > 0">
                                                        <number-card label="split"
                                                                     :number="order_product['quantity_split']"></number-card>
                                                    </div>
                                                    <div class="col d-none d-md-block text-right">
                                                        <div class="text-center w-100 text-secondary small">
                                                            <small class="small text-secondary">unit price</small>
                                                        </div>
                                                        <span class="pr-0 mr-2 h4">{{
                                                                Math.floor(order_product['price'])
                                                            }}<span class="ml-0 pl-0" style="font-size: 8pt"><template
                                                                v-if="order_product['price'] % 1 === 0"> .00</template><template
                                                                v-if="order_product['price'] % 1 > 0"> .{{
                                                                    Math.floor(order_product['price'] % 1 * 100)
                                                                }} </template></span></span>
                                                    </div>
                                                    <div class="col">
                                                        <number-card label="picked"
                                                                     :number="order_product['quantity_picked']"></number-card>
                                                    </div>
                                                    <div class="col bg-warning"
                                                         v-if="Number(order_product['quantity_skipped_picking']) > 0">
                                                        <number-card label="skipped"
                                                                     :number="order_product['quantity_skipped_picking']"></number-card>
                                                    </div>
                                                    <div class="col d-none d-sm-block">
                                                        <number-card label="shipped"
                                                                     :number="order_product['quantity_shipped']"></number-card>
                                                    </div>
                                                    <div class="col">
                                                        <number-card label="to ship"
                                                                     :number="order_product['quantity_to_ship']"></number-card>
                                                    </div>
                                                    <div class="col"
                                                         v-bind:class="{ 'bg-warning': ifHasEnoughStock(order_product) }">
                                                        <number-card label="inventory "
                                                                     :number="getProductQuantity(order_product)"></number-card>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <hr>
                                    </template>
                                </div>

                                <div class="container" v-if="currentTab === 'shippingAddress'">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table-borderless table-hover border-0">
                                                <tr>
                                                    <td class="font-weight-bold">email:</td>
                                                    <td><a
                                                        :href="'mailto:' + order['shipping_address']['email'] + '?subject=Order #' + order['order_number']">{{
                                                            order['shipping_address']['email']
                                                        }} </a></td>
                                                </tr>
                                                <tr class="pb-2">
                                                    <td class="font-weight-bold">phone:</td>
                                                    <td><a :href="'tel:' + order['shipping_address']['phone']">{{
                                                            order['shipping_address']['phone']
                                                        }}</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold"></td>
                                                    <td> &nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">first_name:</td>
                                                    <td> {{ order['shipping_address']['first_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">last_name:</td>
                                                    <td> {{ order['shipping_address']['last_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">company:</td>
                                                    <td> {{ order['shipping_address']['company'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">address1:</td>
                                                    <td> {{ order['shipping_address']['address1'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">address2:</td>
                                                    <td> {{ order['shipping_address']['address2'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">postcode:</td>
                                                    <td>{{ order['shipping_address']['postcode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">city:</td>
                                                    <td> {{ order['shipping_address']['city'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">country_code:</td>
                                                    <td> {{ order['shipping_address']['country_code'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">country_name:</td>
                                                    <td> {{ order['shipping_address']['country_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">fax:</td>
                                                    <td> {{ order['shipping_address']['fax'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">region:</td>
                                                    <td>{{ order['shipping_address']['region'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">state_code:</td>
                                                    <td>{{ order['shipping_address']['state_code'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">state_name:</td>
                                                    <td>{{ order['shipping_address']['state_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">website:</td>
                                                    <td>{{ order['shipping_address']['website'] }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="container" v-if="currentTab === 'orderDetails'">
                                    <div class="row">
                                        <div class="col">
                                            <table class="table-borderless table-hover border-0 w-100">
                                                <tr>
                                                    <td class="text-nowrap"> status code:</td>
                                                    <td class="text-right">
                                                        <select id="selectStatus" class="form-control" @change="changeStatus" v-model="order.status_code">
                                                            <option v-for="orderStatus in order_statuses" :value="orderStatus.code" :key="orderStatus.id">{{ orderStatus.code }}</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> shipping method:</td>
                                                    <td class="text-right"><b> {{ order['shipping_method_code'] }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> label template:</td>
                                                    <td class="text-right"><b> {{ order['label_template'] }} </b></td>
                                                </tr>


                                                <tr>
                                                    <td class="text-nowrap pt-2"> product lines:</td>
                                                    <td class="text-right"><b> {{ toNumberOrDash(order['order_products_totals']['count']) }} </b></td>
                                                </tr>

                                                <tr>
                                                    <td class="text-nowrap"> total products:</td>
                                                    <td class="text-right"><b> {{toNumberOrDash(order['total_products'])}} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> total shipping:</td>
                                                    <td class="text-right"><b> {{ toNumberOrDash(order['total_shipping']) }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> total discounts:</td>
                                                    <td class="text-right"><b> {{ toNumberOrDash(order['total_discounts']) }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> total order:</td>
                                                    <td class="text-right"><b> {{toNumberOrDash(order['total_order'])}} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> total paid:</td>
                                                    <td class="text-right"><b> {{ toNumberOrDash(order['total_paid']) }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap"> total outstanding:</td>
                                                    <td class="text-right"><b> {{ toNumberOrDash(order['total_outstanding']) }} </b></td>
                                                </tr>


                                                <tr>
                                                    <td class="text-nowrap pt-2"> packed by:</td>
                                                    <td class="text-right"><b> {{ order['packer'] ? order['packer']['name'] : '&nbsp' }} </b></td>
                                                </tr>


                                                <tr>
                                                    <td class="pt-2"> placed at:</td>
                                                    <td class="text-right"><b> {{ order['order_placed_at'] | moment('MMM DD H:mm') }} </b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> picked at:</td>
                                                    <td class="text-right"><b> {{ order['picked_at'] | moment('MMM DD H:mm') }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td> packed at:</td>
                                                    <td class="text-right"><b> {{ (order['packed_at']) | moment('MMM DD H:mm') }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td> closed at:</td>
                                                    <td class="text-right"><b> {{ (order['order_closed_at']) | moment('MMM DD H:mm') }} </b></td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"> Shipping Numbers:</td>
                                                </tr>
                                                <template v-for="shipment in order_shipments">
                                                    <tr>
                                                        <td colspan="2">
                                                            <b>
                                                                {{ formatDateTime(shipment['created_at']) }}
                                                            </b>
                                                            <a :href="shipment['tracking_url']" target="_blank">
                                                                {{ shipment['shipping_number'] }}
                                                            </a>
                                                            by {{ shipment['user'] ? shipment['user']['name'] : '' }}
                                                            <a class="btn btn-link btn-sm small" :href="shippingContentUrl(shipment)" target="_blank">
                                                                <font-awesome-icon icon="file-download" />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </table>
                                            <div>&nbsp;</div>
                                        </div>
                                    </div>
                                </div>

                                <template v-if="currentTab === 'orderActivities'">
                                    <div class="row small" v-for="activity in order_activities" :key="activity.id">
                                        <span :title="formatDateTime(activity['created_at'], 'YYYY-MM-DD H:mm:ss')">
                                            {{ formatDateTime(activity['created_at'], 'MMM DD H:mm') }}:
                                        </span>
                                        <span class="flex-nowrap ml-1">
                                            {{ activity['causer'] === null ? 'AutoPilot' : activity['causer']['name'] }}
                                        </span>
                                        <span class="flex-nowrap ml-1">
                                            {{ activity['description'] }}
                                        </span>
                                        <div class="col-12 pl-3 text-nowrap"
                                             v-for="(value, name) in activity['properties']['attributes'] ? activity['properties']['attributes'] : activity['properties']">
                                            {{ name }} = {{ value }}
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="spacer-bottom" class="row mb-4 mt-4" v-if="orderDetailsVisible"></div>
            </div>
        </div>
    </div>

</template>

<script>
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import Vue from "vue";

export default {
    mixins: [api, helpers, url],
    name: "OrderCard",

    props: {
        order: Object,
        expanded: false,
    },


    data: function () {
        return {
            input_comment: '',
            orderDetailsVisible: false,

            currentTab: 'productsOrdered',

            order_products: [],
            order_comments: null,
            order_activities: null,
            order_shipments: null,
            order_statuses: null,
        }
    },

    mounted() {
        // we pre creating array of empty products so UI will display empty row for each orderProduct
        // its simply more pleasant to eye and card doesnt "jump"
        for (let i = 0; i < this.order['product_line_count']; i++)
            this.order_products.unshift([]);
    },

    created: function () {
        if (this.expanded) {
            this.toggleOrderDetails();
        }
    },

    watch: {
        expanded() {
            if (this.expanded) {
                this.toggleOrderDetails();
            }
        },
    },

    methods: {
        changeStatus() {
            this.apiUpdateOrder(this.order['id'], {'status_code': this.order.status_code})
                .catch(() => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order.id,
                        'description': 'Error when changing status'
                    });
                    this.notifyError('Error occurred when changing status');
                });
        },

        loadOrderStatuses(){
            this.apiGetOrderStatus({
                'filter[hidden]': 0,
                'per_page': 999,
                'sort': 'code'
            })
                .then(({ data }) => {
                    this.order_statuses = data.data;
                })
        },

        formatPrice: function (price) {
            if (price === 0) {
                return '-';
            }

            return price.toFixed();
        },

        shippingContentUrl: function (shipment) {
            return '/shipping-labels/' + shipment['id'];
        },

        sharingAvailable() {
            return navigator.share;
        },

        shareLink() {
            navigator.share({
                url: '/orders?search=' + this.order['order_number'],
                title: document.title
            });
        },

        toggleOrderDetails() {
            if (this.orderDetailsVisible) {
                this.orderDetailsVisible = false;
                return;
            }

            this.loadOrderProducts()
            this.loadOrderActivities();
            this.loadOrderShipments();
            this.loadOrderStatuses();

            this.orderDetailsVisible = true;
        },

        loadOrderProducts() {
            let params = {
                'filter[warehouse_id]': this.getUrlParameter('warehouse_id'),
                'filter[order_id]': this.order['id'],
                'include': 'product',
                'per_page': '999',
            };

            this.apiGetOrderProducts(params)
                .then(({data}) => {
                    this.order_products = data.data;
                })

            return this;
        },

        loadOrderComments() {
            if (this.order_comments) {
                return this;
            }

            let params = {
                'filter[order_id]': this.order['id'],
                'include': 'user',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetOrderComments(params)
                .then(({data}) => {
                    this.order_comments = data.data;
                })

            return this;
        },

        loadOrderActivities() {
            if (this.order_activities) {
                return this;
            }

            let params = {
                'filter[subject_id]': this.order['id'],
                'filter[subject_type]': 'App\\Models\\Order',
                'include': 'causer',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetActivityLog(params)
                .then(({data}) => {
                    this.order_activities = data.data;
                })

            return this;
        },

        loadOrderShipments() {
            if (this.order_shipments) {
                return this;
            }

            let params = {
                'filter[order_id]': this.order['id'],
                'include': 'user',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetOrderShipments(params)
                .then(({data}) => {
                    this.order_shipments = data.data;
                })

            return this;
        },

        hasSkippedPick(orderProduct) {
            return Number(orderProduct['quantity_skipped_picking']) > 0;
        },

        addComment() {
            let data = {
                "order_id": this.order['id'],
                "comment": this.input_comment
            };

            this.apiPostOrderComment(data)
                .then(({data}) => {
                    // quick hack to immediately display comment
                    this.order['order_comments'].unshift(data['data'][0])
                    this.loadOrderComments();
                    this.input_comment = '';
                })
        },

        getProductLink(orderProduct) {
            const searchTerm = orderProduct['product'] ? orderProduct['product']['sku'] : orderProduct['sku_ordered'];
            return '/products?filter[sku]=' + searchTerm + '&hide_nav_bar=true';
        },

        getProductQuantity(orderProduct) {
            if (this.getUrlParameter('warehouse_id')) {
                return orderProduct['inventory_source_quantity']
            }
            return orderProduct['product'] ? Number(orderProduct['product']['quantity']) : 0;
        },

        ifHasEnoughStock(orderProduct) {
            return this.getProductQuantity(orderProduct) < Number(orderProduct['quantity_to_ship']);
        }
    },
}
</script>

<style scoped>

.header-row > div, .col {
    /*border: 1px solid #76777838;*/
}

.col {
    background-color: #ffffff;
    /*border: 1px solid #76777838;*/
}

.nav-item {
    margin-right: unset;
}
</style>
