<template>
    <div class="container" style="min-height: 100vh;" :class="[margin, padding]">
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        margin: {
            type: String,
            default: ''
        },
        padding: {
            type: String,
            default: ''
        }
    }
}
</script>
