/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */


require('./bootstrap');

require('./registerServiceWorker');
window.Vue = require('vue').default;

// Vue 2
import Vue from 'vue'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { BootstrapVue } from 'bootstrap-vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCog,
    faQuestionCircle,
    faUsers,
    faUserEdit,
    faBarcode,
    faUserMinus,
    faShareAlt,
    faChevronDown,
    faChevronUp,
    faExternalLinkAlt,
    faPrint,
    faCode,
    faTruck,
    faShoppingCart,
    faKey,
    faDesktop,
    faClipboardList,
    faPuzzlePiece,
    faBoxOpen,
    faEdit,
    faMinus,
    faPlus,
    faCheckCircle,
    faTimesCircle,
    faEnvelopeOpenText,
    faListUl,
    faTrash,
    faMagic,
    faWarehouse,
    faArchive,
    faCopy,
    faCartPlus, faAtom, faChartBar, faChartLine, faBox, faBars, faArrowRight,
    faFileDownload
} from '@fortawesome/free-solid-svg-icons';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import VueTippy, { TippyComponent } from "vue-tippy";
import Snotify from 'vue-snotify';
import VueCountdownTimer from 'vuejs-countdown-timer';

library.add(faBars);
library.add(faCog);
library.add(faQuestionCircle);
library.add(faBoxOpen);
library.add(faBox);
library.add(faUsers);
library.add(faUserEdit);
library.add(faUserMinus);
library.add(faBarcode);
library.add(faShareAlt);
library.add(faChevronDown);
library.add(faChevronUp);
library.add(faExternalLinkAlt);
library.add(faPrint);
library.add(faCode);
library.add(faTruck);
library.add(faShoppingCart);
library.add(faKey);
library.add(faDesktop);
library.add(faClipboardList);
library.add(faChartBar);
library.add(faChartLine);
library.add(faPuzzlePiece);
library.add(faBoxOpen);
library.add(faEdit);
library.add(faMinus);
library.add(faPlus);
library.add(faCheckCircle);
library.add(faTimesCircle);
library.add(faEnvelopeOpenText);
library.add(faListUl);
library.add(faTrash);
library.add(faMagic);
library.add(faWarehouse);
library.add(faArchive);
library.add(faCopy);
library.add(faCartPlus);
library.add(faArrowRight);
library.add(faFileDownload);

Vue.config.productionTip = false;

Vue.use(VueCountdownTimer);

Vue.use(Loading);
Vue.use(require('vue-moment'));

// Install BootstrapVue
Vue.use(BootstrapVue);
Vue.use(VueTippy);
Vue.use(Snotify, {
    global: {
        newOnTop: false,
    },
    toast: {
        position: "centerBottom",
        icon: false,
        showProgressBar: false,
        timeout: 1000,
    }
});

// import our plugin
import Modals from './plugins/Modals.js'

// use it
Vue.use(Modals);

/**
 * The following block of code may be used to automatically register your
 * Vue mixins. It will recursively scan this directory for the Vue
 * mixins and automatically register them with their "basename".
 *
 * Eg. ./mixins/ExampleComponent.vue -> <example-mixins></example-mixins>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.mixins(key.split('/').pop().split('.')[0], files(key).default));

/**
 * Third Party mixins
 */
Vue.component('font-awesome-icon', FontAwesomeIcon);

/**
 * Application mixins
 */
