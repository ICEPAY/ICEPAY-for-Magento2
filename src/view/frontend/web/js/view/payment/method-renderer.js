define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],
function (
    Component,
    rendererList
) {
    'use strict';

    var component = 'Icepay_Payment/js/view/payment/method/default';
    rendererList.push({component: component, type: 'icepay_bancontact'});
    rendererList.push({component: component, type: 'icepay_creditcard'});
    rendererList.push({component: component, type: 'icepay_eps'});
    rendererList.push({component: component, type: 'icepay_ideal'});
    rendererList.push({component: component, type: 'icepay_onlineueberweisen'});
    rendererList.push({component: component, type: 'icepay_paypal'});
    rendererList.push({component: component, type: 'icepay_sofort'});

    return Component.extend({});
});
