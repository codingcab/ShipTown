<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    Stocktake Suggestions AI
                </div>
            </div>
            <div class="card-body m-0">
                <div class="font-weight-bold">Minimum Count Date
                    <input class="form-control" id="minimum_count_date" type="date" v-model="configuration.min_count_date">
                </div>
                <div class="small text-secondary">
                    Its the date from which the system will start to count the stock
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../../mixins/api.vue";

export default {
    mixins: [api],

    data: () => ({
        configuration: {
            min_count_date: null,
        },
    }),

    watch: {
        configuration: {
            handler: function (val, oldVal, e) {
                console.log(val, oldVal, e);
            },
            deep: true
        }
    },
    mounted() {

    },

    methods: {
    },
}

</script>

<style lang="scss" scoped>

</style>
