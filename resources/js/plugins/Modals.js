const Modals = {
    install(Vue, options) {
        this.EventBus = new Vue()

        Vue.prototype.$modal = {
            show(modal, data) {
                Modals.EventBus.$emit('show::modal::' + modal, data);
            },

            showRecentInventoryMovementsModal(inventory_id) {
                this.show('recent-inventory-movements-modal', {
                    'inventory_id': inventory_id
                });
            },

            showDataCollectorQuantityRequestModal(data_collection_id, sku_or_alias) {
                this.show('data-collector-quantity-request-modal', {
                    'data_collection_id': data_collection_id,
                    'sku_or_alias': sku_or_alias,
                });
            },
        }
    }
}

export default Modals
