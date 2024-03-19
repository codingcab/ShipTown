<template>
<div>
    <div class="d-flex flex-column-reverse flex-sm-row">
        <div class="d-none d-lg-block flex-item">
        </div>
        <div class="flex-item">
            <h4 class="card-title text-center text-sm-left text-lg-center mt-3 mt-sm-0">{{ reportName }}</h4>
        </div>
        <div class="d-flex flex-item">
            <div class="ml-auto">
                <a class="btn btn-primary btn-sm" :href="downloadUrl">{{ downloadButtonText }}</a>
            </div>
            <div>
                <button @click="showFilters = !showFilters" class="d-block btn btn-sm btn-primary ml-1">
                    <template v-if="!showFilters">
                        Filters <span v-show="this.filters.length">({{ this.filters.length }})</span>
                    </template>
                    <template v-else>
                        Hide Filters
                    </template>
                </button>
            </div>
        </div>
    </div>

    <filter-slider v-show="showFilters" class="my-2" :filters="filters" @remove-filter="(filter) => removeFilter(filter)"></filter-slider>

    <table class="table-hover w-100 text-left small table-responsive text-nowrap">
        <thead>
        <tr>
            <th class="small pr-3" v-for="field in fields">
                <a @click.prevent="showFilterBox(field)" class="text-dark pb-1" href="javascript:" :style="[field.is_current ? {'text-decoration': 'underline'} : {}]">
                    <span class="small" v-if="field.is_current">{{ field.is_desc ? '▼' : '▲' }}</span>
                    {{ field.display_name }}
                </a>
            </th>
        </tr>
        </thead>
        <tbody>
            <tr class="table-hover" v-for="record in records">
                <td class="pr-3" v-for="field in fields">{{ getCell(record, field) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="modal fade filter-box-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog g modal-dialog-centered modal-md">
            <div class="modal-content p-2" v-if="filterAdding">
                <div class="p-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" v-model="filterAdding.sortAscending" id="addingSortAsc" @click="toggleAddingSort('asc')">
                        <label class="form-check-label" for="addingSortAsc">
                            Sort ascending
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" v-model="filterAdding.sortDescending" id="addingSortDesc" @click="toggleAddingSort('desc')">
                        <label class="form-check-label" for="addingSortDesc">
                            Sort descending
                        </label>
                    </div>
                </div>
                <p class="small px-2">If you would just like to apply a sort, leave the inputs below empty and click 'add'</p>
                <div class="d-flex flex-column p-2" style="gap: 5px; background-color: #efefef">
                    <div>
                        <select v-model="filterAdding.selectedField" @change="filterFieldChanged()" class="form-control form-control-sm">
                            <option v-for="field in fields" :value="field">{{ field.display_name }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="filterAdding.selectedOperator" class="form-control form-control-sm">
                            <option v-for="operator in filterAdding.operators" :key="operator" :value="operator">
                                {{ operator === 'btwn' ? 'between' : operator }}
                            </option>
                        </select>
                    </div>
                    <div class="d-flex">
                        <template v-if="filterAdding.selectedField.type === 'numeric'">
                            <input ref="inputAddValue" v-model="filterAdding.value" type="number" class="form-control form-control-sm">
                            <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="number" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                        <template v-else-if="filterAdding.selectedField.type === 'datetime'">
                            <input ref="inputAddValue" v-model="filterAdding.value" type="datetime-local" />
                            <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="datetime-local" style="margin-left: 5px;" />
                        </template>
                        <template v-else-if="filterAdding.selectedField.type === 'date'">
                            <input ref="inputAddValue" v-model="filterAdding.value" type="date"/>
                            <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="date"  style="margin-left: 5px;" />
                        </template>
                        <template v-else>
                            <input ref="inputAddValue" v-model="filterAdding.value" type="text" class="form-control form-control-sm">
                            <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="text" class="form-control form-control-sm" style="margin-left: 5px;">
                        </template>
                    </div>
                </div>
                <div class="ml-auto">
                    <button @click="addFilter" class="btn btn-sm btn-primary px-3">
                        add
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</template>

<script>

    import FilterSlider from "./FilterSlider.vue";
    import BarcodeInputField from "../SharedComponents/BarcodeInputField";

    export default {
        components: { FilterSlider, BarcodeInputField },

        props: {
            recordString: String,
            fieldsString: String,
            reportName: String,
            downloadUrl: String,
            downloadButtonText: String,
        },

        data() {
            return {
                records: JSON.parse(this.recordString),
                fields: JSON.parse(this.fieldsString),
                filters: [],
                filterAdding: null,
                findText: '',
                showFilters: true,
            }
        },

        mounted() {
            this.buildFiltersFromUrl()
            this.setFilterAdding()

            // when modal is show focus the input
            $('.filter-box-modal').on('shown.bs.modal', () => {
                this.$refs.inputAddValue.focus();
            });
        },

        methods: {

            getCell(record, field) {
                if (record[field.name] === null) {
                    return '';
                }

                if (field.type === 'datetime') {
                    // todo - extract to a helper function if not already done
                    return record[field.name].replace(/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}).*/, '$3/$2/$1 $4:$5');
                }

                if (field.type === 'date') {
                    // todo - extract to a helper function if not already done
                    return record[field.name].replace(/(\d{4})-(\d{2})-(\d{2}).*/, '$3/$2/$1');
                }

                return record[field.name];
            },

            showFilterBox(field){
                $('.filter-box-modal').modal('show');
                this.setFilterAdding(field.name);
            },

            filterFieldChanged() {
                this.filterAdding.operators = this.filterAdding.selectedField.operators;
                this.filterAdding.selectedOperator = this.filterAdding.selectedField.operators[0];
            },

            setFilterAdding(fieldName = null) {
                let selectedField = fieldName ? this.fields.find(f => f.name === fieldName) : this.fields[0];
                let existingFilter = this.filters.find(f => f.name === selectedField.name);
                let selectedOperator = existingFilter ? existingFilter.selectedOperator : selectedField.operators[0];

                let value = existingFilter ? existingFilter.value : '';
                let valueBetween = existingFilter ? existingFilter.valueBetween : '';

                if(selectedField.type === 'date' && value === '') {
                    let today = new Date();
                    value = today.toISOString().split('T')[0];
                }

                if(selectedField.type === 'datetime' && value === '') {
                    let today = new Date();
                    value = today.toISOString().split('T')[0] + 'T00:00';
                    valueBetween = today.toISOString().split('T')[0] + 'T23:59';
                }

                this.filterAdding = {
                    fields: this.fields,
                    selectedField: selectedField,
                    operators: selectedField.operators,
                    selectedOperator: selectedOperator,
                    value: value,
                    valueBetween: valueBetween,
                    sortAscending: selectedField.is_current && !selectedField.is_desc,
                    sortDescending: selectedField.is_current && selectedField.is_desc,
                }
            },

            toggleAddingSort(direction) {
                this.filterAdding.sortDescending = direction !== 'asc';
                this.filterAdding.sortAscending = direction === 'asc';
            },

            buildFiltersFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);

                for (const [key, value] of urlParams.entries()) {

                    if(key.startsWith('filter')) {

                        let fieldInput = key.split('[')[1].split(']')[0];
                        let fieldName = fieldInput;
                        let operator = 'equals';

                        if(fieldInput.endsWith('contains') || fieldInput.endsWith('between')) {
                            fieldName = fieldInput.split('_').slice(0, -1).join('_');
                            operator = fieldInput.split('_').slice(-1)[0];
                        }

                        let field = this.fields.find(f => f.name === fieldName);

                        let filter = {
                            name: fieldName,
                            displayName: field.display_name,
                            selectedOperator: operator === 'between' ? 'btwn' : operator,
                            value: value,
                            valueBetween: '',
                        }

                        if(operator === 'between') {
                            let values = Array.isArray(value) ? value : value.split(',');
                            filter.value = values[0];
                            filter.valueBetween = values[1];
                        }

                        this.filters.push(filter);
                    }
                }
            },

            addFilter() {
                const { value, selectedOperator, valueBetween, sortAscending, sortDescending, selectedField } = this.filterAdding;

                this.fields.forEach(f => {
                    f.is_current = false;
                    f.is_desc = false;
                    if (f.name === selectedField.name) {
                        f.is_current = sortAscending || sortDescending;
                        f.is_desc = sortDescending;
                    }
                });

                // If any of the fields are empty we should just navigate to the new url without adding the filter, so that a sort can be added without a filter
                if (value.trim() === '' || (selectedOperator === 'btwn' && valueBetween.trim() === '')) {
                    location.href = this.buildUrl();
                    return;
                }

                this.filters = this.filters.filter(f => f.name !== selectedField.name);

                this.filters.push({
                    name: selectedField.name,
                    displayName: selectedField.display_name,
                    selectedOperator,
                    value,
                    valueBetween
                });

                location.href = this.buildUrl();
            },

            removeFilter(field) {
                this.filters = this.filters.filter(f => f !== field);
                location.href = this.buildUrl();
            },

            buildUrl() {
                let baseUrl = window.location.pathname;
                const urlParams = new URLSearchParams(window.location.search);

                const nonFilterParams = [...urlParams.entries()].filter(([key]) => !key.startsWith('filter') && !key.startsWith('sort'))
                    .map(([key, value]) => `${key}=${value}`).join('&');

                const sortField = this.fields.find(f => f.is_current);
                const sortParam = sortField ? `sort=${sortField.is_desc ? '-' : ''}${sortField.name}` : '';

                const filterParams = this.filters.map(filter => {
                    const operator = filter.selectedOperator === 'btwn' ? 'between' : filter.selectedOperator;
                    const value = filter.selectedOperator === 'btwn' ? `${filter.value},${filter.valueBetween}` : filter.value;
                    return `filter[${filter.name}${operator === 'equals' ? '' : `_${operator}`}]=${value}`;
                }).join('&');

                const params = [nonFilterParams, sortParam, filterParams].filter(p => p).join('&');
                return `${baseUrl}?${params}`;
            }
        },
    }
</script>

<style scoped>
    .flex-item {
        flex-basis: 0;
        flex-grow: 1;
    }
</style>
