<template>
    <div>
        <div class="text-primary h5">{{ product ? product['name'] : '&nbsp;' }}</div>
        <div>
            sku: <font-awesome-icon icon="copy" class="fa-xs btn-link" role="button" @click="copyToClipBoard((product ? product['sku'] : '') )"></font-awesome-icon><b>&nbsp;<a :href="'/products?filter[sku]=' + (product ? product['sku'] : '') " class="font-weight-bold">{{ (product ? product['sku'] : '&nbsp;') }}</a></b><br>
        </div>
        <div v-if="product">
            <template v-for="tag in product['tags']">
                <a class="badge text-uppercase btn btn-outline-primary" :key="tag.id" @click.prevent="setUrlParameterAngGo('filter[product_has_tags]', getTagName(tag))"> {{ getTagName(tag) }} </a>
            </template>
        </div>
    </div>
</template>

<script>
    import helpers from "../../mixins/helpers";
    import url from "../../mixins/url";

    export default {
        mixins: [helpers, url],

        name: "ProductInfoCard",

        props: {
            product: null,
        },

        methods: {
            getTagName(tag) {
                return tag.name instanceof Object ? tag.name['en'] : tag.name
            },

            setUrlParameterAngGo: function(param, value) {
                this.setUrlParameter(param, value);
                window.location.reload();
                return this;
            },
        }
    }
</script>

<style scoped>

</style>
