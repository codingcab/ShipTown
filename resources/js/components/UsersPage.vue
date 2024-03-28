<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Users
                    </span>
                    <a tabindex="-1" class="action-link" v-b-modal.create-modal>
                        Add User
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table v-if="users.length > 0" class="table table-borderless table-responsive mb-0">
                    <tbody>
                        <tr v-for="(user, i) in users" :key="'user-' + i" @click="onEditClick(user.id)">
                            <td>
                                <strong>{{ user.name }}</strong>
                                <br>
                                {{ user.email }}
                            </td>
                            <td style="vertical-align:middle">
                                <div v-for="session in user['sessions']">
                                    <div :title="session['user_agent']" class="badge border-0" :class="isSessionActive(session['last_activity'])">
                                        {{ formatDateTime(session['last_activity']) }}
                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align:middle">{{ user.roles[0] ? user.roles[0]['name'] : '' }}</td>
                            <td style="vertical-align:middle">
                                <a v-if="isNotSelf(user.id)" @click.prevent="onDeleteClick(user.id)">
                                    <font-awesome-icon icon="user-minus"></font-awesome-icon>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    No user found.
                </p>
            </div>
        </div>
        <!-- The modals -->
        <b-modal ref="createModal" id="create-modal" title="Add User" @ok="handleAddOk" no-fade>
            <create-modal
                ref="createForm"
                :roles="roles"
                :warehouses="warehouses"
                @onCreated=addedUser
            >
            </create-modal>
            <template #modal-footer="{ ok, cancel }">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button variant="primary" @click="ok()">
                    Save
                </b-button>
            </template>
        </b-modal>
        <b-modal ref="editModal" id="edit-modal" title="Edit User" @ok="handleEditOk" no-fade>
            <edit-modal
                ref="editForm"
                v-if="selectedId"
                :id="selectedId"
                :roles="roles"
                :warehouses="warehouses"
                @onUpdated=updatedUser
            >
            </edit-modal>
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
import Create from './Users/Create';
import Edit from './Users/Edit';
import api from "../mixins/api";
import Vue from "vue";
import moment from "moment";

export default {
    mixins: [api],
    components: {
        'create-modal': Create,
        'edit-modal': Edit,
    },

    mounted() {
        this.loadUsers();
        this.loadRoles();
        this.loadWarehouses();
    },

    data: () => ({
        users: [],
        roles: [],
        warehouses: [],
        selectedId: null,
    }),

    methods: {
        isSessionActive(lastActivity) {
            const now = moment();
            const last = moment(lastActivity);
            const diff = now.diff(last, 'minutes');
            return diff < 10 ? 'badge-success' : '';
        },

        loadUsers() {
            this.apiGetUsers({
                    'include': 'roles,warehouse,sessions',
                    'per_page': 999
                })
                .then(({ data }) => {
                    this.users = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        loadRoles() {
            this.apiGetUserRoles()
                .then(({ data }) => {
                    this.roles = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

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

        hideModal(ref) {
            setTimeout(() => {
                this.$refs[ref].hide();
            }, 500);
        },

        addedUser(){
            this.hideModal('createModal');
            this.loadUsers();
        },

        updatedUser(){
            this.hideModal('editModal');
            this.loadUsers();
        },

        handleAddOk(bvModalEvt) {
            bvModalEvt.preventDefault();
            this.$refs.createForm.submit();
        },

        handleEditOk(bvModalEvt) {
            bvModalEvt.preventDefault();
            this.$refs.editForm.submit();
        },

        onEditClick(id) {
            this.selectedId = id;
            this.$refs.editModal.show();
        },

        onDeleteClick(id) {
            this.confirmDelete(id)
        },

        confirmDelete(id) {
            this.$snotify.confirm('Deactivate this user', 'Are you sure?', {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.delete(id)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: 'Cancel'},
                ]
            });
        },

        delete(id) {
            this.apiDeleteUser(id)
                .then(() => {
                    this.$snotify.success('User deactivated.');
                    this.loadUsers()
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },
    },

    computed: {
        isNotSelf() {
            return (id) => Vue.prototype.$currentUser.id !== id;
        }
    }
}
</script>
