define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],
function (
    Component,
    rendererList
) {
    'use strict';

    rendererList.push({
        type: 'icepay_creditcard',
        component: 'Icepay_Payment/js/view/payment/method/default'
    });

    return Component.extend({});
});
