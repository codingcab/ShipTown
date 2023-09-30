<template>
    <b-modal id="modal-create-connection" ref="modal-create-connection" no-fade
        title="Add Connection"
        ok-title="Save"
             @shown="setFocusElementById(100, 'create-base_url');"
        @ok="submit"
    >
        <ValidationObserver ref="form">
            <form class="form" @submit.prevent="submit" ref="loadingContainer">

                <div class="form-group">
                    <label class="form-label" for="create-base_url">URL</label>
                    <ValidationProvider vid="base_url" name="base_url" v-slot="{ errors }">
                    <input v-model="config.base_url" :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }" id="create-base_url" type="url" required>
                    <div class="invalid-feedback">
                        {{ errors[0] }}
                    </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="api_access_token">Access Token</label>
                    <ValidationProvider vid="api_access_token" name="api_access_token" v-slot="{ errors }">
                        <input type="password" v-model="config.api_access_token" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="api_access_token" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </form>
        </ValidationObserver>

        <template #modal-footer>
            <b-button
                variant="secondary"
                class="float-right"
                @click="closeModal"
            >
                Cancel
            </b-button>
            <b-button @click="submit" variant="primary" class="float-right">Save</b-button>
        </template>
    </b-modal>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../../mixins/loading-overlay";
import api from "../../../mixins/api";

export default {
    name: "CreateModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    mounted() {
        this.fetchWarehouses();
    },

    data() {
        return {
            config: {
                base_url: ''
            },
            warehouses: []
        }
    },

    methods: {
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
            this.apiPostMagentoApiConnection({...this.config})
                .then(({ data }) => {
                    this.$snotify.success('Connection created.');
                    this.resetForm()
                    this.closeModal()
                    this.$emit('onCreated');
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(error.response.data.errors);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        resetForm(){
            this.config = {}
        },

        closeModal() {
            this.$bvModal.hide('modal-create-connection')
        }
    },
}
</script>
