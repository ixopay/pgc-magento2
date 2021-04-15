define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader'
], function (VaultComponent, $, url, fullScreenLoader) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Magento_Vault/payment/form'
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details['maskedCC'];
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details['expirationDate'];
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details['type'];
        },

        /**
         * Get ThreeDSecure info
         * @returns {*}
         */
        isThreeDSecure: function () {
            return window.checkoutConfig.payment['pgc_creditcard'].three_d_secure;
        },

        /**
         * Redirect to hpp
         */
        afterPlaceOrder: function () {
            if ((this.isThreeDSecure() === 'OPTIONAL') || (this.isThreeDSecure() === 'MANDATORY')) {
                fullScreenLoader.startLoader();
                this.isPlaceOrderActionAllowed(false);
                this.redirectAfterPlaceOrder = false;
                $.ajax({
                    url: url.build('pgc/payment/frontend'),
                    type: 'post',
                    success: (result) => {
                        console.log(result);
                        if (result.type === 'finished') {
                            this.redirectAfterPlaceOrder = true;
                        } else if (result.type === 'redirect') {
                            fullScreenLoader.startLoader();
                            window.location.replace(result.url);
                        }
                    },
                    error: (err) => {
                        console.error('Error : ' + JSON.stringify(err));
                    }
                });
            }
        }
    });
});
