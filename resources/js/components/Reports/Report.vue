<template>

<container>
    <top-nav-bar>
        <search-filter @search="addSearchFilter" />
    </top-nav-bar>

    <report-head :report-name="reportName" :filters="filters" @remove-filter="(filter) => removeFilter(filter)"></report-head>

    <card>
        <template v-if="records.length">
            <div class="table-responsive py-2" style="transform: rotateX(180deg);">
                <table class="table-hover w-100 text-left small text-nowrap" style="transform: rotateX(180deg);">
                    <thead>
                    <tr>
                        <th class="small pr-3" v-for="field in fields">
                            <div class="dropdown">
                                <button class="btn btn-link dropdown-toggle" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ field.display_name }} <span class="small" v-if="field.is_current">{{ field.is_desc ? '▼' : '▲' }}</span>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                    <button class="dropdown-item" type="button" @click="applySort('asc', field)">
                                        <icon-sort-asc/>&nbsp; Sort Ascending
                                    </button>
                                    <button class="dropdown-item" type="button" @click="applySort('desc', field)">
                                        <icon-sort-desc/>&nbsp; Sort Descending
                                    </button>
                                    <button class="dropdown-item" type="button" @click="showFilterBox(field)">
                                        <icon-filter/>&nbsp; Filter by value
                                    </button>
                                </div>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="table-hover" v-for="record in records">
                        <td class="pr-3" v-for="field in fields">{{ getCell(record, field) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <hr>
            <div class="row mx-2 small" v-if="pagination">
                <div class="col col-sm-4">
                    <p>
                        show
                        <select v-model="pagination.per_page" @change="changePagination('perPage')">
                            <option v-for="option in perPageOptions" :value="option">{{ option }}</option>
                        </select>
                        records
                    </p>
                </div>
                <div class="col col-sm-4">
                    <div class="d-flex justify-content-end justify-content-sm-center">
                        <div>
                            <button class="no-style-button mr-md-3" @click="changePagination('decrementPage')">
                                <icon-arrow-left/>
                            </button>
                        </div>
                        <div>
                            <p class="pb-0" style="margin-top: 0.1rem">Page</p>
                        </div>
                        <div>
                            <input type="text"
                                   v-on:input="customChangePagination"
                                   v-model.number="pagination.current_page"
                                   style="max-width: 30px; height: 19px; margin-top: 0.1rem;"
                                   class="mx-1 text-center"
                            />
                        </div>
                        <div>
                            <p class="pb-0" style="margin-top: 0.1rem">of {{ pagination.last_page }}</p>
                        </div>
                        <div>
                            <button class="no-style-button ml-md-3" @click="changePagination('incrementPage')">
                                <icon-arrow-right/>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <p class="float-right mr-1 mr-sm-0">{{ pagination.total }} records</p>
                </div>
            </div>
        </template>
        <div v-else class="text-secondary small text-center">
            No records found
        </div>
    </card>

    <b-modal id="filter-box-modal" size="md" no-fade hide-header @shown="focusFilterBoxInput">
        <div v-if="filterAdding">
            <div class="d-flex flex-column p-2" style="gap: 5px; background-color: #efefef">
                <div v-show="filterAdding.operators.length > 1">
                    <select v-model="filterAdding.selectedOperator" @change="focusFilterBoxInput" class="form-control form-control-sm">
                        <option v-for="operator in filterAdding.operators" :key="operator" :value="operator">
                            {{ operator === 'btwn' ? 'between' : operator }}
                        </option>
                    </select>
                </div>
                <form @submit.prevent="addFilter" @keyup.enter="addFilter" class="d-flex flex-row" style="grid-gap: 5px;">
                    <template v-if="filterAdding.selectedField.type === 'numeric'">
                        <input ref="inputAddValue" v-model="filterAdding.value" type="number" class="form-control form-control-sm">
                        <input v-show="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="number" class="form-control form-control-sm" style="margin-left: 5px;">
                    </template>
                    <template v-else>
                        <input ref="inputAddValue" v-model="filterAdding.value" type="text" class="form-control form-control-sm">
                        <input v-show="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="text" class="form-control form-control-sm" style="margin-left: 5px;">
                    </template>
                </form>
            </div>
        </div>
        <template #modal-footer>
            <b-button b-button variant="secondary" class="float-right" @click="$bvModal.hide('filter-box-modal')">Cancel</b-button>
            <b-button variant="primary" class="float-right" @click="addFilter">apply</b-button>
        </template>
    </b-modal>

    <ModalDateBetweenSelector
        :starting_date.sync="filterAdding.value"
        :ending_date.sync="filterAdding.valueBetween"
        @close="$bvModal.hide('modal-date-selector-widget')"
        @apply="addFilter"
    />

    <b-modal id="quick-actions-modal" no-fade hide-header @hidden="setFocusElementById('barcode-input')">
        <stocktake-input v-bind:auto-focus-after="100" ></stocktake-input>
        <hr>
        <br>
        <a class="btn btn-primary btn-block" :href="downloadUrl">{{ downloadButtonText }}</a>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                Cancel
            </b-button>
            <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                OK
            </b-button>
        </template>
    </b-modal>

</container>

</template>

<script>
    import loadingOverlay from '../../mixins/loading-overlay';
    import url from "../../mixins/url";
    import api from "../../mixins/api";
    import helpers from "../../mixins/helpers";
    import IconSortAsc from "../UI/Icons/IconSortAsc.vue";
    import IconSortDesc from "../UI/Icons/IconSortDesc.vue";
    import IconFilter from "../UI/Icons/IconFilter.vue";
    import IconArrowRight from "../UI/Icons/IconArrowRight.vue";
    import IconArrowLeft from "../UI/Icons/IconArrowLeft.vue";
    import ModalDateBetweenSelector from "../Widgets/ModalDateBetweenSelector.vue";
    import SearchFilter from "./SearchFilter.vue";
    import ReportHead from "./ReportHead.vue";

    export default {

        mixins: [loadingOverlay, url, api, helpers],

        components: {IconArrowRight, IconArrowLeft, IconSortAsc, IconSortDesc, IconFilter, ModalDateBetweenSelector, SearchFilter, ReportHead},

        props: {
            recordString: String,
            fieldsString: String,
            reportName: String,
            downloadUrl: String,
            downloadButtonText: String,
            paginationString: String,
        },

        data() {
            return {
                records: JSON.parse(this.recordString),
                fields: JSON.parse(this.fieldsString),
                pagination: JSON.parse(this.paginationString),
                filters: [],
                filterAdding: null,
                showFilters: true,
            }
        },

        beforeMount() {
            this.setFilterAdding()
        },

        mounted() {
            this.buildFiltersFromUrl()
        },

        methods: {

            focusFilterBoxInput() {
                this.$refs.inputAddValue.focus();
            },

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
                if(['date', 'datetime'].includes(field.type)) {
                    this.$bvModal.show('modal-date-selector-widget')
                    this.setFilterAdding(field.name);
                    return;
                }

                this.$bvModal.show('filter-box-modal')
                this.setFilterAdding(field.name);
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
                }
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

            addSearchFilter(searchInput) {
                this.filters = this.filters.filter(f => f.name !== 'search');
                this.filters.push({
                    name: 'search',
                    displayName: 'Search',
                    selectedOperator: 'equals',
                    value: searchInput,
                    valueBetween: '',
                });

                this.pagination.current_page = 1;
                location.href = this.buildUrl();
            },

            addFilter() {
                const { value, selectedOperator, valueBetween, selectedField } = this.filterAdding;

                this.filters = this.filters.filter(f => f.name !== selectedField.name);

                this.filters.push({
                    name: selectedField.name,
                    displayName: selectedField.display_name,
                    selectedOperator,
                    value,
                    valueBetween
                });

                // reset pagination
                this.pagination.current_page = 1;

                location.href = this.buildUrl();
            },

            removeFilter(field) {
                this.filters = this.filters.filter(f => f !== field);
                location.href = this.buildUrl();
            },

            buildUrl() {
                let baseUrl = window.location.pathname;

                const paginationParams = `per_page=${this.pagination.per_page}&page=${this.pagination.current_page}`;

                const sortField = this.fields.find(f => f.is_current);
                const sortParam = sortField ? `sort=${sortField.is_desc ? '-' : ''}${sortField.name}` : '';

                const filterParams = this.filters.map(filter => {
                    const operator = filter.selectedOperator === 'btwn' ? 'between' : filter.selectedOperator;
                    const value = filter.selectedOperator === 'btwn' ? `${filter.value},${filter.valueBetween}` : filter.value;
                    return `filter[${filter.name}${operator === 'equals' ? '' : `_${operator}`}]=${value}`;
                }).join('&');

                const params = [paginationParams, sortParam, filterParams].filter(p => p).join('&');
                return `${baseUrl}?${params}`;
            },

            applySort(direction, field) {
                this.fields.forEach(f => {
                    f.is_current = false;
                    f.is_desc = false;
                    if (f.name === field.name) {
                        f.is_current = true;
                        f.is_desc = direction === 'desc';
                    }
                });

                location.href = this.buildUrl();
            },

            customChangePagination: _.debounce(function() {
                if(this.pagination.current_page < 1) {
                    this.pagination.current_page = 1;
                }else if(this.pagination.current_page > this.pagination.last_page) {
                    this.pagination.current_page = this.pagination.last_page;
                }
                this.changePagination();
            }, 800),

            changePagination(changeType = null) {
                if (changeType === 'perPage') {
                    this.pagination.current_page = 1;
                    location.href = this.buildUrl();
                } else if (changeType === 'incrementPage' && this.pagination.current_page < this.pagination.last_page) {
                    this.pagination.current_page++;
                    location.href = this.buildUrl();
                } else if (changeType === 'decrementPage' && this.pagination.current_page > 1) {
                    this.pagination.current_page--;
                    location.href = this.buildUrl();
                }

                location.href = this.buildUrl();
            },
        },

        computed: {
            perPageOptions(){
                return [...new Set([this.pagination.per_page, 10, 25, 50, 100])].sort((a, b) => a - b);
            }
        }
    }
</script>

<style scoped>

.dropdown > .btn.dropdown-toggle {
    font-size: 12px;
    padding: 0;
    color: black;
    font-weight: bold;
    &:focus, &:active {
        outline: none;
        box-shadow: none;
        border-color: transparent;
    }
}

.dropdown-toggle::after {
    display: none;
}

.no-style-button {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
}

</style>
