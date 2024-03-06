<template>
    <div class="text-center border-0 d-inline-block p-0 m-0" :style="{ minWidth: minWidth}">
        <div class="small text-secondary">
            <small class="small text-secondary">{{ this.label }}</small>
        </div>
        <h4>{{ this.text || '&nbsp;'}}</h4>
    </div>
</template>

<script>
    export default {
        name: "TextCard",

        props: {
            label: null,
            text: null,
            minWidth: {
                type: String,
                default: '70px'
            }
        },
    }
</script>

<style scoped>

</style>
