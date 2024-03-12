<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Inventory Reservations
                    </span>
                </div>
            </div>
            <div class="card-body">
                <p>Reserves stock for open orders.</p>
                    <form class="form" ref="loadingContainer">
                        <div class="row">
                            <label for="update-warehouse_id" class="col-sm-3 col-form-label">Warehouse</label>
                            <div class="col-sm-9">
                                <select v-model="configuration.warehouse_id" @change="saveChanged" class="form-control"
                                    id="update-warehouse_id" required>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                        {{ warehouse.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../../mixins/api.vue";
import helpers from "../../../mixins/helpers";
import { ValidationObserver, ValidationProvider } from 'vee-validate';
import Loading from '../../../mixins/loading-overlay';

export default {
    components: {
        ValidationObserver, ValidationProvider
    },

    mixins: [api, helpers, Loading],

    mounted() {
        this.loadWarehouses();
        this.loadConfig();
    },

    data: () => ({
        warehouses: [],
        configuration: {}
    }),

    methods: {
        loadWarehouses() {
            this.apiGetWarehouses({
                    'per_page': 999,
                    'sort': 'name',
                })
                .then(({ data }) => {
                    this.warehouses = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        loadConfig() {
            this.apiGetActiveOrdersInventoryReservationsConfig()
                .then(({ data }) => {
                    this.configuration = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        saveChanged() {
            this.showLoading();
            let data = {
                warehouse_id: this.configuration.warehouse_id
            };
            this.apiPostActiveOrdersInventoryReservationsConfig(this.configuration.id, data)
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        }
    },
}
</script>
