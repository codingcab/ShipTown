<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        RMS API Connections
                    </span>
                    <a tabindex="-1" class="action-link" v-b-modal.connection-modal>
                        Create New Connection
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table v-if="configurations.length > 0" class="table table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(configuration, i) in configurations" :key="i">
                            <td>
                                <div v-if="configuration.status === 200" class="badge badge-success text-uppercase"> Status OK </div>
                                <div v-if="configuration.status !== 200" class="badge badge-danger text-uppercase"> Status {{configuration.status}} </div>
                            </td>
                            <td>
                                {{ configuration.url }}<br>
                                <div class="text-small"><span class="font-weight-bold">username: </span> {{ configuration.username }}</div>
                                <div class="text-small"><span class="font-weight-bold">warehouse: </span> {{ configuration.location_id }}</div>
                                <div class="text-small"><span class="font-weight-bold">Password Reset Page: </span> <a :href="configuration.url + '/password/reset'" target="_blank" class="action-link text-primary">link</a></div>
                                <div class="text-small"><span class="font-weight-bold">Status Page: </span> <a :href="configuration.url + '/status'" target="_blank" class="action-link text-primary">link</a></div>
                                <div class="text-small"><span class="font-weight-bold">Webhooks: </span> {{ configuration.url + '/api/products-management/webhooks' }}</div>
                            </td>
                            <td>
                                <a @click="confirmDelete(configuration.id, i)" class="action-link text-danger">DELETE</a><br>
                                <a @click="changePassword(configuration.id, i)" class="action-link text-danger">UPDATE API PASSWORD</a><br>
                                <br>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    You have not created any RMS API connections.
                </p>
            </div>
        </div>

        <!-- The modal -->
        <b-modal ref="formModal" id="connection-modal" title="Configure Connection" @ok="handleModalOk">
            <rms-api-configuration-form ref="form" @saved="handleSaved" />
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
    import { BModal, VBModal, BButton } from 'bootstrap-vue';

    import RMSApiConfigurationForm from './RmsapiConfigurationForm';
    import api from "../../mixins/api";

    export default {
        mixins: [api],

        components: {
            'b-modal': BModal,
            'b-button': BButton,
            'rms-api-configuration-form': RMSApiConfigurationForm,
        },

        directives: {
            'b-modal': VBModal
        },

        data: () => ({
            configurations: []
        }),

        mounted() {
            this.prepareComponent();
        },

        methods: {
            prepareComponent() {
                this.getConfiguration();
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getConfiguration() {
                this.apiGetRmsapiConnections({})
                    .then(({ data }) => {
                        data.data.forEach((item, index) => {
                            item['status'] = 0;
                            this.configurations.push(item);

                        })
                        this.configurations.forEach((item, index) => {
                            this.apiGet(item['url'] + '/status')
                                .then((response) => {
                                    this.configurations[index]['status'] = response.status;
                                })
                        })
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

            changePassword(id, index){
                this.$snotify.prompt('Enter new password', 'Change Password', {
                    buttons: [
                        {text: 'Cancel', action: (toast) => { this.$snotify.remove(toast.id) }},
                        {text: 'Ok', action: (toast) => {
                                this.apiPostRmsapiConnections({id: id, password: toast.value})
                                    .then(({ data }) => {
                                        this.$snotify.success('Password changed successfully');
                                        this.$snotify.remove(toast.id);
                                    })
                                    .catch(({ response }) => {
                                        this.$snotify.error(response.data.message);
                                        this.$snotify.remove(toast.id);
                                    })
                                    .finally(() => {
                                        this.$snotify.remove(toast.id);
                                    });
                            }
                        }
                    ]
                });
                this.apiPostRmsapiConnections({
                    id: id,
                    password: 'password'
                })
                    .then(({ data }) => {
                        this.configurations[index]['password'] = 'password';
                    });
            },

            confirmDelete(id, index){
                this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                    position: 'centerCenter',
                    buttons: [
                        {
                            text: 'Yes',
                            action: (toast) => {
                                this.handleDelete(id, index)
                                this.$snotify.remove(toast.id);
                            }
                        },
                        {text: 'Cancel'},
                    ]
                });
            },

            handleDelete(id, index) {
                this.apiDeleteRmsapiConnection(id)
                    .then(() => {
                        Vue.delete(this.configurations, index);
                    });
            }
        },
    }

</script>

<style lang="scss" scoped>
    .action-link {
        cursor: pointer;
    }
</style>
