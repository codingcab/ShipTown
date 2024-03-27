<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mail Template</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="form-group">
                                <label class="form-label" for="to">To</label>
                                <ValidationProvider vid="to" name="to" v-slot="{ errors }">
                                    <input type="text"
                                        id="edit-to"
                                        class="form-control"
                                        :class="{'is-invalid' : errors.length}"
                                        v-model="to"
                                        required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reply_to">Reply To</label>
                                <ValidationProvider vid="reply_to" name="reply_to" v-slot="{ errors }">
                                    <input type="text"
                                           id="edit-reply_to"
                                           class="form-control"
                                           :class="{'is-invalid' : errors.length}"
                                           v-model="replyTo"
                                           required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="subject">Subject</label>
                                <ValidationProvider vid="subject" name="subject" v-slot="{ errors }">
                                    <input type="text"
                                           id="edit-subject"
                                           class="form-control"
                                           :class="{'is-invalid' : errors.length}"
                                           v-model="subject"
                                           required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="html_template">Html Template</label>
                                <ValidationProvider vid="html_template" name="html_template" v-slot="{ errors }">
                                    <textarea
                                        id="edit-html_template"
                                        class="form-control"
                                        rows="5"
                                        :class="{'is-invalid' : errors.length}"
                                        v-model="htmlTemplate"
                                        ></textarea>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="text_template">Text Template</label>
                                <ValidationProvider vid="text_template" name="text_template" v-slot="{ errors }">
                                    <textarea
                                        id="edit-text_template"
                                        class="form-control"
                                        rows="5"
                                        :class="{'is-invalid' : errors.length}"
                                        v-model="textTemplate"
                                        ></textarea>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
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
    name: "EditModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    data() {
        return {
            subject: "",
            replyTo: "",
            to: "",
            htmlTemplate: "",
            textTemplate: "",
        }
    },

    props: {
        mailTemplate: Object,
    },

    watch: {
        mailTemplate: function(newVal) {
            this.subject = newVal.subject
            this.replyTo = newVal.reply_to
            this.to = newVal.to
            this.htmlTemplate = newVal.html_template
            this.textTemplate = newVal.text_template
        }
    },

    methods: {

        submit() {
            this.showLoading();
            this.apiPutMailTemplate(this.mailTemplate.id, {
                    subject: this.subject,
                    reply_to: this.replyTo,
                    to: this.to,
                    html_template: this.htmlTemplate,
                    text_template: this.textTemplate,
                })
                .then(({ data }) => {
                    this.$snotify.success('Order status updated.');
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
                })
                .catch((error) => {
                    let response = error.response;

                    if (response) {
                        if (response.status === 422) {
                          this.$refs.form.setErrors(response.data.errors);
                        } else {
                          this.$snotify.error('Error saving template: ' + response.status + ' ' + response.data.message);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        }
    },
}
</script>
