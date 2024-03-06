<template>
    <text-card :label="label" :text="numberFormatted" :min-width="minWidth"></text-card>
</template>

<script>
    import helpers from "../../mixins/helpers";

    export default {
        mixins: [helpers],
        name: "NumberCard",

        props: {
            label: null,
            number: null,
            minWidth: {
                type: String,
                default: '70px'
            }
        },
        computed: {
            labelFormatted() {
                return this.label || '';
            },

            numberFormatted() {
                return this.toNumberOrDash(Number(this.number || 0));
            },
        },
    }
</script>

<style scoped>

</style>
