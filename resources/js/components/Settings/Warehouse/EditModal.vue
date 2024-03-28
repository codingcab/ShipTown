<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Warehouse</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label" for="code">Code</label>
                              <div class="col-sm-9">
                                <ValidationProvider vid="code" name="code" v-slot="{ errors }">
                                  <input v-model="code" :class="{
                                              'form-control': true,
                                              'is-invalid': errors.length > 0,
                                          }" id="edit-code" required>
                                  <div class="invalid-feedback">{{ errors[0] }}</div>
                                </ValidationProvider>
                              </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="name">Name</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                                        <input v-model="name" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-name" required>
                                        <div class="invalid-feedback">{{ errors[0] }}</div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="tagsString">Tags</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="tagsString" name="tagsString" v-slot="{ errors }">
                                        <input v-model="tagsString" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-tags" required>
                                        <div class="invalid-feedback">{{ errors[0] }}</div>
                                    </ValidationProvider>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click.prevent="confirmDelete(warehouse)" class="btn btn-outline-danger float-left">Delete</button>
                    <div>
                        <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
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
import {split} from "lodash/string";

export default {
    name: "EditModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    data() {
        return {
            name: '',
            code: '',
            tagsString: '',
            tags: [],
        }
    },

    props: {
        warehouse: Object,
    },

    watch: {
        warehouse: function(newVal) {
            this.name = newVal.name;
            this.code = newVal.code;
            this.tags = newVal.tags;
            this.tagsString = newVal.tags
                .map(function ($tag) {
                    return $tag['name'];
                })
                .join(',');
        }
    },

    methods: {

        submit() {
            this.showLoading();
            this.apiPutWarehouses(this.warehouse.id, {
                    name: this.name,
                    code: this.code,
                    tags: this.tagsString.split(','),
                })
                .then(({ data }) => {
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
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

        closeModal() {
            $(this.$el).modal('hide');
        },

        confirmDelete(selectedWarehouse) {
            this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.apiDeleteWarehouses(selectedWarehouse.id)
                                .catch(() => {
                                    this.$snotify.error('Error occurred while deleting.');
                                });
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
