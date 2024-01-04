<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" id="show-inventory-movements" size="xl" scrollable no-fade>
        <template #modal-header>
            <span>Inventory Movements</span>
            <a :href="productItemMovementLink" class="fa-pull-right">See all</a>
        </template>

        <template v-for="record in records">
            <inventory-movement-card :record="record" />
        </template>

        <div class="d-flex align-items-center justify-content-center" style="height:100px" v-if="!isLoading && !records.length">
            No records found
        </div>

        <div class="row" v-if="isLoading">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 100px"></div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a v-if="hasNextPage" :href="productItemMovementLink" target="_blank">See more</a>
        </div>

        <template #modal-footer>
            <b-button
                v-show="!isLoading"
                variant="outline-secondary"
                class="float-right"
                @click="$bvModal.hide('show-inventory-movements')"
            >
                Close
            </b-button>
        </template>
    </b-modal>
</template>

<script>

import api from "../../mixins/api";
import loadingOverlay from '../../mixins/loading-overlay';
import InventoryMovementCard from './InventoryMovementCard';

export default {
    components: { InventoryMovementCard },

    mixins: [api, loadingOverlay],

    props: {
        product_sku: {
            default: null,
            type: String
        },
        warehouse_code: {
            default: null,
            type: String
        }
    },

    watch: {
        product_sku() {
            this.loadRecords()
        }
    },

    data: function() {
        return {
            records: [],
            hasNextPage: false
        };
    },

    computed: {
        productItemMovementLink() {
            return '/reports/inventory-movements?hide_nav_bar=true&filter[search]=' + this.product_sku + '&filter[warehouse_code]=' + this.warehouse_code;
        },
    },

    mounted: function () {
        if (this.currentUser()['warehouse']) {
            this.warehouse_code = this.currentUser()['warehouse']['code'];
        }
    },

    methods: {
        loadRecords: function(page = 1) {
            this.showLoading();
            this.records = []

            let params = {
                filter: null,
                include: 'product,inventory,user',
                sort: '-sequence_number,-occurred_at',
                page,
                per_page: 50
            };

            params["filter[warehouse_code]"] = this.warehouse_code;
            params["filter[search]"] = this.product_sku

            this.apiGetInventoryMovements(params)
                .then(({data}) => {
                    this.records = this.records.concat(data.data);
                    this.hasNextPage = data.links.next != null
                })
                .finally(() => {
                    this.hideLoading();
                });
        },
    }
}
</script>

<style>

</style>
