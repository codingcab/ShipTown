<template>
    <div class="row mb-2 pl-1 pr-1" v-if="getUrlParameter('hide_nav_bar', false) === false">
        <div class="flex-fill">
            <slot/>
        </div>
        <button v-b-modal="vbModal" type="button" class="btn btn-primary ml-2" data-toggle="modal" :data-target="dataTarget">
            <font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon>
        </button>
    </div>
</template>

<script>

import url from "../../mixins/url";
export default {
    mixins: [url],
    props: {
        vbModal: {
            type: String,
            default: 'quick-actions-modal'
        },
        dataTarget: {
            type: String,
            default: '#filterConfigurationModal'
        }
    }

}
</script>


