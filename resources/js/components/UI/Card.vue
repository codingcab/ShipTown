<template>
    <div class="card" :class="margin">
        <div class="card-body" :class="bodyPadding">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        margin: {
            type: String,
            default: 'mb-5'
        },
        bodyPadding: {
            type: String,
            default: 'px-2 py-2'
        }
    }
}
</script>
