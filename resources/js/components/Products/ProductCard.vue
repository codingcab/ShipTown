<template>
    <div>
        <div class="row card p-2" >
            <div class="col pl-1">
                <div class="row text-left">
                    <div class="col-md-6 col-lg-7 mb-2">
                        <product-info-card :product= "product"></product-info-card>
                    </div>
                    <div class="col-md-6 col-lg-5">
                        <div class="table-responsive small" @click="toggle">
                            <table class="table table-borderless mb-0 w-100 text-right">
                               <thead>
                                  <tr class="small font-weight-bold">
                                      <th class="text-left">Location</th>
                                      <th class="text-left">Shelf</th>
                                      <th class="text-right">Available</th>
                                      <th class="text-right d-none d-md-table-cell">Reserved</th>
                                      <th class="text-right pr-1">Incoming</th>
                                      <th class="text-right d-none d-md-table-cell pr-1">Required</th>
                                      <th class="text-right">Price</th>
                                      <th class="text-right">7 day</th>
                                  </tr>
                               </thead>
                               <tbody>
                                   <template v-for="inventory in product.inventory" >
                                      <tr class="" v-bind:class="{ 'table-active': currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code']}">
                                          <td class="text-left"><a class="text-primary cursor-pointer" @click.prevent="showInventoryMovementModal(inventory['id'])">{{ inventory['warehouse_code'] }}</a></td>
                                          <td class="text-left">{{ inventory['shelf_location'] }}</td>
                                          <td>{{ toNumberOrDash(inventory['quantity_available'])}}</td>
                                          <td class="d-none d-md-table-cell">{{ toNumberOrDash(inventory['quantity_reserved'])}}</td>
                                          <td class="pr-1">{{ toNumberOrDash(inventory['quantity_incoming']) }}</td>
                                          <td class="d-none d-md-table-cell pr-1">{{ toNumberOrDash(inventory['quantity_required']) }}</td>
                                          <td class="ml-2 pl-2" :class="{ 'bg-warning': product.prices[inventory['warehouse_code']]['is_on_sale'] === true }">{{ toNumberOrDash(product.prices[inventory['warehouse_code']]['current_price'], 2) }}</td>
                                          <td class="ml-2">
                                              <template v-for="inventory_statistic in product['inventoryMovementsStatistics']">
                                                <div v-if="inventory_statistic['type'] === 'sale' && inventory['warehouse_code'] === inventory_statistic['warehouse_code']">{{ toNumberOrDash( inventory_statistic['last7days_quantity_delta'] * (-1) ) }}</div>
                                              </template>
                                          </td>
                                      </tr>
                                   </template>
                               </tbody>
                            </table>
                        </div>

                        <div @click="toggle" class="row-col text-center text-secondary" >
                            <font-awesome-icon v-if="showDetails" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                            <font-awesome-icon v-if="!showDetails" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                        </div>

                        <div class="row-col" v-if="showDetails">
                            <div class="row-col tabs-container mb-2">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link p-0 pl-1 pr-1 pr-lg-2 active"  @click.prevent="currentTab = 'inventory'" data-toggle="tab" href="#">
                                            Inventory
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link p-0 pl-1 pr-1 pr-lg-2"  @click.prevent="currentTab = 'prices'" data-toggle="tab" href="#">
                                            Pricing
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link p-0 pl-1 pr-1 pr-lg-2"  @click.prevent="currentTab = 'aliases'" data-toggle="tab" href="#">
                                            Aliases
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link p-0 pl-1 pr-1 pr-lg-2"  @click.prevent="currentTab = 'activityLog'" data-toggle="tab" href="#">
                                            Activity
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a v-if="sharingAvailable()" @click.prevent="shareLink" class="nav-link p-0 pl-1 pr-1 pr-lg-2" href="#">
                                            <font-awesome-icon icon="share-alt" class="fas fa-sm"></font-awesome-icon>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <template v-if="currentTab === 'inventory'">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0 w-100 text-right small">
                                        <thead>
                                        <tr class="small font-weight-bold">
                                            <th class="text-left">Warehouse</th>
                                            <th class="d-table-cell d-md-none">RL</th>
                                            <th class="d-none d-md-table-cell">Restock Level</th>

                                            <th class="d-table-cell d-md-none pl-2">RP</th>
                                            <th class="d-none d-md-table-cell">Reorder Point</th>

                                            <th>In Stock</th>
                                            <th class="d-none d-md-table-cell">Reserved</th>
                                            <th>Incoming</th>
                                            <th>Required</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-for="inventory in product.inventory" >
                                            <tr class="" v-bind:class="{ 'table-active': currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code']}">
                                                <td class="text-left">{{ inventory['warehouse_code'] }}</td>
                                                <td>{{ toNumberOrDash(inventory['restock_level']) }}</td>
                                                <td>{{ toNumberOrDash(inventory['reorder_point']) }}</td>
                                                <td>{{ toNumberOrDash(inventory['quantity'])}}</td>
                                                <td class="d-none d-md-table-cell">{{ toNumberOrDash(inventory['quantity_reserved'])}}</td>
                                                <td>{{ toNumberOrDash(inventory['quantity_incoming']) }}</td>
                                                <td>{{ toNumberOrDash(inventory['quantity_required']) }}</td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>

                                    <hr>

                                    <div class="row col d-block font-weight-bold pb-1 text-uppercase small text-secondary align-content-center text-center">ORDERS</div>

                                    <template>
                                        <div>
                                            {{ statusMessageOrder }}
                                        </div>
                                        <div v-if="!orders.length" class="text-center text-secondary small">
                                            No orders found
                                        </div>
                                        <div v-for="orderProduct in orders" :key="orderProduct.id">
                                            <div class="row text-left mb-2">
                                                <div class="col">
                                                    <div>
                                                        <a target="_blank" :href="getProductLink(orderProduct)">
                                                            #{{ orderProduct['order']['order_number']}}
                                                        </a>
                                                    </div>
                                                    <div class="small">
                                                        {{ formatDateTime(orderProduct['order']['order_placed_at'], 'MMM DD') }}
                                                    </div>
                                                    <div class="small">
                                                        {{ orderProduct['order']['status_code']}}
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="row justify-content-end text-center">
                                                        <div class="cold d-none d-sm-block">
                                                            <small>ordered</small>
                                                            <h3>{{ Math.ceil(orderProduct['quantity_ordered']) }}</h3>
                                                        </div>
                                                        <div class="col">
                                                            <small>picked</small>
                                                            <h3>{{ dashIfZero(Number(orderProduct['quantity_picked'])) }}</h3>
                                                        </div>
                                                        <div class="col">
                                                            <small>skipped</small>
                                                            <h3>{{ dashIfZero(Number(orderProduct['quantity_skipped_picking'])) }}</h3>
                                                        </div>
                                                        <div class="col d-none d-sm-block">
                                                            <small>shipped</small>
                                                            <h3>{{ dashIfZero(Number(orderProduct['quantity_shipped']))  }}</h3>
                                                        </div>
                                                        <div class="col">
                                                            <small>to ship</small>
                                                            <h3>{{ dashIfZero(Number(orderProduct['quantity_to_ship']))  }}</h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </template>

                            <template v-if="currentTab === 'prices'">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0 w-100 small">
                                        <thead>
                                        <tr class="small font-weight-bold">
                                            <th>Location</th>
                                            <th class="text-right pr-1">Price</th>
                                            <th class="text-right">Sale Price</th>
                                            <th class="text-right">Start Date</th>
                                            <th class="text-right">End Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-for="price in product['prices']">
                                            <tr :key="price.id" v-bind:class="{ 'table-active': currentUser()['warehouse'] && price['warehouse_code'] === currentUser()['warehouse']['code']}" >
                                                <td>{{ price['warehouse_code'] }}</td>
                                                <td class="text-right pr-1">{{ price['price'] }}</td>
                                                <td class="text-right" :class="{ 'bg-warning': price['is_on_sale'] }">{{ price['sale_price'] }}</td>
                                                <td class="text-right">{{ formatDateTime(price['sale_price_start_date'], 'YYYY MMM D') }}</td>
                                                <td class="text-right">{{ formatDateTime(price['sale_price_end_date'], 'YYYY MMM D') }}</td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template v-if="currentTab === 'aliases'">
                                <div class="container">

                                    <div v-for="alias in product.aliases" :key="alias.id">
                                        <div class="badge mb-2">{{ alias.alias }}</div>
                                    </div>
                                </div>
                            </template>


                            <template v-if="currentTab === 'activityLog'">
                              <div class="row small" v-for="activity in activityLog" :key="activity.id">
                                      <span :title="formatDateTime(activity['created_at'], 'YYYY-MM-DD H:mm:ss')">
                                          {{ formatDateTime(activity['created_at'], 'MMM DD H:mm')  }}:
                                      </span>
                                <span class="flex-nowrap ml-1">
                                          {{ activity['causer'] === null ? 'AutoPilot' : activity['causer']['name'] }}
                                      </span>
                                <span class="flex-nowrap ml-1">
                                          {{ activity['description'] }}
                                      </span>
                                <div class="col-12 pl-3 text-nowrap" v-for="(value, name) in activity['properties']['attributes'] ? activity['properties']['attributes'] : activity['properties']">
                                  {{ name }} = {{ value }}
                                </div>
                              </div>
                            </template>

                        </div>
                    </div>
                </div>

                <div class="row" v-if="showDetails">
                    <div class="col offset-md-6 offset-lg-5">
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";

    export default {
        name: "ProductCard",

        mixins: [api, helpers],

        props: {
            product: Object,
            expanded: false,
            ordered: 0,
        },

        mounted: function () {
          this.currentTab = 'inventory';
        },

        watch: {
            currentTab() {
                switch (this.currentTab) {
                    case 'orders':
                        if(!this.activeOrderProducts.length){
                            this.loadActiveOrders();
                            this.loadCompletedOrders(10);
                        }
                        break;
                    case 'activityLog':
                        if(!this.activityLog.length){
                            this.loadActivityLog();
                        }
                        break;
                    default:
                        break;
                }
            }
        },

        data: function() {
            return {
                statusMessageOrder: '',
                statusMessageActivity: '',
                activityLog: [],
                currentTab: '',
                showDetails: false,
                activeOrderProducts: [],
                completeOrderProducts: []
            };
        },

        computed: {
            orders() {
                return this.activeOrderProducts.concat(this.completeOrderProducts)
            },

            quantityOrdered() {
                return this.ordered
            },
        },

        created: function () {
            if (this.expanded) {
                this.toggle();
            }
        },

        methods: {
            showInventoryMovementModal(inventory_id) {
                this.$modal.showRecentInventoryMovementsModal(inventory_id);
            },

            movementsReportLink(warehouse_code) {
              return '/reports/inventory-movements?filter[created_at_between]=&filter[warehouse_code]=' + warehouse_code + '&filter[search]=' + this.product['sku']
            },

            soldLast7Days(warehouse_id) {
              const soldLast7DaysArray = this.product['inventoryMovementsStatistics'][warehouse_id];

              if (soldLast7DaysArray === null) {
                return 0;
              }

              return soldLast7DaysArray['quantity_sold']
            },

            hide() {
                $('#filterConfigurationModal').modal('hide');
            },

            orderProduct(quantity) {
                console.log(this.$ref);
                this.ordered = quantity;
                this.hide();
            },

            sharingAvailable() {
                return navigator.share;
            },

            shareLink() {
                navigator.share({
                    url: '/products?search=' + this.product['sku'],
                    title: document.title
                });
            },

            toggle() {
                this.showDetails = !this.showDetails;
                if (this.showDetails) {
                    this.loadActiveOrders();
                    this.loadCompletedOrders(10);
                    this.currentTab = 'inventory';
                }
            },

            loadActiveOrders: function () {
                this.statusMessageOrder = "Loading orders ..."
                const params = {
                    'filter[product_id]': this.product['id'],
                    'filter[order.is_active]': true,
                    'sort': 'id',
                    'include': 'order',
                    'per_page': 999
                }
                this.apiGetOrderProducts(params)
                    .then(({data}) => {
                        this.statusMessageOrder = '';
                        this.activeOrderProducts = data.data;
                    });
            },

            loadCompletedOrders: function (count = 5) {
                const params = {
                    'filter[product_id]': this.product['id'],
                    'filter[order.is_active]': false,
                    'sort': '-id',
                    'include': 'order',
                    'per_page': count
                }
                this.apiGetOrderProducts(params)
                    .then(({data}) => {
                        this.statusMessageOrder = '';
                        this.completeOrderProducts = data.data;
                    });
            },

            loadActivityLog: function () {
                this.statusMessageActivity = "Loading activities ...";
                const params = {
                    'filter[subject_type]': 'App\\Models\\Product',
                    'filter[subject_id]': this.product['id'],
                    'sort': '-id',
                    'include': 'causer',
                    'per_page': 100
                }

                this.apiGetActivityLog(params)
                    .then(({data}) => {
                        this.statusMessageActivity = '';
                        this.activityLog = data.data
                        if (this.activityLog.length === 0) {
                            this.statusMessageActivity = 'No activities found';
                        }
                    });
            },

            dashIfZero(value) {
                return value === 0 ? '-' : value;
            },

            getProductLink(orderProduct) {
                return '/orders?search=' + orderProduct['order']['order_number'];
            },

            getProductQuantity(orderProduct) {
                return orderProduct['product'] ? Number(orderProduct['product']['quantity']) : -1;
            },

            ifHasEnoughStock(orderProduct) {
                return this.getProductQuantity(orderProduct) < Number(orderProduct['quantity_ordered']);
            }
        }
    }
</script>

<style scoped>
li {
    margin-right: 5px;
}
.badge {
    font-family: "Lato",-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;
}
.table th, .table td {;
    padding: 0.0rem;
}

.btn:active{
    background-color: rgb(94, 79, 126);
    border-color:rgb(94, 79, 126);
    box-shadow: 0 1px 1px rgba(255, 255, 255, 0.075) inset, 0 0 8px rgba(114, 96, 153, 0.6);
    outline: 0 none;
}

</style>
