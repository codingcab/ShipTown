<template>
    <b-modal id="modal-date-selector-widget" size="sm" no-fade hide-header>
        <form class="form" @submit.prevent="">
            <div class="form-group">
                <label class="form-label" for="starting_date">From</label>
                <input
                    :value="starting_date"
                    @input="$emit('update:starting_date', $event.target.value)"
                    class="form-control"
                    id="starting_date"
                    type="datetime-local"
                >
            </div>
            <div class="form-group">
                <label class="form-label" for="ending_date">To</label>
                <input
                    class="form-control"
                    id="ending_date"
                    type="datetime-local"
                    :value="ending_date"
                    @input="$emit('update:ending_date', $event.target.value)"
                >
            </div>
        </form>
        <template #modal-footer>
            <b-button b-button variant="secondary" class="float-right" @click="$emit('close')">Cancel</b-button>
            <b-button variant="primary" class="float-right" @click="$emit('apply')">Apply</b-button>
        </template>
    </b-modal>
</template>

<script>
export default {
    props: ['starting_date', 'ending_date'],
}
</script>
