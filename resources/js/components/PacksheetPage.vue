<template>
    <div v-if="order">
        <div class="row" v-if="showMultipackerWarning" >
            <div class="col">
                <div class="alert alert-danger" role="alert">
                    This order is already opened by someone else. Be careful
                </div>
            </div>
        </div>

        <div v-if="order && order['is_packed']" class="row" >
            <div class="col">
                <div class="alert alert-danger" role="alert">
                    Order already packed...
                </div>
            </div>
        </div>

        <div v-if="!isLoading">
            <div class="row-col mb-2">
                <order-details :order="order" />
            </div>

            <div class="row m-1 pb-2 sticky-top bg-light" style="z-index: 10;">
                <div class="flex-fill">
                    <barcode-input-field :input_id="'barcode-input'" @barcodeScanned="packBarcode" placeholder="Enter sku or alias to ship 1 piece" ref="barcode"/>
                </div>

                <button type="button" v-b-modal="'filtersModal'" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
            </div>

            <div v-show="manuallyExpandComments" class="row mx-1 my-2">
                <input id="comment-input" ref="newCommentInput" v-model="input_comment" class="form-control" placeholder="Add comment here" @keypress.enter="addComment"/>
            </div>
            <div class="my-1" v-if="commentsToShow.length">
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

            <template v-if="orderProducts.length === 0" >
                <div class="row mb-3" >
                    <div class="col">
                        <div class="alert alert-info" role="alert">
                            No products found
                        </div>
                    </div>
                </div>
            </template>

            <template v-for="record in packlist">
                <div class="row mb-3">
                    <div class="col">
                        <packlist-entry :picklistItem="record" :key="record.id" @swipeRight="shipAll" @swipeLeft="shipPartialSwiped"/>
                    </div>
                </div>
            </template>

            <template v-for="record in packed">
                <div class="row mb-3">
                    <div class="col">
                        <packed-entry :picklistItem="record" :key="record.id" @swipeLeft="shipPartialSwiped" />
                    </div>
                </div>
            </template>
        </div>

        <b-modal ref="shippingNumberModal2" no-fade hide-footer hide-header dusk="shippingNumberModal"
                 @shown="setFocusElementById('shipping_number_input')"
                 @hidden="setFocusOnBarcodeInput()">
            <input id="shipping_number_input" class="form-control" placeholder="Scan shipping number"
                   v-model="shippingNumberInput"
                   @keyup.enter.prevent="addShippingNumber"/>
            <hr>
            <div class="text-right">
                <button type="button" @click.prevent="closeAskForShippingNumberModal" class="btn btn-secondary">Cancel</button>
                <button type="button" @click.prevent="addShippingNumber" class="btn btn-primary">OK</button>
            </div>
        </b-modal>

        <b-modal id="filtersModal" ref="filtersModal" no-fade hide-footer hide-header
                 @shown="setFocusElementById('stocktake-input')"
                 @hidden="modalHidden">
                <stocktake-input></stocktake-input>
                <hr>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="selectStatus" class="form-control" @change="changeStatus" v-model="order.status_code">
                        <option v-for="orderStatus in orderStatuses" :value="orderStatus.code" :key="orderStatus.id">{{ orderStatus.code }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Courier</label>
                    <select id="courierSelect" class="form-control" @change="updateLabelTemplate" v-model="order.label_template">
                        <option :value="''"></option>
                        <option v-for="shippingCourier in shippingCouriers" :value="shippingCourier.code" :key="shippingCourier.code">{{shippingCourier.code}}</option>
                    </select>
                </div>

                <button :disabled="order.label_template === ''" type="button" @click.prevent="printExtraLabelClick()" class="col btn mb-1 btn-primary">Print Courier Label</button>
                <button type="button" @click.prevent="printShippingLabel('address_label')" class="col btn mb-1 btn-primary">Print Address Label</button>
                <br>
                <br>
                <button type="button" class="col btn mb-1 btn-primary" @click.prevent="showShippingNumberRequestModal">Add Shipping Number</button>
                <br>
                <br>
                <button :disabled="previous_order_id === null" type="button" class="col btn btn-primary" @click.prevent="openPreviousOrder">Open Previous Order</button>
                <hr>
                <div class="text-right">
                    <button @click="closeFilersModal" type="button" class="btn btn-secondary">Cancel</button>
                    <button disabled type="button" class="btn btn-primary">OK</button>
                </div>
        </b-modal>

    </div>
</template>

    <script>
    import beep from '../mixins/beep';
    import loadingOverlay from '../mixins/loading-overlay';

    import PacklistEntry from './Packlist/PacklistEntry';
    import PackedEntry from './Packlist/PackedEntry';

    import OrderDetails from "./Packlist/OrderDetails";
    import BarcodeInputField from "./SharedComponents/BarcodeInputField";
    import FiltersModal from "./Packlist/FiltersModal";
    import url from "../mixins/url";
    import SetShippingNumberModal from "./Packlist/ShippingNumberModal";
    import api from "../mixins/api";
    import helpers from "../mixins/helpers";
    import Vue from "vue";

    export default {
            mixins: [loadingOverlay, beep, url, api, helpers, BarcodeInputField],

            components: {
                PacklistEntry,
                FiltersModal,
                BarcodeInputField,
                OrderDetails,
                PackedEntry,
                SetShippingNumberModal,
            },

            props: {
                order_id: null,
                previous_order_id: null,
            },

            data: function() {
                return {
                    order: null,
                    orderProducts: [],
                    orderStatuses: [],
                    shippingCouriers: [],

                    input_comment: '',
                    shippingNumberInput: '',

                    packlist: null,
                    packed: [],

                    canClose: true,
                    somethingHasBeenPackedDuringThisSession: false,
                    autoLabelAlreadyPrinted: false,

                    manuallyExpandComments: false,
                };
            },
            watch: {
                order() {
                    if (this.order === null) {
                        return;
                    }

                    if (this.somethingHasBeenPackedDuringThisSession === false) {
                        return;
                    }

                    if (this.order['order_products_totals']['quantity_to_ship'] > 0) {
                        return;
                    }

                    if (this.order['is_packed'] === true) {
                        return;
                    }

                    this.somethingHasBeenPackedDuringThisSession = false;

                    this.completeOrder();
                },
            },


            mounted() {
                if (! Vue.prototype.$currentUser['warehouse_id']) {
                    this.$snotify.error('You do not have warehouse assigned. Please contact administrator', {timeout: 50000});
                    return
                }
                this.setUrlParameter('warehouse_id', Vue.prototype.$currentUser['warehouse_id']);

                this.reloadData();

                this.loadOrderStatuses();
                this.loadShippingCouriers();

                this.reloadPageAfterInactivity();

                $('#shippingNumberModal2').modal();
            },

            methods: {
                modalHidden() {
                    this.setFocusElementById('barcode-input');
                    this.reloadData();
                },

                reloadData() {
                    this.loadOrder();
                    this.loadOrderProducts();
                },

                reloadPageAfterInactivity() {
                    let time = new Date().getTime();

                    const setActivityTime = (e) => {
                        if (new Date().getTime() - time >= 60 * 1000 * 5) {
                            this.loadOrder();
                        }

                        time = new Date().getTime();
                    }

                    document.body.addEventListener("scroll", setActivityTime);
                    document.body.addEventListener("focus", setActivityTime);
                    document.body.addEventListener("mousemove", setActivityTime);
                    document.body.addEventListener("keypress", setActivityTime);
                },

                addComment() {

                    let data = {
                        "order_id": this.order['id'],
                        "comment": this.input_comment
                    };

                    // quick hack to immediately display comment
                    this.order.order_comments.unshift(data);

                    this.apiPostOrderComment(data)
                        .then(() => {
                            this.loadOrder();
                            this.input_comment = '';
                            this.manuallyExpandComments = false;
                            this.setFocusElementById('barcode-input');

                        })
                        .catch((error) => {
                            // remove first comment if it was not saved
                            this.order.order_comments.shift();
                            console.log(error)
                            this.displayApiCallError(error);
                        });
                },

                toggleExpandComments() {
                    this.manuallyExpandComments = !this.manuallyExpandComments;
                    this.setFocusElementById(this.manuallyExpandComments ? 'comment-input': 'barcode-input', this.manuallyExpandComments);
                },

                checkIfPacker: async function() {
                    if (this.order === null) {
                        return;
                    }

                    this.apiGetActivityLog({
                            'filter[subject_type]': 'App\\Models\\Order',
                            'filter[subject_id]': this.order.id,
                            'filter[description]': 'Packsheet opened',
                            'sort': '-id',
                            'per_page': 1
                        })
                        .then(({data}) => {
                            const activity = data.data.pop();

                            if (activity['causer_id'] !== Vue.prototype.$currentUser['id']) {
                                this.order = null;
                                this.notifyError('Someone else opened packsheet for this order', {
                                    timeout: 0,
                                    buttons: [
                                        {
                                            text: 'OPEN PACKSHEET #' + this.order.order_number,
                                            action: (toast) => {
                                                window.location.href = '/orders?search=' + this.order.order_number;
                                            }
                                        },
                                    ],
                                })
                            }
                        });


                    setTimeout(() => {this.checkIfPacker();}, 10000);
                },

                completeOrder: async function () {
                    await this.markAsPacked();
                    await this.autoPrintLabelIfNeeded();

                    if (Vue.prototype.$currentUser['ask_for_shipping_number'] === true) {
                        this.showShippingNumberRequestModal();
                        return;
                    }

                    if((this.packlist.length === 0) && this.canClose) {
                        this.$emit('orderCompleted')
                    }
                },

                loadOrder: function () {
                    this.canClose = true;

                    let params = {
                        'filter[order_id]': this.order_id,
                        'include': 'order_products_totals,order_comments,order_comments.user,order_shipments',
                    };

                    return this.apiGetOrders(params)
                        .then(({data}) => {
                            this.order = data.data.length > 0 ? data.data[0] : null;
                        })
                        .catch((error) => {
                            this.displayApiCallError(error);
                        });
                },

                loadOrderProducts: function() {
                    const params = {
                        'filter[order_id]': this.order_id,
                        'filter[warehouse_id]': this.getUrlParameter('warehouse_id'),
                        'sort': 'inventory_source_shelf_location,sku_ordered',
                        'include': 'product,product.aliases',
                        'per_page': 999,
                    };

                    this.apiGetOrderProducts(params)
                        .then(({ data }) => {
                            this.orderProducts = data.data;

                            this.packed = this.orderProducts.filter(orderProduct => Number(orderProduct['quantity_to_ship']) === 0);
                            this.packlist = this.orderProducts.filter(orderProduct => Number(orderProduct['quantity_to_ship']) > 0);
                        })
                        .catch(() => {
                            this.notifyError('Error occurred while loading packlist');
                        });
                },

                loadOrderStatuses(){
                    this.apiGetOrderStatus({
                            'filter[hidden]': 0,
                            'per_page': 999,
                            'sort': 'code'
                        })
                        .then(({ data }) => {
                            this.orderStatuses = data.data;
                        })
                },

                loadShippingCouriers() {
                    this.apiGetShippingServices({
                        'per_page': 999,
                        'sort': 'code'
                    })
                    .then(({ data }) => {
                        this.shippingCouriers = data.data;
                    })
                },

                shipPartialSwiped(orderProduct) {
                    this.$snotify.prompt('Partial shipment', {
                        placeholder: 'Enter quantity to ship:',
                        position: 'centerCenter',
                        icon: false,
                        buttons: [
                            {
                                text: 'OK',
                                action: (toast) => {

                                    const maxQuantityAllowed = orderProduct['quantity_to_ship'];
                                    const minQuantityAllowed = orderProduct['quantity_shipped'] * (-1);

                                    if (
                                        isNaN(toast.value)
                                        || (toast.value > Number(maxQuantityAllowed))
                                        || (toast.value < Number(minQuantityAllowed))
                                    ) {
                                        toast.valid = false;
                                        return false;
                                    }

                                    this.$snotify.remove(toast.id);
                                    this.shipOrderProduct(orderProduct, Number(toast.value));
                                    this.setFocusElementById('barcode-input');
                                }
                            },
                            {
                                text: 'Cancel',
                                action: (toast) => {
                                    this.$snotify.remove(toast.id);
                                    this.setFocusElementById('barcode-input');
                                }
                            },
                        ],
                    });
                },

                changeStatus() {
                    this.$refs.filtersModal.hide();
                    this.setFocusElementById('barcode-input');

                    this.apiUpdateOrder(this.order['id'], {'status_code': this.order.status_code})
                        .then(() => {
                            this.reloadData();
                            this.notifySuccess('Status changed')
                        })
                        .catch(() => {
                            this.apiActivitiesPost({
                                'subject_type': 'order',
                                'subject_id': this.order.id,
                                'description': 'Error when changing status'
                            });
                            this.notifyError('Error when changing status');
                        });
                },

                showShippingNumberRequestModal() {
                    this.$refs.filtersModal.hide();
                    this.$refs.shippingNumberModal2.show();
                },

                closeAskForShippingNumberModal() {
                    this.$refs.shippingNumberModal2.hide();
                },

                closeFilersModal() {
                    this.$refs.filtersModal.hide();
                },

                addShippingNumber() {
                    if (this.shippingNumberInput === '') {
                        return;
                    }

                    this.$refs.shippingNumberModal2.hide();

                    let data = {
                        'order_id': this.order_id,
                        'shipping_number': this.shippingNumberInput,
                    };

                    this.apiPostOrderShipment(data)
                        .then(() => {
                            if(this.packlist.length === 0) {
                                this.$emit('orderCompleted')
                            }

                            this.notifySuccess('Shipping number saved');
                        })
                        .catch(() => {
                            this.apiActivitiesPost({
                                'subject_type': 'order',
                                'subject_id': this.order.id,
                                'description': 'Error saving shipping number, try again'
                            });
                            this.notifyError('Error saving shipping number, try again');
                        })
                },

                markAsPacked: async function () {
                    if (this.order['is_packed'] === true) {
                        return;
                    }

                    this.order['is_packed'] = true;
                    this.order['packer_user_id'] = Vue.prototype.$currentUser['id'];

                    return await this.apiUpdateOrder(this.order_id,{
                            'is_packed': true,
                            'packer_user_id': Vue.prototype.$currentUser['id']
                        })
                        .catch((error) => {
                            this.apiActivitiesPost({
                                'subject_type': 'order',
                                'subject_id': this.order_id,
                                'description': 'Error occurred when marking order as packed'
                            });
                            this.notifyError('Error: '+error.response.message);
                        });
                },

                shipOrderProduct(orderProduct, quantity) {
                    orderProduct.quantity_shipped += quantity;

                    if (orderProduct.product_id === null) {
                        this.notifyError('Product with SKU not found: "' + orderProduct.sku_ordered + '"');
                        return;
                    }

                    this.apiPostOrderProductShipment({
                            'sku_shipped': orderProduct.sku_ordered,
                            'product_id': orderProduct.product_id,
                            'order_id': orderProduct.order_id,
                            'order_product_id': orderProduct.id,
                            'quantity_shipped': quantity,
                        })
                        .then((data) => {
                            this.somethingHasBeenPackedDuringThisSession = true;
                            this.notifySuccess(data.data.data.quantity_shipped + ' x ' + '' + ' shipped');
                        })
                        .catch((error) => {
                            this.apiActivitiesPost({
                                'subject_type': 'order',
                                'subject_id': this.order_id,
                                'description': 'Error occurred when shipping products, try again'
                            });
                            this.displayApiCallError(error);
                        })
                        .finally(() => {
                            this.reloadData();
                        });
                },

                shipAll(orderProduct) {
                    this.shipOrderProduct(orderProduct, orderProduct['quantity_to_ship']);
                    this.setFocusElementById('barcode-input');
                },

                findEntry: function (barcode) {
                    if(barcode === '') {
                        return null;
                    }

                    for (let element of this.packlist) {

                        if(element.sku_ordered.toUpperCase() === barcode.toUpperCase()) {
                            return element;
                        }

                        if(typeof element.product === 'undefined' ){
                            continue;
                        }

                        if(element.product === null ){
                            continue;
                        }

                        if(element.product.sku.toUpperCase() === barcode.toUpperCase()) {
                            return element;
                        }

                        if(typeof element.product.aliases === 'undefined') {
                            continue;
                        }

                        for(let alias of element.product.aliases) {
                            if(alias.alias.toUpperCase() === barcode.toUpperCase()){
                                return element;
                            }
                        }
                    }

                    return null;
                },

                packBarcode: function (barcode) {
                    let pickItem = this.findEntry(barcode);

                    if(!pickItem) {
                        this.notifyError(`"${barcode}" not found on packlist!`);
                        return;
                    }

                    if (pickItem['quantity_to_ship'] === 0) {
                        this.notifyError(`SKU already shipped`);
                        return;
                    }

                    this.shipOrderProduct(pickItem, 1);
                    this.setFocusElementById('barcode-input');
                },

                getAddressLabelTemplateName: function () {
                    if (this.getUrlParameter('address_label_template')) {
                        return this.getUrlParameter('address_label_template');
                    }

                    if (this.order.label_template) {
                        return this.order.label_template;
                    }

                    if (Vue.prototype.$currentUser.address_label_template) {
                        return Vue.prototype.$currentUser.address_label_template;
                    }

                    return '';
                },

                autoPrintLabelIfNeeded: async function() {
                    if (this.autoLabelAlreadyPrinted) {
                        return;
                    }

                    this.autoLabelAlreadyPrinted = true;

                    let template = this.getAddressLabelTemplateName();

                    if (template) {
                        return await this.printShippingLabel(template);
                    }
                },

                printExtraLabelClick: function () {
                    this.$refs.filtersModal.hide();
                    this.setFocusElementById('barcode-input');

                    this.printShippingLabel();
                },

                printShippingLabel: async function(shipping_service_code = null) {
                    if (shipping_service_code === null) {
                        shipping_service_code = this.getAddressLabelTemplateName();
                    }

                    let params = {
                        'shipping_service_code': shipping_service_code,
                        'order_id': this.order_id
                    };

                    return this.apiPostShippingLabel(params)
                        .then((data) => {
                            this.notifySuccess('Label generated', false,{
                                closeOnClick: true,
                                timeout: 1,
                                buttons: []
                            });
                        })
                        .catch((error) => {
                            this.canClose = false;
                            let errorMsg = 'Error ' + error.response.status + ': ' + error.response.data.message;

                            this.notifyError(errorMsg, {
                                closeOnClick: true,
                                timeout: 0,
                                buttons: [
                                    {text: 'OK', action: null},
                                ]
                            });

                            this.apiActivitiesPost({
                                'subject_type': 'order',
                                'subject_id': this.order.id,
                                'description': 'Error when posting shipping label request'
                            });
                        })
                        .finally(() => {
                            this.reloadData();
                        });
                },

                openPreviousOrder: function (){
                    this.$refs.filtersModal.hide();
                    this.setFocusElementById('barcode-input');

                    if (! this.previous_order_id) {
                        this.notifyError('Not Available');
                        return;
                    }

                    this.loadOrder(this.previous_order_id);
                },

                updateLabelTemplate: function () {
                    this.$refs.filtersModal.hide();

                    this.apiUpdateOrder(this.order_id, {
                            'label_template': this.order.label_template
                        })
                        .then(() => {
                            this.reloadData();
                        })
                        .catch((error) => {
                            this.displayApiCallError(error);
                        });
                }
            },

            computed: {
                showMultipackerWarning()  {
                    if (this.order === null) {
                        return false;
                    }

                    return (this.order['packer_user_id']  && this.order['packer_user_id'] !== Vue.prototype.$currentUser['id']);
                },

                commentsToShow() {
                    return this.order.order_comments.length
                        ? (this.manuallyExpandComments ? this.order.order_comments : [this.order.order_comments[0]])
                        : [];
                }
            },
    }
    </script>


<style lang="scss">

</style>
