<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        API2Cart Connections
                    </span>
                    <a tabindex="-1" class="action-link" v-b-modal.api2cart-connection-modal>
                        Create New Connection
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table v-if="configurations.length > 0" class="table table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
                            <th>Location ID</th>
                            <th>URL</th>
                            <th>Store Type</th>
                            <th>Magento Store ID</th>
                            <th>magento_warehouse_id</th>
                            <th>pricing_location_id</th>
                            <th></th><!--Delete-->
                            <th></th><!--SKU Lookup-->
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(configuration, i) in configurations" :key="i">
                            <td>{{ configuration.location_id }}</td>
                            <td>{{ configuration.url }}</td>
                            <td>{{ configuration.type | capitalize }}</td>
                            <td>{{ configuration.magento_store_id }}</td>
                            <td>{{ configuration.magento_warehouse_id }}</td>
                            <td>{{ configuration.pricing_location_id }}</td>
                            <td>
                                <a @click="confirmDelete(configuration.id, i)" class="action-link text-danger">Delete</a>
                            </td>
                            <td>
                                <a @click="showSkuDetails('01')" class="action-link">SKU Lookup</a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    You have not created any API2Cart connections.
                </p>
            </div>
        </div>

        <!-- The modal -->
        <b-modal ref="formModal" id="api2cart-connection-modal" title="Configure Connection" @ok="handleModalOk">
            <api-configuration-form ref="form" @saved="handleSaved" />
            <template #modal-footer="{ ok, cancel }">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button variant="primary" @click="ok()">
                    Save
                </b-button>
            </template>
        </b-modal>

        <!-- The modal -->
        <b-modal ref="formModal" id="sku_details_modal" title="SKU Lookup">
            {{ sku_details }}
            <template #modal-footer="{ ok, cancel }">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button variant="primary" @click="ok()">
                    Save
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import { BModal, VBModal, BButton} from 'bootstrap-vue';

    import Api2CartConfigurationForm from '../SharedComponents/Api2CartConfigurationForm';
    import api from "../../mixins/api";

    export default {
        components: {
            'b-modal': BModal,
            'b-button': BButton,
            'api-configuration-form': Api2CartConfigurationForm,
        },

        mixins: [api],

        directives: {
            'b-modal': VBModal
        },

        data: () => ({
            configurations: [],
            sku_details: '',
        }),

        mounted() {
            this.prepareComponent();
        },

        methods: {

            prepareComponent() {
                this.getConfiguration();
            },

            getConfiguration() {
                this.apiGetApi2cartConnections({})
                    .then(({ data }) => {
                        this.configurations = data.data;
                    });
            },

            handleModalOk(bvModalEvt) {
                bvModalEvt.preventDefault();
                this.$refs.form.submit();
            },

            handleSaved(config) {
                this.configurations.push(config);

                this.$nextTick(() => {
                    this.$refs.formModal.hide();
                });
            },

            showSkuDetails(sku) {
                this.$snotify.prompt('Partial shipment', {
                    placeholder: 'Enter SKU:',
                    position: 'centerCenter',
                    icon: false,
                    buttons: [
                        {
                            text: 'Show In Web Console',
                            action: (toast) => {
                                this.apiModuleEcommerceProductInfo({'sku': toast.value})
                                    .then(({ data }) => {
                                        console.log(data);
                                    });
                                this.$snotify.remove(toast.id);
                            }
                        },
                        {
                            text: 'Cancel',
                            action: (toast) => {
                                this.$snotify.remove(toast.id);
                            }
                        },
                    ],
                });

            },

            confirmDelete(connection_id, index){
                this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                    position: 'centerCenter',
                    buttons: [
                        {
                            text: 'Yes',
                            action: (toast) => {
                                this.handleDelete(connection_id, index)
                                this.$snotify.remove(toast.id);
                            }
                        },
                        {text: 'Cancel'},
                    ]
                });
            },

            handleDelete(connection_id, index) {
                this.apiDeleteApi2cartConnection(connection_id)
                    .then(() => {
                        Vue.delete(this.configurations, index);
                    });
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }

</script>

<style lang="scss" scoped>
    .action-link {
        cursor: pointer;
    }
</style>
