<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order Status</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="order_active">Order Active</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="order_active" name="order_active" v-slot="{ errors }">
                                        <div class="custom-control custom-switch mt-2" :class="{'is-invalid' : errors.length}">
                                            <input type="checkbox"
                                                id="edit-order_active"
                                                class="custom-control-input"
                                                v-model="orderActive"
                                                required>
                                            <label class="custom-control-label" for="edit-order_active"></label>
                                        </div>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="order_on_hold">Order On Hold</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="order_on_hold" name="order_on_hold" v-slot="{ errors }">
                                        <div class="custom-control custom-switch mt-2" :class="{'is-invalid' : errors.length}">
                                            <input type="checkbox"
                                                id="edit-order_on_hold"
                                                class="custom-control-input"
                                                v-model="orderOnHold"
                                                required>
                                            <label class="custom-control-label" for="edit-order_on_hold"></label>
                                        </div>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="sync_ecommerce">Sync Ecommerce</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="sync_ecommerce" name="sync_ecommerce" v-slot="{ errors }">
                                        <div class="custom-control custom-switch mt-2" :class="{'is-invalid' : errors.length}">
                                            <input type="checkbox"
                                                id="edit-sync_ecommerce"
                                                class="custom-control-input"
                                                v-model="syncEcommerce"
                                                required>
                                            <label class="custom-control-label" for="edit-sync_ecommerce"></label>
                                        </div>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="hidden">Hidden</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="hidden" name="hidden" v-slot="{ errors }">
                                        <div class="custom-control custom-switch mt-2" :class="{'is-invalid' : errors.length}">
                                            <input type="checkbox"
                                                id="edit-hidden"
                                                class="custom-control-input"
                                                v-model="hidden"
                                                required>
                                            <label class="custom-control-label" for="edit-hidden"></label>
                                        </div>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click="confirmDelete" class="btn btn-outline-danger float-left">Archive</button>
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
import helpers from "../../../mixins/helpers";

export default {
    name: "EditModal",

    mixins: [api, Loading, helpers],

    components: {
        ValidationObserver, ValidationProvider
    },

    data() {
        return {
            reservesStock: false,
            orderActive: false,
            orderOnHold: false,
            syncEcommerce: false,
            hidden: false,
        }
    },

    props: {
        orderStatus: Object,
    },

    watch: {
        orderStatus: function(newVal) {
            this.orderActive = newVal.order_active
            this.orderOnHold = newVal.order_on_hold
            this.syncEcommerce = newVal.sync_ecommerce
            this.hidden = newVal.hidden
        }
    },

    methods: {
        submit() {
            this.showLoading();
            this.apiPutOrderStatus(this.orderStatus.id, {
                    order_active: this.orderActive,
                    order_on_hold: this.orderOnHold,
                    sync_ecommerce: this.syncEcommerce,
                    hidden: this.hidden,
                })
                .then(({ data }) => {
                    this.$snotify.success('Order status updated.');
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

            this.closeModal();
        },

        confirmDelete() {
            if(this.reservesStock || this.orderActive || this.syncEcommerce || this.syncEcommerce) {
              this.showError('Please disable all options before archiving');
              return;
            }

            if(this.isModified) {
              this.showError('Please save changes before archiving');
              return;
            }

            this.$snotify.confirm(`Do you want to archive this status?`,  {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.delete(this.orderStatus.id)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: 'Cancel'},
                ]
            });
        },

        delete(id, index) {
            this.submit();

            this.apiDeleteOrderStatus(id)
                .then(() => {
                    this.closeModal();
                    this.$emit('onDeleted', id);
                    this.$snotify.success('Order status archived.');
                })
                .catch(error => {
                    if (error.response) {
                        if (error.response.status === 401) {
                            this.$snotify.error(error.response.data.message);
                        }
                    }
                });
        },

        closeModal() {
            $(this.$el).modal('hide');
        }
    },
}
</script>
