<template>
    <div class="modal fade set-shipping-number-modal" @ id="shippingNumberModal" tabindex="-1" role="dialog" aria-labelledby="setShippingNumberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="setShippingNumberModal" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shipping Number</h5>
                    <div class="widget-tools-container">
                        <font-awesome-icon icon="question-circle" :content="helpText"></font-awesome-icon>
                    </div>
                </div>
                <div class="modal-body row">
                    <div class="col m-0 p-0">
                        <form class="m-0 p-0" method="POST" @submit.prevent="setShippingNumber">
                            <div class="form-group form-check m-0 p-0">
                                <input id="shipping_number_input" ref="shipping_number" class="form-control" placeholder="Scan shipping number"
                                       v-model="shipping_number"
                                       @focus="simulateSelectAll"/>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" @click.prevent="setShippingNumber" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import helpers from "../../mixins/helpers";

export default {
    mixins: [helpers],

    name: "SetShippingNumberModal",

    data: function() {
        return {
            shipping_number: ''
        }
    },

    computed: {
        helpText() {
            return 'Single line orders are orders with only single product ordered.';
        }
    },

    methods: {
        focusOnInput() {
            this.setFocusElementById('shipping_number_input')
        },

        setFocus() {
            // we using readOnly field here to prevent
            // on-screen keyboard showing up
            // on phones and tablets
            this.$refs.shipping_number.readOnly = true;
            this.$refs.shipping_number.focus();
            this.$refs.shipping_number.readOnly = false;
        },

        setShippingNumber() {
            if (!this.shipping_number) {
                this.notifyError('Shipping number missing');
                this.focusOnInput();
                return;
            }

            this.hide();
            this.$emit('shippingNumberUpdated', this.shipping_number);
            this.shipping_number = '';
        },

        simulateSelectAll() {
            setTimeout(() => { document.execCommand('selectall', null, false); }, 100);
        },

        hide() {
            $(this.$el).modal('hide');
        }
    }
}
</script>

<style scoped>

</style>
