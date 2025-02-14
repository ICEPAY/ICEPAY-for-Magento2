define([
    'mage/url',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    urlBuilder,
    Component,
    fullScreenLoader
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Icepay_Payment/payment/method/default',
            redirectAfterPlaceOrder: false,
        },

        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            window.location.replace(urlBuilder.build('icepay/payment/redirect'));
        }
    });
})
