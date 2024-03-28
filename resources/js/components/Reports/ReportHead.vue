<template>
    <div>
        <div class="d-flex">
            <div class="font-weight-bold text-uppercase small text-secondary">
                REPORTS > {{ reportName.toUpperCase() }}
            </div>
            <div class="flex-grow-1">
                <div class="filter-container d-none d-lg-flex" ref="filterContainer">
                    <p class="text-primary small" v-for="filter in filters" :key="filter.id">
                        {{ filter.displayName }} <span v-html="filterExtendedOverview(filter)"></span><!--
                        --><button @click="handleClick(filter, $event)" class="btn btn-link p-0 ml-1 mb-1">x</button>
                    </p>
                </div>
                <a href="#" @click.prevent="showFilters = !showFilters" class="float-right d-lg-none small">
                    {{ showFilters ? 'HIDE' : 'FILTERS' }} <span v-show="!showFilters">({{ filters.length }})</span>
                </a>
            </div>
        </div>

        <div class="filter-container d-flex d-lg-none" ref="filterContainer">
            <p v-if="showFilters" class="text-primary small" v-for="filter in filters" :key="filter.id">
                {{ filter.displayName }} <span v-html="filterExtendedOverview(filter)"></span><!--
            --><button @click="handleClick(filter, $event)" class="btn btn-link p-0 ml-1 mb-1">x</button>
            </p>
        </div>
    </div>
</template>

<script>
export default {

    props: {
        reportName: String,
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

.btn-link {
    outline: none;
    box-shadow: none;
    border-color: transparent;
}

</style>
