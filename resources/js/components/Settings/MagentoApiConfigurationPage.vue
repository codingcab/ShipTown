<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Magento 2 API
                    </span>
                    <b-btn variant="primary" class="btn-sm bv-no-focus-ring" @click="showCreateForm" >
                        New Connection
                    </b-btn>
                </div>
            </div>

            <div class="card-body">
                <table v-if="connections.length > 0" class="table table-hover table-borderless table-responsive mb-0">
                    <thead>
                    <tr>
                        <th>URL</th>
                        <th>Inventory</th>
                        <th>Pricing</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="connection in connections" :key="connection.id" @click.prevent="showEditForm(connection)">
                        <td class="w-100">{{ connection.base_url }}</td>
                        <td>

                            <a class="badge text-uppercase" :key="connection.inventory_totals_tag_id"> {{ connection.inventory_totals_tag.name }} </a>
<!--                            <template v-for="tag in connection.tags">-->
<!--                                a-->
<!--                                <a class="badge text-uppercase" :key="tag.id"> {{ tag.name }} </a>-->
<!--                            </template>-->
                        </td>
                        <td>{{ connection.warehouse?.name }}</td>
                    </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    You have not created any Magento Api connections.
                </p>
            </div>
        </div>

        <create-modal @onCreated="getConnections"></create-modal>
        <edit-modal :connection="selectedConnection" id="editForm" @onUpdated="conncetionUpdatedEvent"></edit-modal>
    </div>
</template>

<script>
import api from "../../mixins/api";
import CreateModal from './MagentoApi/CreateModal.vue';
import EditModal from './MagentoApi/EditModal.vue';

export default {
    components: {
        CreateModal, EditModal
    },

    mixins: [api],

    data: () => ({
        selectedConnection: null,
        connections: [],
    }),

    mounted() {
        this.getConnections();
    },

    methods: {

        getConnections() {
            this.apiGetMagentoApiConnections({
                'per_page': 100,
                'include': 'tags,warehouse,inventoryTotalsTag'
            })
                .then(({ data }) => {
                    this.connections = data.data;
                });
        },

        showCreateForm() {
            this.$bvModal.show('modal-create-connection');
        },

        confirmDelete(connection_id){
            this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.handleDelete(connection_id)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: 'Cancel'},
                ]
            });
        },

        showEditForm(connection) {
            this.selectedConnection = connection;
            $('#editForm').modal('show');
        },

        conncetionUpdatedEvent() {
            this.getConnections();
        },
    },
}

</script>

<style lang="scss" scoped>
.action-link {
    cursor: pointer;
}
</style>
