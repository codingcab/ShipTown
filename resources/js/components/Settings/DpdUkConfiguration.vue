<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        DPD UK
                    </span>
                </div>
            </div>

            <div class="card-body">

                <p v-if="connections.length === 0" class="flex-fill text-center">
                    <button class="btn btn-primary mt-3" v-b-modal.dpd-create-form-modal>Connect</button>
                </p>

                <table v-if="connections.length > 0" class="table table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Username</th>
                            <th>Collection Address</th>
<!--                            <th></th>&lt;!&ndash;Delete&ndash;&gt;-->
                        </tr>
                    </thead>
                    <tbody>
                    <template v-for="connection in connections">
                        <tr key="{{ connection['id'] }}">
                            <td>{{ connection['account_number'] }}</td>
                            <td>
                                <div>{{ connection['collection_address']['first_name'] }} {{ connection['collection_address']['last_name'] }}</div>
                                <div>{{ connection['collection_address']['compnay'] }}</div>
                                <div>{{ connection['collection_address']['address1'] }}</div>
                                <div>{{ connection['collection_address']['address2'] }}</div>
                                <div>{{ connection['collection_address']['city'] }}</div>
                                <div>{{ connection['collection_address']['country_code'] }}</div>
                                <div>&nbsp;</div>
                                <div>{{ connection['collection_address']['phone'] }}</div>
                                <div>{{ connection['collection_address']['email'] }}</div>
                            </td>
                            <td>
                                <a @click="handleDelete(connection.id)" class="action-link text-danger">Delete</a>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- The modal -->
        <b-modal ref="createFormModal" id="dpd-create-form-modal" title="New DPD UK connection" @ok="handleModalOk">
            <dpd-configuration-form ref="createForm" @saved="handleSaved" />
            <template #modal-footer="{ ok, cancel }">
                <b-button varaint="secondary" @click="cancel()">
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

    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";
    import DpdUkNewConnectionForm from "../SharedComponents/DpdUkNewConnectionForm";

    export default {
        components: {
            'b-modal': BModal,
            'b-button': BButton,
            'dpd-configuration-form': DpdUkNewConnectionForm,
        },

        mixins: [api, helpers],

        directives: {
            'b-modal': VBModal
        },

        data: () => ({
            connections: [],
        }),

        mounted() {
            this.reloadConnections();
        },

        methods: {
            reloadConnections() {
                this.apiGetDpdUkConnections()
                    .then(({ data }) => {
                        this.connections = data.data;
                    })
                    .catch((error) => {
                        this.notifyError('Api call failed - Error ' + error.response.status +': '+ error.response.statusText);
                    });
            },

            handleModalOk(bvModalEvt) {
                bvModalEvt.preventDefault();

                if(this.configuration) {
                    this.$refs.updateForm.submit();
                } else {
                    this.$refs.createForm.submit();
                }

                this.reloadConnections();
            },

            handleSaved(data) {
                this.configuration = data;
                this.$nextTick(() => {
                    this.$refs.createFormModal.hide();
                    this.reloadConnections();
                });
            },

            handleDelete(id) {
                this.apiDeleteDpdUkConnection(id)
                    .then(() => {
                        this.connections = [];
                    });
            },
        }
    }

</script>

<style lang="scss" scoped>
    .action-link {
        cursor: pointer;
    }
</style>
