define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/url',
        'mage/translate',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/validation',
        'Magento_Vault/js/view/payment/vault-enabler'
    ],
    function (Component, $, url, $t, fullScreenLoader, validation, VaultEnabler) {
        'use strict';
        return Component.extend({

            defaults: {
                paymentJs: null,
                transactionToken: null,
                ccHolder: '',
                ccMonth: '',
                ccYear: '',
                template: 'Pgc_Pgc/payment/creditcard',
            },

            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                this.config = window.checkoutConfig.payment[this.getCode()];
                return this;
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'transactionToken': this.transactionToken
                    }
                };
                this.vaultEnabler.visitAdditionalData(data);
                return data;
            },

            placeOrder: async function (data, event) {
                if (this.isSeamless()) {
                    const _super = this._super;
                    var form = $('#pgc_form_' + this.getCode());
                    if (!form.validation() || !form.validation('isValid')) {
                        return;
                    }
                    var validator = form.validate();
                    try {
                        fullScreenLoader.startLoader();
                        this.isPlaceOrderActionAllowed(false);
                        var result = await this.getTransactionToken()
                        this.transactionToken = result;
                        this.isPlaceOrderActionAllowed(true);
                        _super.call(data, event);
                        fullScreenLoader.stopLoader();
                    } catch (error) {
                        fullScreenLoader.stopLoader();
                        error.forEach(message => {
                            validator.showLabel($('#pgc_cc_' + message.attribute + '_' + this.getCode()).get(0), message.message);
                        });
                        validator.showErrors();
                        this.isPlaceOrderActionAllowed(true);
                    }
                } else {
                    this._super(data, event);
                }
            },

            getTransactionToken: async function () {
                const paymentJsData = {
                    card_holder: this.ccHolder,
                    month: this.ccMonth,
                    year: this.ccYear,
                };
                return new Promise(((resolve, reject) => {
                    this.paymentJs.tokenize(
                        paymentJsData,
                        function (token, cardData) {
                            resolve(token)
                        },
                        function (errors) {
                            reject(errors)
                        })
                }))
            },

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
            },

            isSeamless: function () {
                return this.config.seamless;
            },

            isThreeDSecure: function () {
                return this.config.three_d_secure;
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            initializeJsIntegration: function () {
                this.paymentJs = new PaymentJs('1.2');
                this.paymentJs.init(this.config.integration_key, 'pgc_cc_number_' + this.getCode(), 'pgc_cc_cvv_' + this.getCode(), function (payment) {
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
                    //payment.initRiskScript({type: 'kount'});
                });
            },
        });
    }
);
