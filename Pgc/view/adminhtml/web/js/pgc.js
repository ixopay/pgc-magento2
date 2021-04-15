/*browser:true*/
/*global define*/
define([
  'jquery',
  'uiComponent',
  'Magento_Ui/js/modal/alert',
  'Magento_Ui/js/lib/view/utils/dom-observer',
  'mage/translate',
  'Pgc_Pgc/js/validator'
], function ($, Class, alert, domObserver, $t, validator) {
  'use strict';

  return Class.extend({

    defaults: {
      $selector: null,
      selector: 'edit_form',
      container: 'payment_form_pgc_creditcard',
      active: false,
      scriptLoaded: false,
      pgc: null,
      token: null,
      paymentJs: null,
      paymentJsUrl:null,
      selectedCardType: null,
      imports: {
        onActiveChange: 'active'
      }
    },

    /**
     * Set list of observable attributes
     * @returns {exports.initObservable}
     */
    initObservable: function () {
      var self = this;

      validator.setConfig(this);

      self.$selector = $('#' + self.selector);
      this._super()
        .observe([
          'active',
          'scriptLoaded',
          'selectedCardType'
        ]);

      // re-init payment method events
      self.$selector.off('changePaymentMethod.' + this.code)
        .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

      // listen block changes
      domObserver.get('#' + self.container, function () {
        if (self.scriptLoaded()) {
          self.$selector.off('submit');          
          self.initPgcJs();
        }
      });

      return this;
    },

    /**
     * Enable/disable current payment method
     * @param {Object} event
     * @param {String} method
     * @returns {exports.changePaymentMethod}
     */
    changePaymentMethod: function (event, method) {
      this.active(method === this.code);
      return this;
    },

    /**
     * Triggered when payment changed
     * @param {Boolean} isActive
     */
    onActiveChange:function (isActive) {
      if (!isActive) {
        this.$selector.off('submitOrder.pgc_creditcard');
        return;
      }

      this.disableEventListeners();
      window.order.addExcludedPaymentMethod(this.code);

      if (!this.publicTokenKey) {
        this.error($.mage.__('This payment is not available'));
        return;
      }

      this.enableEventListeners();

      if (!this.scriptLoaded()) {
         this.loadScript();
      }
    },

    loadPaymentJs:function(src) {
      return new Promise(function(resolve, reject) {
       let script = document.createElement('script'); 
       script.src = src;
       script.type = 'text/javascript';
       script.setAttribute('data-main', 'payment-js');
       script.onload = () => resolve(script); 
       script.onerror = () => reject(new Error("Script load error for")); 
       document.head.append(script); }); 
     },
     
     /**
     * Load external PAYMENT JS
     */
    loadScript: function () {
      var self = this;
      var state = self.scriptLoaded;
      $('body').trigger('processStart');
      state(true);
       self.initPgcJs();
      $('body').trigger('processStop');
    },

    initPgcJs:function(){
      var self=this;
      var jsurl = this.paymentJsUrl+"js/integrated/payment.1.2.min.js";
      console.log(jsurl);
      if(this.paymentJs==null){
        let promise = this.loadPaymentJs(jsurl);
        promise.then( script => {
          this.paymentJs=new PaymentJs('1.2');
          self.initPgc();
        }, error => alert("Error while initializing the payment js") ); 
      }
      else{
        self.initPgc();
      }
    },

    /**
     * Create and mount card
     */
    initPgc: function () {
      $(".pgc-error").hide();   
      this.paymentJs.init(this.publicTokenKey, 'number_div', 'cvv_div', function(payment) {
        var numberFocused = false;
        var cvvFocused = false;
        var style = {
          'border': 'none',
          'padding': '0',
          'height': '100%',
          'font-size':'14px',
        };
        var hoverStyle = {
          'border': 'none',
        };
        var focusStyle = {
          'border': 'none',
          'outline': 'none',
          'box-shadow': 'none',
        };

        // Set the initial style
        payment.setNumberStyle(style);
        payment.setCvvStyle(style);

        // Focus events
        payment.numberOn('focus', function() {
          numberFocused = true;
          payment.setNumberStyle(focusStyle);
        });
        payment.cvvOn('focus', function() {
          cvvFocused = true;
          payment.setCvvStyle(focusStyle);
        });

        // Blur events
        payment.numberOn('blur', function() {
          numberFocused = false;
          payment.setNumberStyle(style);
        });
        payment.cvvOn('blur', function() {
          cvvFocused = false;
          payment.setCvvStyle(style);
        });

        // Hover events
        payment.numberOn('mouseover', function() {
          // Don't override style if element is already focused
          if(! numberFocused) {
            payment.setNumberStyle(hoverStyle);
          }
        });
        payment.numberOn('mouseout', function() {
          // Don't override style if element is already focused
          if(! numberFocused) {
            payment.setNumberStyle(style);
          }
        });
        payment.cvvOn('mouseover', function() {
          // Don't override style if element is already focused
          if(! cvvFocused) {
            payment.setCvvStyle(hoverStyle);
          }
        });
        payment.cvvOn('mouseout', function() {
          // Don't override style if element is already focused
          if(! cvvFocused) {
            payment.setCvvStyle(style);
          }
        });
    });
    },

    /**
     * Show alert message
     * @param {String} message
     */
    error: function (message) {
      alert({
        content: message
      });
    },

    /**
     * Enable form event listeners
     */
    enableEventListeners: function () {
      this.$selector.on('submitOrder.pgc_creditcard', this.submitOrder.bind(this));
    },

    /**
     * Disable form event listeners
     */
    disableEventListeners: function () {
      this.$selector.off('submitOrder');
      this.$selector.off('submit');
    },

    /**
     * Trigger order submit
     */
    submitOrder: function () {
      var self = this;
      this.$selector.validate().form();
      this.$selector.trigger('afterValidate.beforeSubmit');
      $('body').trigger('processStart');

      // validate parent form
      if (this.$selector.validate().errorList.length) {
        return false;
      }

      $.when(this.createToken()).done(function () {
        $('body').trigger('processStop');
        if (self.validateCardType()) {
          self.placeOrder();
        }
      }).fail(function (result) {
        $('body').trigger('processStop');
        self.error(result);

        return false;
      });
    },

    /**
     * Convert card information to pgc token
     */
    createToken: function () {
      var data = {
        card_holder: $('#card_holder').val(),
        month: $('#exp_month').val(),
        year: $('#exp_year').val()
    };
    var defer = $.Deferred();
    this.paymentJs.tokenize(
        data, //additional data, MUST include card_holder (or first_name & last_name), month and year
        function(token, cardData) {
            $('#pgc_creditcard_transactionToken').val(token);
            defer.resolve();
        }, 
        function(errors) { //error callback function
          console.log(errors);
          if(errors.length){
            $(errors).each(function( index ) {
              //debugger;              
              $("#"+errors[index].attribute+"-error").text(errors[index].message);
              $("#"+errors[index].attribute+"-error").show();
              console.log(errors[index].attribute);
            }); 
          }
          defer.reject('error while tokenize the data');            
        }
    );
    return defer.promise();

    },

    /**
     * Place order
     */
    placeOrder: function () {
      $('#' + this.selector).trigger('realOrder');
    },

    /**
     * Get list of currently available card types
     * @returns {Array}
     */
    getCcAvailableTypes: function () {
      var types = [],
        $options = $(this.getSelector('cc_type')).find('option');

      $.map($options, function (option) {
        types.push($(option).val());
      });

      return types;
    },

    /**
     * Validate current entered card type
     * @returns {Boolean}
     */
    validateCardType: function () {
      
      return true;
    },

    /**
     * Get jQuery selector
     * @param {String} field
     * @returns {String}
     */
    getSelector: function (field) {
      return '#' + this.code + '_' + field;
    }

  });
});