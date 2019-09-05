define(
    [
        'Magento_Checkout/js/view/payment/default',
        "jquery",
        "mage/url",
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, $, url, fullScreenLoader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: "Pgc_Pgc/payment/creditcard"
            },

            initialize: function() {
                this._super();
                this.config = window.checkoutConfig.payment[this.getCode()];
            },

            afterPlaceOrder: function () {

                this.redirectAfterPlaceOrder = false;

                $.ajax({
                    url: url.build("pgc/payment/frontend"),
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
                        console.error("Error : " + JSON.stringify(err));
                    }
                });

            },

            initializeJsIntegration: function() {
                var payment = new PaymentJs("1.2");


                payment.init('public-integration-key', 'number_div', 'cvv_div', function(payment) {
                    // ...

                    payment.initRiskScript({type:'kount'});
                });

            },
        });
    }
);
