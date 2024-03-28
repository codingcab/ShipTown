<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" id="product-info-modal" size="xl" scrollable no-fade>
        <template #modal-header>
            <span>Inventory Movements</span>
            <a :href="productItemMovementLink" class="fa-pull-right">See all</a>
        </template>

<!--        <product-card :product="product" :expanded="true"/>-->

        <div class="d-flex align-items-center justify-content-center" style="height:100px" v-if="!isLoading && !records.length">
            No records found
        </div>

        <div class="row" v-if="isLoading">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 100px"></div>
            </div>
        </div>

<!--        <div class="d-flex justify-content-end">-->
<!--            <a v-if="hasNextPage" :href="productItemMovementLink" target="_blank">See more</a>-->
<!--        </div>-->

        <template #modal-footer>
            <b-button v-show="!isLoading" variant="secondary" class="float-right" @click="$bvModal.hide('product-info-modal')">
                Close
            </b-button>
        </template>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import loadingOverlay from '../mixins/loading-overlay';
import InventoryMovementCard from '../components/SharedComponents/InventoryMovementCard.vue';
import ProductCard from "../components/Products/ProductCard.vue";

export default {
    components: {ProductCard, InventoryMovementCard },

    mixins: [api, loadingOverlay],

    props: {
        product: {
            default: null,
            type: Object
        },
    },

    watch: {
        product() {
            console.log(this.product);
            // this.loadRecords()
        },
    },

    computed: {
        productItemMovementLink() {
            return '/reports/inventory-movements?filter[inventory_id]=' + this.inventory_id;
        },
    },

    methods: {
        loadRecords: function(page = 1) {
            if (this.inventory_id == null) {
                this.notifyError('Inventory ID is required')
                return;
            }

            this.showLoading();
            this.records = []

            let params = {
                "filter[inventory_id]": this.inventory_id,
                include: 'product,inventory,user',
                sort: '-occurred_at,-sequence_number',
                page: page,
                per_page: 50
            };

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
