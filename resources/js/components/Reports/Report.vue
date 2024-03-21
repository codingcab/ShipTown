<template>
<div>
    <template v-if="getUrlParameter('hide_nav_bar', false) === false">
        <div class="row mb-3 pl-1 pr-1">
            <div class="flex-fill">
                <barcode-input-field placeholder="Search activity" ref="barcode" @refreshRequest="reloadProducts" @barcodeScanned="findText"/>
            </div>
            <button v-b-modal="'quick-actions-modal'" type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#filterConfigurationModal">
                <font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon>
            </button>
        </div>
    </template>

    <div class="d-lg-flex">
        <div class="text-nowrap font-weight-bold small text-secondary">
            REPORTS > {{ reportName.toUpperCase() }}
        </div>
        <div class="flex-grow-1">
            <filter-slider v-show="showFilters" :filters="filters" @remove-filter="(filter) => removeFilter(filter)"></filter-slider>
        </div>
    </div>

    <card>
        <table class="table-hover w-100 text-left small table-responsive text-nowrap">
            <thead>
            <tr>
                <th class="small pr-3" v-for="field in fields">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="small" v-if="field.is_current">{{ field.is_desc ? '▼' : '▲' }}</span> {{ field.display_name }}
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
        <hr>
        <div class="row mx-2 small" v-if="pagination">
            <div class="col-5 col-sm-3">
                <p>
                    <select v-model="pagination.per_page" @change="changePagination('perPage')">
                        <option v-for="option in perPageOptions" :value="option">{{ option }}</option>
                    </select>
                    records
                </p>
            </div>
            <div class="col-7 col-sm-6">
                <p class="text-right text-sm-center">
                    <button class="no-style-button" @click="changePagination('decrementPage')">
                        <icon-arrow-left/>
                    </button>
                    page
                    <input type="text" v-on:input="customChangePagination" v-model.number="pagination.current_page" style="max-width: 40px; height: 20px"/>
                    of {{ pagination.last_page }}
                    <button class="no-style-button" @click="changePagination('incrementPage')">
                        <icon-arrow-right/>
                    </button>
                </p>
            </div>
            <div class="col-12 col-sm-3">
                <p class="float-right mr-1 mr-sm-0">{{ pagination.total }} records</p>
            </div>
        </div>

        <div class="modal fade filter-box-modal" tabindex="-1" role="dialog"  aria-hidden="true">
            <div
                v-if="filterAdding"
                class="modal-dialog g modal-dialog-centered"
                :class="[['datetime', 'date'].includes(filterAdding.selectedField.type) ? 'modal-sm' : 'modal-md']"
            >
                <div class="modal-content p-2">
                    <div class="d-flex flex-column p-2" style="gap: 5px; background-color: #efefef">
                        <div v-show="filterAdding.operators.length > 1">
                            <select v-model="filterAdding.selectedOperator" class="form-control form-control-sm">
                                <option v-for="operator in filterAdding.operators" :key="operator" :value="operator">
                                    {{ operator === 'btwn' ? 'between' : operator }}
                                </option>
                            </select>
                        </div>
                        <div class="d-flex flex-row'" style="grid-gap: 5px;">
                            <template v-if="filterAdding.selectedField.type === 'numeric'">
                                <input ref="inputAddValue" v-model="filterAdding.value" type="number" class="form-control form-control-sm">
                                <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="number" class="form-control form-control-sm" style="margin-left: 5px;">
                            </template>
                            <template v-else>
                                <input ref="inputAddValue" v-model="filterAdding.value" type="text" class="form-control form-control-sm">
                                <input v-if="filterAdding.selectedOperator === 'btwn'" v-model="filterAdding.valueBetween" type="text" class="form-control form-control-sm" style="margin-left: 5px;">
                            </template>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <button type="button" @click="closeFilterBoxModal" class="btn btn-default">Cancel</button>
                        <button @click="addFilter" class="btn btn-sm btn-primary px-3">
                            apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ModalDateBetweenSelector
            :starting_date.sync="filterAdding.value"
            :ending_date.sync="filterAdding.valueBetween"
            @close="closeDateSelectionModal"
            @apply="addFilter"
        />

        <b-modal id="quick-actions-modal" no-fade hide-header @hidden="setFocusElementById('barcode-input')">
            <stocktake-input v-bind:auto-focus-after="100" ></stocktake-input>
            <hr>
            <template #modal-footer>
                <a class="btn btn-primary" :href="downloadUrl">{{ downloadButtonText }}</a>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    Cancel
                </b-button>
                <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    OK
                </b-button>
            </template>
        </b-modal>
    </card>
</div>

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

    export default {

        mixins: [loadingOverlay, url, api, helpers],

        components: {IconArrowRight, IconArrowLeft, IconSortAsc, IconSortDesc, IconFilter, ModalDateBetweenSelector},

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
                findText: '',
                showFilters: true,
            }
        },

        beforeMount() {
            this.setFilterAdding()
        },

        mounted() {
            this.buildFiltersFromUrl()

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

                // if datetime field, show date selector
                if(['date', 'datetime'].includes(field.type)) {
                    document.getElementById('modal-date-selector-widget').showModal();
                    this.setFilterAdding(field.name);
                    return;
                }

                $('.filter-box-modal').modal('show');
                this.setFilterAdding(field.name);
            },

            closeFilterBoxModal() {
                $('.filter-box-modal').modal('hide');
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

            closeDateSelectionModal() {
                document.getElementById('modal-date-selector-widget').close();
            }
        },

        computed: {

            // todo - could probably make this static with some general assumptions about total records, 10, 25, 50, 100 etc
            perPageOptions(){
                let options = [this.pagination.per_page, this.pagination.total]; // Start with current per_page and total

                // Add half of per_page if it's more than 1
                if (this.pagination.per_page > 1) {
                    options.push(Math.floor(this.pagination.per_page / 2));
                }

                // Generate multiples of per_page up to total, with a limit of 6 options including the ones already added
                for (let multiplier = 2; options.length < 6; multiplier *= 2) {
                    let nextOption = this.pagination.per_page * multiplier;
                    if (nextOption >= this.pagination.total) break; // Stop if the next option exceeds the total
                    options.push(nextOption);
                }

                // Remove duplicates, sort numerically, and return
                return [...new Set(options)].sort((a, b) => a - b);
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

.no-style-button {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
}

</style>
