<template>
<div>
    <div class="text-right">
        <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle text-primary font-weight-bold" type="button" id="dropdownDateRange" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ formatted_starting_date }} - {{ formatted_ending_date }}
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownDateRange">
                <a class="dropdown-item" @click.prevent="applyFilter('today,now')">Today</a>
                <a class="dropdown-item" @click.prevent="applyFilter('yesterday,today')">Yesterday</a>
                <a class="dropdown-item" @click.prevent="applyFilter('-7days,now')">Last 7 days</a>
                <a class="dropdown-item" @click.prevent="applyFilter('this week monday,now')">This week</a>
                <a class="dropdown-item" @click.prevent="applyFilter('last week monday,this week monday')">Last Week</a>
                <a class="dropdown-item" @click.prevent="$bvModal.show('modal-date-selector-widget')">Custom Date</a>
            </div>
        </div>
    </div>

    <ModalDateBetweenSelector
        :starting_date.sync="starting_date"
        :ending_date.sync="ending_date"
        @close="$bvModal.hide('modal-date-selector-widget')"
        @apply="validateFilter"
    />
</div>
</template>

<script>
import moment from "moment"
import url from "../../mixins/url";
import ModalDateBetweenSelector from "./ModalDateBetweenSelector.vue";

export default {
    components: {ModalDateBetweenSelector},
    mixins: [url],

    props: {
        dates: null
    },

    data(){
        return {
            formatted_starting_date: null,
            formatted_ending_date: null,
            starting_date: null,
            ending_date: null,
            between_dates: null
        }
    },
    mounted(){
        this.formatted_starting_date = moment(this.dates.starting_date).format('YYYY-MM-DD hh:mm')
        this.formatted_ending_date = moment(this.dates.ending_date).format('YYYY-MM-DD hh:mm')
        this.starting_date = moment(this.dates.starting_date).format('YYYY-MM-DD\Thh:mm')
        this.ending_date = moment(this.dates.ending_date).format('YYYY-MM-DD\Thh:mm')
        this.between_dates = this.dates.between_dates

        // this.applyFilter(this.between_dates)
        this.setUrlParameter(
            'filter[created_at_between]',
            this.getUrlParameter('filter[created_at_between]', '')
        );
    },
    computed: {
        dateRange(){
            return this.starting_date + "," + this.ending_date
        }
    },
    methods: {
        applyFilter(range){
            let param = this.dates['url_param_name'] ?? 'between_dates';
            this.setUrlParameterAngGo(param, range)
        },

        validateFilter(){
            if (this.starting_date > this.ending_date) {
                this.$snotify.warning('Invalid date filter.');
                return
            }
            this.applyFilter(this.dateRange)
        },
    }
}
</script>
