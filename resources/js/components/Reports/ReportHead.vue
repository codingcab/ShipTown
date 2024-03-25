<template>
    <div>
        <div class="filter-container d-none d-lg-flex" ref="filterContainer">
            <p class="text-primary small" v-for="filter in filters" :key="filter.id">
                {{ filter.displayName }} <span v-html="filterExtendedOverview(filter)"></span><!--
            --><button @click="handleClick(filter, $event)" class="btn btn-link p-0 ml-1 mb-1">x</button>
            </p>
        </div>
        <div class="filter-container d-flex d-lg-none" ref="filterContainer">
            <p v-if="showFilters" class="text-primary small" v-for="filter in filters" :key="filter.id">
                {{ filter.displayName }} <span v-html="filterExtendedOverview(filter)"></span><!--
            --><button @click="handleClick(filter, $event)" class="btn btn-link p-0 ml-1 mb-1">x</button>
            </p>
            <button @click="showFilters = !showFilters" class="btn btn-link p-0 ml-1 mb-1">
                <font-awesome-icon :icon="showFilters ? 'chevron-up' : 'chevron-down'" class="fa-lg"></font-awesome-icon>
            </button>
        </div>
    </div>
</template>

<script>
export default {

    props: {
        filters: Array,
    },

    data() {
        return {
            showFilters: false,
        }
    },

    methods: {
        handleClick(filter, event) {
            this.$emit('remove-filter', filter);
        },

        filterExtendedOverview(filter) {
            if(filter.selectedOperator === 'btwn') {
                return `between ${filter.value} <b>&</b> ${filter.valueBetween}`;
            }
            return `${filter.selectedOperator} ${filter.value}`;
        },
    }
}
</script>

<style scoped>

.filter-container {
    flex-direction: row-reverse;
    flex-wrap: wrap;
    p {
        flex: 0 0 auto;
        margin: 0 0 0 10px;
        -webkit-user-select: none;
    }
}

</style>