Vue.component('recent-inventory-movements-modal', require('./modals/RecentInventoryMovementsModal.vue').default);
Vue.component('product-info-modal', require('./modals/ProductInfoModal.vue').default);
Vue.component('stocktake-suggestions-page', require('./components/Settings/Modules/StocktakeSuggestionsPage.vue').default);
Vue.component('activity-log-page', require('./components/ActivityLogPage.vue').default);
Vue.component('admin-modules-slack-config-page', require('./components/admin/modules/slack/config-page.vue').default);
Vue.component('api', require('./mixins/api.vue').default);
Vue.component('api2cart-configuration', require('./components/Settings/Api2cartConnections.vue').default);
Vue.component('apt-configuration-modal', require('./components/Widgets/APT/ConfigurationModal.vue').default);
Vue.component('auto-pilot-tuning-section', require('./components/Settings/AutoPilotTuningSection.vue').default);
Vue.component('automation-table', require('./components/Settings/AutomationTable.vue').default);
Vue.component('autopilot-packlist-page', require('./components/AutopilotPacklistPage.vue').default);
Vue.component('barcode-input-field', require('./components/SharedComponents/BarcodeInputField.vue').default);
Vue.component('configuration-section', require('./components/Settings/ConfigurationSection.vue').default);
Vue.component('data-collector-list-page', require('./components/DataCollectorListPage.vue').default);
Vue.component('data-collector-page', require('./components/DataCollectorPage.vue').default);
Vue.component('data-collector-quantity-request-modal', require('./components/DataCollectionPage/DataCollectorQuantityRequestModal.vue').default);
Vue.component('date-selector-widget', require('./components/Widgets/DateSelectorWidget.vue').default);
Vue.component('dpd-configuration', require('./components/Settings/DpdConfiguration.vue').default);
Vue.component('dpd-uk-configuration', require('./components/Settings/DpdUkConfiguration.vue').default);
Vue.component('heartbeats', require('./components/Heartbeats.vue').default);
Vue.component('inventory-movements-report-page', require('./components/InventoryMovementsReportPage.vue').default);
Vue.component('active-orders-inventory-reservations-page', require('./components/Settings/Modules/ActiveOrdersInventoryReservationsConfigurationPage.vue').default);
Vue.component('magento-api-configuration-page', require('./components/Settings/MagentoApiConfigurationPage.vue').default);
Vue.component('magento2msi-configuration-page', require('./components/Settings/Modules/Magento2msiConfigurationPage.vue').default);
Vue.component('mail-template-table', require('./components/Settings/MailTemplateTable.vue').default);
Vue.component('maintenance-section', require('./components/Settings/MaintenanceSection.vue').default);
Vue.component('module-configuration', require('./components/Settings/ModuleConfiguration.vue').default);
Vue.component('navigation-menu-table', require('./components/Settings/NavigationMenuTable.vue').default);
Vue.component('number-card', require('./components/SharedComponents/NumberCard.vue').default);
Vue.component('order-status-table', require('./components/Settings/OrderStatusPage.vue').default);
Vue.component('orders-table', require('./components/OrdersPage.vue').default);
Vue.component('packlist-configuration-modal', require('./components/Packlist/FiltersModal.vue').default);
Vue.component('packlist-table-entry', require('./components/Packlist/PacklistEntry.vue').default);
Vue.component('packsheet-page', require('./components/PacksheetPage.vue').default);
Vue.component('passport-authorized-clients', require('./components/Settings/AuthorizedClients.vue').default);
Vue.component('passport-clients', require('./components/Settings/OauthClients.vue').default);
Vue.component('passport-personal-access-tokens', require('./components/Settings/PersonalAccessTokens.vue').default);
Vue.component('picks-table', require('./components/PicklistPage.vue').default);
Vue.component('printer-configuration', require('./components/Settings/PrintersConfiguration.vue').default);
Vue.component('printnode-configuration', require('./components/Settings/PrintNode.vue').default);
Vue.component('product-count-request-input-field', require('./components/SharedComponents/ProductCountRequestInputField.vue').default);
Vue.component('product-info-card', require('./components/SharedComponents/ProductInfoCard.vue').default);
Vue.component('products-table', require('./components/ProductsPage.vue').default);
Vue.component('quick-connect-magento-page', require('./components/QuickConnectMagentoPage.vue').default);
Vue.component('restocking-page', require('./components/RestockingPage.vue').default);
Vue.component('restocking-record-card', require('./components/RestockingPage/RestockingRecordCard.vue').default);
Vue.component('rmsapi-configuration', require('./components/Settings/RmsapiiConfiguration.vue').default);
Vue.component('stocktake-input', require('./components/SharedComponents/StocktakeInput.vue').default);
Vue.component('stocktaking-page', require('./components/StocktakingPage.vue').default);
Vue.component('swiping-card', require('./components/SharedComponents/SwipingCard.vue').default);
Vue.component('text-card', require('./components/SharedComponents/TextCard.vue').default);
Vue.component('user-courier-integration-select', require('./components/Settings/UsersCourierIntegrationSelect.vue').default);
Vue.component('user-table', require('./components/UsersPage.vue').default);
Vue.component('warehouses-page', require('./components/Settings/WarehousesPage.vue').default);
Vue.component('webhooks-subscription-page', require('../../app/Modules/Webhooks/resources/js/WebhooksSubscriptionsPage.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding mixins to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
