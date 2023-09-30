<template>
    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Magento Api Connection</h5>
                </div>
                <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" for="base_url">Base URL</label>
                                <input v-model="config.base_url" :class="{'form-control': true}" id="base_url" type="url" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="magento_store_id">Magento Store ID</label>
                                <input v-model="config.magento_store_id" :class="{'form-control': true}" id="magento_store_id" type="text" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="magento_store_code">Magento Store Code</label>
                                <input v-model="config.magento_store_code" :class="{'form-control': true}" id="magento_store_code" type="text" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="magento_inventory_source_code">Magento Inventory Source Code</label>
                                <input v-model="config.magento_inventory_source_code" :class="{'form-control': true}" id="magento_inventory_source_code" type="text" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Inventory source warehouse tag</label>
                                <select v-model="config.inventory_totals_tag_id" :class="{'form-control': true}" id="inventory_source_tag_id">
                                    <option value="">Do not sync inventory</option>
                                    <option v-for="tag in tags"  :value="tag.id" :key="tag.id">
                                       {{ tag.name.en }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Pricing source warehouse</label>
                                <select v-model="config.pricing_source_warehouse_id" :class="{'form-control': true}" id="pricing_source_warehouse_id">
                                    <option value="">Do not sync prices</option>
                                    <option v-for="warehouse in warehouses"  :value="warehouse.id" :key="warehouse.id">
                                        {{ warehouse.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="api_access_token">Access Token</label>
                                <input v-model="config.api_access_token" type="password" :class="{'form-control': true}" id="api_access_token" required>
                            </div>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click.prevent="confirmDelete" class="btn btn-outline-danger float-left">Delete</button>
                    <div>
                        <button type="button" @click="closeModal" class="btn btn-outline-primary">Cancel</button>
                        <button type="button" @click="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../../mixins/loading-overlay";
import api from "../../../mixins/api";

export default {
    name: "EditModal",

    mixins: [api, Loading],

    mounted() {
        this.fetchWarehouses();
        this.fetchTags();
    },

    data() {
        return {
            config: {
                base_url: location.protocol + '//' + location.host
            },
            warehouses: [],
            tags: [],
        }
    },

    props: {
        connection: Object,
    },

    watch: {
        connection: function(newVal) {
            this.config = {
                base_url: newVal.base_url,
                magento_inventory_source_code: newVal.magento_inventory_source_code ?? '',
                magento_store_id: newVal.magento_store_id ?? 0,
                magento_store_code: newVal.magento_store_code ?? 'default',
                inventory_totals_tag_id: newVal.inventory_totals_tag_id ?? '',
                pricing_source_warehouse_id: newVal.pricing_source_warehouse_id ?? '',
                api_access_token: newVal.api_access_token
            };
        }
    },

    methods: {
        fetchTags: function () {
            this.apiGetTags({
                'filter[model]': 'App\\Models\\Warehouse',
                'per_page': 100,
                'sort': 'name'
            })
            .then(({data}) => {
                this.tags = data.data;
            })
        },

        fetchWarehouses: function () {
            this.apiGetWarehouses({
                'per_page': 100,
                'sort': 'code',
                'include': 'tags'
            })
                .then(({data}) => {
                    this.warehouses = data.data;
                })
        },

        submit() {
            this.showLoading();
            this.apiPutMagentoApiConnection(this.connection.id, this.config)
                .then(({ data }) => {
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        },

        confirmDelete() {
            this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.apiDeleteMagentoApiConnection(this.connection.id)
                                .then(() => {
                                    this.closeModal();
                                    this.$emit('onUpdated');
                                })
                                .catch((error) => {
                                    this.displayApiCallError(error);
                                })
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: 'Cancel'},
                ]
            });
        },
    },
}
</script>
