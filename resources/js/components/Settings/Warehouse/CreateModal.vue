<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Warehouse</h5>
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
                                        }" id="create-code" required>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
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
                                            }" id="create-name" required>
                                    <div class="invalid-feedback">
                                      {{ errors[0] }}
                                    </div>
                                  </ValidationProvider>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
                    <button type="button" @click="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
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

    data() {
        return {
            name: '',
            code: '',
        }
    },

    methods: {

        submit() {
            this.showLoading();
            this.apiPostWarehouses({
                    name: this.name,
                    code: this.code,
                })
                .then(({ data }) => {
                    this.$snotify.success('Warehouse created.');
                    this.closeModal();
                    this.resetForm()
                    this.$emit('onCreated', data.data);
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
            this.name = '';
            this.code = '';
        },

        closeModal() {
            $(this.$el).modal('hide');
        }
    },
}
</script>
