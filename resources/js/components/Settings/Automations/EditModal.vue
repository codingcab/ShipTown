<template>
    <div class="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Automation</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="edit-name">Automation Name</label>
                                        <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                                            <input v-model="automation.name" :class="{
                                                'form-control': true,
                                                'is-invalid': errors.length > 0,
                                            }" id="edit-name" required>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="edit-name">Priority</label>
                                        <ValidationProvider vid="priority" name="priority" v-slot="{ errors }">
                                            <input v-model="automation.priority" :class="{
                                                'form-control': true,
                                                'is-invalid': errors.length > 0,
                                            }" id="edit-priority" type="number">
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="edit-enabled">Enabled</label>
                                        <ValidationProvider vid="enabled" name="enabled" v-slot="{ errors }">
                                            <div class="custom-control custom-switch float-right" :class="{'is-invalid' : errors.length}">
                                                <input type="checkbox" v-model="automation.enabled"
                                                    id="edit-enabled"
                                                    class="custom-control-input"
                                                    required>
                                                <label class="custom-control-label" for="edit-enabled"></label>
                                            </div>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="create-description">Automation Description</label>
                                        <ValidationProvider vid="description" name="description" v-slot="{ errors }">
                                            <textarea v-model="automation.description" :class="{
                                                'form-control': true,
                                                'is-invalid': errors.length > 0,
                                            }" id="create-description"></textarea>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <div class="row">
                                    <div class="col-md-1">
                                        <div class="block-title">When</div>
                                    </div>
                                    <div class="col-md-11">
                                        <ValidationProvider vid="event_class" name="event_class" v-slot="{ errors }">
                                            <select v-model="automation.event_class"
                                                :class="{
                                                    'form-control': true,
                                                    'is-invalid': errors.length > 0,
                                                }"
                                            >
                                                <option value=""></option>
                                                <option v-for="event, index in events" :key="index" :value="event.class">{{ event.description }}</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <div class="row" v-for="condition, index in automation.conditions" :key="condition.id">
                                    <div class="col-md-1">
                                        <div class="block-title" v-if="!index">If</div>
                                    </div>
                                    <div class="col-md-3">
                                        <ValidationProvider :vid="`conditions.${index}.condition_class`" :name="`conditions.${index}.condition_class`" v-slot="{ errors }">
                                            <select v-model="condition.condition_class" :class="{
                                                    'form-control': true,
                                                    'is-invalid': errors.length > 0,
                                                }"
                                            >
                                                <option value="">-</option>
                                                <template v-if="selectedEvent">
                                                    <option v-for="condition, indexOption in selectedEvent.conditions" :key="indexOption" :value="condition.class">{{ condition.description }}</option>
                                                </template>
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-md-8">
                                        <ValidationProvider :vid="`conditions.${index}.condition_value`" :name="`conditions.${index}.condition_value`" v-slot="{ errors }">
                                            <div class="input-group mb-1">
                                                <input v-model="condition.condition_value" :class="{
                                                    'form-control': true,
                                                    'is-invalid': errors.length > 0,
                                                }">
                                                <div class="input-group-append">
                                                    <button class="btn btn-danger" type="button" @click="removeCondition(index)">-</button>
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{ errors[0] }}
                                                </div>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-1">&nbsp;</div>
                                    <div class="col-md-11">
                                        <a href="#" @click="addCondition">+ Add Condition</a>
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <div class="row" v-for="action, index in automation.actions" :key="action.id">
                                    <div class="col-md-1">
                                        <div class="block-title" v-if="!index">Then</div>
                                    </div>
                                    <div class="col-md-3">
                                        <ValidationProvider :vid="`actions.${index}.action_class`" :name="`actions.${index}.action_class`" v-slot="{ errors }">
                                            <select v-model="action.action_class" :class="{
                                                    'form-control': true,
                                                    'is-invalid': errors.length > 0,
                                                }"
                                            >
                                                <option value="">-</option>
                                                <template v-if="selectedEvent">
                                                    <option v-for="action, indexOption in selectedEvent.actions" :key="indexOption" :value="action.class">{{ action.description }}</option>
                                                </template>
                                            </select>
                                            <div class="invalid-feedback">
                                                {{ errors[0] }}
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-md-8">
                                        <ValidationProvider :vid="`actions.${index}.action_value`" :name="`actions.${index}.action_value`" v-slot="{ errors }">
                                            <div class="input-group mb-1">
                                                <input v-model="action.action_value" :class="{
                                                    'form-control': true,
                                                    'is-invalid': errors.length > 0,
                                                }">
                                                <div class="input-group-append">
                                                    <button class="btn btn-danger" type="button" @click="removeAction(index)">-</button>
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{ errors[0] }}
                                                </div>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-1">&nbsp;</div>
                                    <div class="col-md-11">
                                        <a href="#" @click="addAction">+ Add Action</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click="runAutomation" class="btn btn-outline-danger float-left">Run</button>
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

export default {
    name: "EditModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    props: ['events', 'selectedAutomation'],

    watch: {
        selectedAutomation: function(newVal) {
            this.getAutomation(newVal.id)
        },

        "automation.event_class": function(newValue) {
            if(newValue === ""){
                this.automation.conditions.forEach(condition => {
                    condition.condition_class = ""
                });

                this.automation.actions.forEach(condition => {
                    condition.action_class = ""
                });
            }
        },

        confirmDelete(selectedAutomation) {
          const indexAutomations = this.automation;
          this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
            position: 'centerCenter',
            buttons: [
              {
                text: 'Yes',
                action: (toast) => {
                  this.delete(selectedAutomation.id, indexAutomations)
                  this.$snotify.remove(toast.id);
                }
              },
              {text: 'Cancel'},
            ]
          });
        },

    },

    data() {
        return {
            automation: {},
        }
    },

    computed: {
        selectedEvent(){
            return this.events.find(event => event.class === this.automation.event_class);
        }
    },

    mounted() {
    },

    methods: {

        getAutomation(id){
            this.showLoading();
            this.apiShowAutomations(id)
                .then(({ data }) => {
                    this.automation = data.data
                })
                .finally(this.hideLoading);
        },

        addCondition(){
            this.automation.conditions.push({
                id: Date.now(),
                condition_class: '',
                condition_value: ''
            });
        },

        addAction(){
            this.automation.actions.push({
                id: Date.now(),
                action_class: '',
                action_value: ''
            });
        },

        removeCondition(index){
            this.automation.conditions.splice(index, 1);
            if(!this.automation.conditions.length){
                this.addCondition();
            }
        },

        removeAction(index){
            this.automation.actions.splice(index, 1);
            if(!this.automation.actions.length){
                this.addAction();
            }
        },

        submit() {
            this.showLoading();
            this.apiPutAutomations(this.automation.id, this.automation)
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

        runAutomation() {
            this.showLoading();
            this.apiRunAutomation(this.automation.id)
                .then(() => {
                    this.$snotify.success('Automation run requested');
                    this.closeModal();
                })
                .catch((error) => {
                    this.$snotify.error(error.response.status +' '+error.response.statusText+'');
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        }
    },
}
</script>

<style scoped>
.block {
    background-color: #f1f1f1;
    border-radius: 8px;
    padding: .5rem;
    margin: .5rem 0;
}
.block-title {
    padding: 7px 0 0 10px;
    font-weight: 600;
}
</style>
