<template>

</template>

<script>
import Vue from "vue";
import helpers from "./helpers";
export default {
        name: "api",

        mixins: [helpers],

        created() {
            Vue.prototype.$currentUser = JSON.parse(document.querySelector("meta[name='current-user']").getAttribute('content'));
        },

        methods: {
            currentUser: function () {
                return Vue.prototype.$currentUser;
            },

            apiGet(url, params) {
                return axios.get(url, params)
            },

            apiPost(url, data) {
                return axios.post(url, data)
            },

            displayApiCallError: function (error) {
                console.log('API failed call response', error);

                if (error.response.status >= 500) {
                    this.notifyError(JSON.stringify(error.response.data));
                    return;
                }

                if (error.response.status >= 400) {
                    this.notifyError(JSON.stringify(error.response.data));
                    return;
                }

                this.notifyError('API call failed: ' + error.status + ' ' + error.data.message);
            },

            apiGetUserMe: function () {
                return axios.get('/api/settings/user/me');
            },

            apiGetActivityLog: function (params) {
                return axios.get('/api/activities', {params: params});
            },

            apiActivitiesPost: function (data) {
                return axios.post('/api/activities', data);
            },

            apiGetProducts: function(params) {
                return axios.get('/api/products', {params: params});
            },

            apiGetProductsPrices: function(params) {
                return axios.get('/api/products-prices', {params: params});
            },

            apiGetProductTags: function(params) {
                return axios.get('/api/product/tags', {params: params});
            },

            apiGetStocktakeSuggestions: function(params) {
                return this.apiGet('/api/stocktake-suggestions', {params: params});
            },

            apiGetStocktakeSuggestionsDetails: function(params) {
                return this.apiGet('/api/stocktake-suggestions-details', {params: params});
            },

            apiPostStocktakeSuggestionsConfiguration(data) {
                return axios.post('/api/modules/stocktake-suggestions/configuration', data);
            },

            apiGetStocktakeSuggestionsConfiguration(params) {
                return axios.get('/api/modules/stocktake-suggestions/configuration', params);
            },

            apiGetRestocking(params) {
                return axios.get('/api/restocking', {params: params});
            },

            apiInventoryPost(data) { return this.apiPost('/api/inventory', data)},
            apiInventoryGet(params) { return this.apiGet('/api/inventory', {params: params})},

            apiGetInventoryMovements(params) {
                return this.apiGet('/api/inventory-movements', {params: params});
            },

            apiPostStocktakes(data) {
                return axios.post('/api/stocktakes', data);
            },

            apiPostCsvImport(data) {
                return axios.post('/api/csv-import', data);
            },

            apiPostCsvImportDataCollections(data) {
                return axios.post('/api/csv-import/data-collections', data);
            },

            apiPostDataCollection(data) {
                return axios.post('/api/data-collector', data);
            },

            apiUpdateDataCollection(id, data) {
                return axios.put("/api/data-collector/" + id, data);
            },

            apiDataCollectorActionImportAsStocktake(data) {
                return axios.post("/api/data-collector-actions/import-as-stocktake", data);
            },

            apiDeleteDataCollection(id) {
                return axios.delete("/api/data-collector/" + id);
            },

            apiPostDataCollectorRecords(data) {
                return axios.post('/api/data-collector-records', data);
            },

            apiPostDataCollectorActionsAddProduct(data) {
                return axios.post('/api/data-collector-actions/add-product', data);
            },

            apiPostDataCollectionComment(data) {
                return axios.post('/api/data-collector/comments', data);
            },

             apiGetDataCollector(params) {
                return axios.get('/api/data-collector', {params: params});
            },

            apiGetDataCollectorRecords(params) {
                return axios.get('/api/data-collector-records', {params: params});
            },

            apiModuleEcommerceProductInfo: function(params) { return axios.get('/api/modules/api2cart/products', {params: params}) },

            apiGetOrders: function(params) {
                return axios.get('/api/orders', {params: params});
            },

            apiPostOrderCheckRequest: function (data) {
                return axios.post('/api/order-check-request', data);
            },

            apiUpdateOrder: function (order_id, data) {
                return axios.put('/api/orders/' + order_id, data);
            },

            apiGetOrderProducts: function(params) {
                return axios.get('/api/order/products', {params: params});
            },

            apiPostOrderProductShipment: function (data) {
                return axios.post('/api/orders/products/shipments', data);
            },

            apiUpdateOrderProduct: function (orderProduct_id, data) {
                return axios.put('/api/order/products/' + orderProduct_id, data);
            },

            apiGetOrderComments: function(params) {
                return axios.get('/api/order/comments', {params: params});
            },

            apiPostShipment: function(data) {
                return axios.post('/api/shipments', data);
            },

            apiGetOrderShipments: function(params) {
                return axios.get('/api/order/shipments', {params: params});
            },

            apiPostOrderShipment: function (data) {
                return axios.post('/api/order/shipments', data);
            },

            apiPostOrderComment: function (data) {
                return axios.post('/api/order/comments', data);
            },

            apiPostActivity: function (data) {
                return axios.post('/api/activities', data);
            },

            apiGetActivities: function (params) {
                return axios.get('/api/activities', {params: params});
            },

            apiPostUserMe: function (data) {
                return axios.post('/api/settings/user/me', data);
            },

            apiGetUser: function (userId) {
                return axios.get(`/api/admin/users/${userId}`);
            },

            apiGetUsers: function (params) {
                return axios.get(`/api/admin/users`, {params: params});
            },

            apiPostUserStore: function (data) {
                return axios.post(`/api/admin/users`, data);
            },

            apiPostUserUpdate: function (userId, data) {
                return axios.put(`/api/admin/users/${userId}`, data);
            },

            apiDeleteUser: function (id) {
                return axios.delete(`/api/admin/users/${id}`);
            },

            apiGetUserRoles: function (params) {
                return axios.get('/api/admin/user/roles', {params: params});
            },

            apiGetModulePrintnodeClients: function (params) { return axios.get('/api/modules/printnode/clients', {params: params}) },

            apiPostModulePrintnodeClients: function (data) { return axios.post('/api/modules/printnode/clients', data) },

            apiDeletePrintnodeClient: function (id) { return axios.delete(`/api/modules/printnode/clients/${id}`, {}) },

            apiGetApi2cartConnections: function (params) { return axios.get('/api/modules/api2cart/connections', {params: params}) },

            apiDeleteApi2cartConnection: function (connection_id) { return axios.delete(`/api/modules/api2cart/connections/${connection_id}`) },

            apiGetDpdConfiguration: function () { return axios.get(`/api/modules/dpd-ireland/connections`) },

            apiGetDpdUkConnections() {
                return axios.get(`/api/modules/dpd-uk/dpd-uk-connections`);
            },

            apiPostDpdUkConnection(data) {
                return axios.post(`/api/modules/dpd-uk/dpd-uk-connections`, data);
            },

            apiDeleteDpdUkConnection(id) {
                return axios.delete(`/api/modules/dpd-uk/dpd-uk-connections/${id}`);
            },

            apiPostDpdConfiguration: function (data) { return axios.post(`/api/modules/dpd-ireland/connections`, data) },

            apiDeleteDpdConfiguration: function (id) { return axios.delete(`/api/modules/dpd-ireland/connections/${id}`) },

            apiGetOauthTokens: function () {
                return axios.get('/oauth/tokens');
            },

            apiDeleteOauthToken: function (token) {
                return axios.delete('/oauth/tokens/' + token.id);
            },

            apiGetOauthClients: function () {
                return axios.get('/oauth/clients');
            },

            apiGetModuleAutoStatusPickingConfiguration: function () {
                return axios.get(`/api/modules/autostatus/picking/configuration`);
            },

            apiSetModuleAutoStatusPickingConfiguration: function (configuration) {
                return axios.post(`/api/modules/autostatus/picking/configuration`, configuration);
            },

            apiGetJobsRequest: function (params) {
                return axios.get('/api/jobs', {params: params});
            },

            apiPostJobsRequest: function (data) {
                return axios.post('/api/jobs', data);
            },

            apiGetPrintNodePrinters: function () {
                return axios.get('/api/modules/printnode/printers');
            },

            apiPostPrintnodePrintJob: function (data) { return axios.post('/api/modules/printnode/printjobs', data) },
            apiPostRmsapiConnections: function (data) { return axios.post('/api/modules/rms_api/connections', data) },
            apiGetRmsapiConnections: function (params) { return axios.get('/api/modules/rms_api/connections', {params: params}) },
            apiPostModulesSlackConfig: function (data) { return axios.post('/api/modules/slack/config', data) },
            apiGetModulesSlackIncomingWebhook: function (params) { return axios.get('/api/modules/slack/config', {params: params}) },
            apiDeleteRmsapiConnection: function (connection_id) { return axios.delete(`/api/modules/rms_api/connections/${connection_id}`) },
            apiPostApi2cartConnection: function (data) { return axios.post('/api/modules/api2cart/connections', data) },
            apiPostWidget: function (data) {
                return axios.post('/api/widgets', data);
            },

            apiPutWidget: function (widget_id, data) {
                return axios.put('/api/widgets/' + widget_id, data);
            },

            apiGetPacklistOrder: function (params) {
                return axios.get('/api/packlist/order', {params: params});
            },

            apiPrintLabel: function (orderNumber, template) {
                return axios.put(`/api/print/order/${orderNumber}/${template}`);
            },

            apiGetPicklist: function (params) {
                return axios.get('/api/picklist', {params: params});
            },

            apiPostPicklistPick: function (data) {
                return axios.post('/api/picklist/picks', data);
            },

            apiDeletePick: function (id) { return axios.delete('/api/picklist/picks/' + id)},

            apiGetModules: function () { return axios.get('/api/modules')},
            apiPostModule: function (id, params) { return axios.put('/api/modules/' + id, params)},


            apiGetShippingServices: function (params = {}) {
                return axios.get('/api/shipping-services', {params: params});
            },

            apiPostShippingLabel: function (params) {
                return axios.post('/api/shipping-labels', params);
            },

            apiGetOrderStatus: function (params) { return axios.get('/api/orders-statuses', {params: params})},
            apiPostOrderStatus: function (params) { return axios.post('/api/orders-statuses', params);},
            apiPutOrderStatus: function (id, params) { return axios.put('/api/orders-statuses/' + id, params)},
            apiDeleteOrderStatus: function (id) { return axios.delete('/api/settings/order-statuses/' + id)},

            apiGetMailTemplate: function () { return axios.get('/api/mail-templates')},
            apiPutMailTemplate: function (id, params) { return axios.put('/api/mail-templates/' + id, params)},

            apiGetNavigationMenu: function (params = {}) { return axios.get('/api/navigation-menu', {params: params})},
            apiPostNavigationMenu: function (params) { return axios.post('/api/navigation-menu/', params)},
            apiPutNavigationMenu: function (id, params) { return axios.put('/api/navigation-menu/' + id, params)},
            apiDeleteNavigationMenu: function (id) { return axios.delete('/api/navigation-menu/' + id)},

            apiGetAutomationConfig: function() { return axios.get('/api/modules/automations/config') },

            apiGetAutomations: function () { return axios.get('/api/modules/automations') },
            apiShowAutomations: function (id) { return axios.get('/api/modules/automations/' + id) },
            apiPostAutomations: function (params) { return axios.post('/api/modules/automations/', params) },
            apiPutAutomations: function (id, params) { return axios.put('/api/modules/automations/' + id, params) },
            apiDeleteAutomations: function (id) { return axios.delete('/api/modules/automations/' + id) },

            apiRunAutomation: function (id) {
                return axios.post('/api/settings/modules/automations/run', {
                    'automation_id': id
                });
            },

            apiGetWarehouses: function (params) {
                return axios.get('/api/warehouses', {params: params});
            },

            apiPostWarehouses: function (params) {
                return axios.post('/api/warehouses/', params);
            },

            apiPutWarehouses: function (id, params) {
                return axios.put('/api/warehouses/' + id, params);
            },

            apiDeleteWarehouses: function (id) {
                return axios.delete('/api/warehouses/' + id);
            },

            apiGetConfiguration: function () { return axios.get('/api/configurations/') },
            apiSaveConfiguration: function (params) { return axios.post('/api/configurations/', params) },

            apiGetHeartbeats: function () {
                return axios.get('/api/heartbeats/');
            },

            apiGetMagentoApiConnections: function (params) { return axios.get('/api/modules/magento-api/connections/', {params}) },
            apiPostMagentoApiConnection: function (params) { return axios.post(`/api/modules/magento-api/connections/`, params) },
            apiPutMagentoApiConnection: function (id, params) { return axios.put(`/api/modules/magento-api/connections/${id}`, params) },
            apiDeleteMagentoApiConnection: function (id) { return axios.delete(`/api/modules/magento-api/connections/${id}`) },

            apiPostMagento2msiConnection(params) {
                return axios.post(`/api/modules/magento2msi/connections/`, params)
            },

            apiGetMagento2msiConnections(params) {
                return axios.get('/api/modules/magento2msi/connections/', {params})
            },

            apiPutMagento2msiConnection(id, params) {
                return axios.put(`/api/modules/magento2msi/connections/${id}`, params)
            },

            apiGetActiveOrdersInventoryReservationsConfig() {
                return axios.get('/api/modules/active-orders-inventory-reservations/configuration')
            },

            apiPostActiveOrdersInventoryReservationsConfig: function (id, params) {
                return axios.put('/api/modules/active-orders-inventory-reservations/configuration/' + id, params)
            },
        }
    }
</script>

<style scoped>

</style>
