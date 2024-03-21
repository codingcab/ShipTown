<template>
    <dialog id="modal-date-selector-widget" class="m-auto border-light rounded">
        <div class="row">
            <div class="col">
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
            </div>
        </div>
        <div class="row">
            <div class="col text-right">
                <div>
                    <button type="button" @click="$emit('close')" class="btn btn-default">Cancel</button>
                    <button type="button" @click="$emit('apply')" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </div>
    </dialog>
</template>

<script>
export default {
    props: ['starting_date', 'ending_date'],
}
</script>
