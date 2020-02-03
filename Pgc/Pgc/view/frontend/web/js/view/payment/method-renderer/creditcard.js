define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/url',
        'mage/translate',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/validation'
    ],
    function (Component, $, url, $t, fullScreenLoader) {
        'use strict';
        return Component.extend({
            paymentJs: null,
            transactionToken: null,
            ccHolder: '',
            ccMonth: '',
            ccYear: '',

            defaults: {
                template: 'Pgc_Pgc/payment/creditcard',
            },

            initialize: function() {
                this._super();
                this.config = window.checkoutConfig.payment[this.getCode()];
            },

            placeOrder: function (data, event) {

                if (this.isSeamless()) {

                    var form = $('#pgc_form_' + this.getCode());

                    if (!form.validation() || !form.validation('isValid')) {
                        return;
                    }

                    var validator = form.validate();

                    const paymentJsData = {
                        card_holder: this.ccHolder,
                        month: this.ccMonth,
                        year: this.ccYear,
                    };

                    this.isPlaceOrderActionAllowed(false);

                    var localSuper = this._super;

                    this.paymentJs.tokenize(
                        paymentJsData,
                        (token, cardData) => {
                            this.transactionToken = token;
                            this._super = localSuper;
                            this._super(data, event);
                            this._super = null;
                            return true;
                        },
                        (errors) => {
                            errors.forEach(message => {
                                validator.showLabel($('#pgc_cc_' + message.attribute + '_' + this.getCode()).get(0), message.message);
                            });

                            validator.showErrors();

                            this.isPlaceOrderActionAllowed(true);
                            return false;
                        }
                    )
                } else {
                    this._super(data, event);
                }
            },

            afterPlaceOrder: function () {

                this.redirectAfterPlaceOrder = false;

                const data = {
                    token: this.transactionToken
                };

                $.ajax({
                    url: url.build('pgc/payment/frontend'),
                    type: 'post',
                    data: data,
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

            },

            isSeamless: function() {
                return this.config.seamless;
            },

            initializeJsIntegration: function() {
                this.paymentJs = new PaymentJs('1.2');

                this.paymentJs.init(this.config.integration_key, 'pgc_cc_number_' + this.getCode(), 'pgc_cc_cvv_' + this.getCode(), function(payment) {
                    var style = {
                        'border': '1px solid #c2c2c2',
                        'outline': 'none',

                        'padding': '0 9px',
                        'font-size': '14px',
                        'width': 'calc(100% - 6px)',
                        'height': '32px'
                    };

                    payment.setNumberStyle(style);
                    payment.setCvvStyle(style);

                    payment.initRiskScript({type:'kount'});
                });
            },
        });
    }
);
