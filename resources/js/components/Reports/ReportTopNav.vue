<template>
    <div>
        <div v-show="showFilters" class="mb-2 py-2" style="background-color: #F6F6F6">
            <div v-for="(filter, index) in filters" :key="index" class="px-2">
                <div v-if="filter.editingOrAdding" class="d-flex flex-column flex-md-row my-2" style="gap: 5px">
                    <div>
                        <select v-model="filter.selectedField" @change="filterFieldChanged(filter)" class="form-control form-control-sm">
                            <option v-for="field in fields" :value="field">{{ field.display_name }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="filter.selectedOperator" class="form-control form-control-sm">
                            <option v-for="operator in filter.operators" :key="operator" :value="operator">
                                {{ operator === 'btwn' ? 'between' : operator }}
                            </option>
                        </select>
                    </div>
                    <div class="d-flex">
                        <template v-if="filter.type === 'numeric'">
                            <input v-model="filter.value" type="number" class="form-control form-control-sm">
                            <input v-if="filter.selectedOperator === 'btwn'" v-model="filter.valueBetween" type="number" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                        <template v-else-if="filter.type === 'datetime'">
                            <input v-model="filter.value" type="datetime-local" class="form-control form-control-sm">
                            <input v-if="filter.selectedOperator === 'btwn'" v-model="filter.valueBetween" type="datetime-local" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                        <template v-else-if="filter.type === 'date'">
                            <input v-model="filter.value" type="date" class="form-control form-control-sm">
                            <input v-if="filter.selectedOperator === 'btwn'" v-model="filter.valueBetween" type="date" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                        <template v-else>
                            <input v-model="filter.value" type="text" class="form-control form-control-sm">
                            <input v-if="filter.selectedOperator === 'btwn'" v-model="filter.valueBetween" type="text" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                    </div>
                    <div class="ml-auto">
                        <button v-if="filter.editingOrAdding === 'adding'" @click="applyFilter(filter)" class="btn btn-sm border border-dark px-4 bg-white">+</button>
                        <button v-if="filter.editingOrAdding === 'editing'" @click="removeFilter(index)" class="btn btn-link p-0">Remove</button>
                        <button v-if="filter.editingOrAdding === 'editing'" @click="applyFilter(filter)" class="btn btn-link p-0">Update</button>
                    </div>
                </div>
                <div v-else>
                    <div class="d-flex">
                        <div>
                            <span>{{ filter.selectedField.display_name }} {{ filter.selectedOperator === 'btwn' ? 'between' : filter.selectedOperator }} {{ filter.value }} {{ filter.valueBetween }}</span>
                        </div>
                        <div class="ml-auto pl-2">
                            <button v-if="!filter.editingOrAdding" @click="editFilter(filter)" class="btn btn-link p-0">Edit</button>
                        </div>
                    </div>
                    <hr v-show="filter !== filters[filters.length - 1]" style="margin-top: 0; margin-bottom: 0;">
                </div>
            </div>
            <div v-if="!addingFilterShown && !filterIsEditing">
                <hr style="margin-top: 0; margin-bottom: 0;">
                <button @click="addNewFilter" class="btn btn-link">add new filter</button>
            </div>
        </div>
        <div class="d-flex flex-column-reverse flex-sm-row">
            <div class="d-none d-lg-block flex-fill">
            </div>
            <div class="flex-lg-fill">
                <h4 class="card-title text-center mt-2 mt-sm-0">{{ reportName }}</h4>
            </div>
            <div class="d-flex flex-fill">
                <div class="ml-auto">
                    <a class="btn btn-primary btn-sm" :href="downloadUrl">{{ downloadButtonText }}</a>
                </div>
                <div>
                    <button @click="toggleAddFilter" class="d-block btn btn-sm btn-primary ml-1">
                        <template v-if="!showFilters">
                            Filters <span v-show="numOfFilters > 0">({{ numOfFilters }})</span>
                        </template>
                        <template v-else>
                            Hide Filters
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        reportName: String,
        downloadUrl: String,
        downloadButtonText: String,
        fieldsString: String
    },
    data() {
        return {
            filters: [],
            showFilters: false,
            fields: JSON.parse(this.fieldsString),
        }
    },
    methods: {

        toggleAddFilter() {
            this.showFilters = !this.showFilters;

            if(this.showFilters && !this.filters.length) {
                this.addNewFilter();
                return;
            }

            this.removeLastFilterIfInAddingMode();
        },

        addNewFilter() {

            if(this.filters.length) {
                this.filters[this.filters.length - 1].editingOrAdding = false;
            }

            this.filters.push({
                fields: this.fields,
                selectedField: this.fields[0],
                operators: this.fields[0].operators,
                selectedOperator: this.fields[0].operators[0],
                value: '',
                valueBetween: '',
                editingOrAdding: 'adding',
            });
        },

        editFilter(filter) {

            this.removeLastFilterIfInAddingMode();

            // close all other filters in editing mode
            this.filters.forEach(f => {
                f.editingOrAdding = false;
            });

            filter.editingOrAdding = 'editing';
        },

        removeFilter(index) {
            this.filters.splice(index, 1);

            if(this.filters.length === 0) {
                this.showFilters = false;
            }
        },

        applyFilter(filter) {
            filter.editingOrAdding = false;

            // call api to apply filter
        },

        removeLastFilterIfInAddingMode() {
            if(this.filters.length && this.filters[this.filters.length - 1].editingOrAdding === 'adding') {
                this.filters.pop();
            }
        },

        filterFieldChanged(filter) {
            filter.operators = filter.selectedField.operators;
            filter.selectedOperator = filter.selectedField.operators[0];
        }
    },
    computed: {
        numOfFilters() {
            return this.filters.length;
        },
        addingFilterShown() {
            return this.filters.length && this.filters[this.filters.length - 1].editingOrAdding === 'adding';
        },
        filterIsEditing() {
            return this.filters.some(f => f.editingOrAdding === 'editing');
        }
    }
}
</script>
