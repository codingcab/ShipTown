<template>
    <input :id="inputId"
           :placeholder="placeholder"
           type=text
           class="form-control barcode-input"
           autocomplete="off"
           autocapitalize="off"
           enterkeyhint="done"
           v-model.trim="searchInput"
           @keyup.enter="$emit('search', searchInput)"
    />
</template>

<script>
export default {
    props: {
        inputId: {
            type: String,
            default: 'search-filter'
        },
        placeholder: {
            type: String,
            default: 'Search filter...'
        }
    },
    data() {
        return {
            searchInput: ''
        }
    },
}
</script>
