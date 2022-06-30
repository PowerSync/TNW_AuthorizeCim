/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function ($, Class, alert, domObserver) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'tnw_authorize_cim-form-validate',
            container: 'payment_form_tnw_authorize_cim',
            active: true,
            scriptLoaded: false,
            accept: null,
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

            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded'
                ]);

            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                return;
            }
            this.disableEventListeners();

            if (!this.apiLoginID || !this.clientKey) {
                this.error($.mage.__('This payment is not available'))
                return;
            }

            this.enableEventListeners();

            if (!this.scriptLoaded()) {
                this.loadScript();
            }
        },

        /**
         * Load external Authorize SDK
         */
        loadScript: function () {
            var self = this,
                state = self.scriptLoaded;

            $('body').trigger('processStart');
            require([this.sdkUrl], function () {
                state(true);
                self.accept = window.Accept;
                $('body').trigger('processStop');
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
            this.$selector.on('authorizeCimSave', this.submitPayment.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('authorizeCimSave');
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setOpaqueDescriptor: function (nonce) {
            var $container = $('#' + this.container);
            $container.find('[name="payment[opaqueDescriptor]"]').val(nonce);
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setOpaqueValue: function (nonce) {
            var $container = $('#' + this.container);
            $container.find('[name="payment[opaqueValue]"]').val(nonce);
        },

        /**
         * Trigger order submit
         */
        submitPayment: function () {
            this.$selector.validate().form();
            $('body').trigger('processStop');

            if (this.$selector.validate().errorList.length) {
                return false;
            }

            var self = this,
                paymentData = {
                cardData: {
                    cardNumber: $(this.getSelector('cc_number')).val().replace(/\D/g, ''),
                    month: $(this.getSelector('expiration')).val(),
                    year: $(this.getSelector('expiration_yr')).val(),
                    cardCode: $(this.getSelector('cc_cid')).val()
                },
                authData: {
                    clientKey: this.clientKey,
                    apiLoginID: this.apiLoginID
                }
            };

            this.accept.dispatchData(paymentData, function (response) {
                if (response.messages.resultCode === "Error") {
                    var i = 0;
                    while (i < response.messages.message.length) {
                        self.error(response.messages.message[i].code + ": " + response.messages.message[i].text);
                        i = i + 1;
                    }
                    self.$selector.data('preventSave', true);
                } else {
                    self.$selector.data('preventSave', false);
                    self.setOpaqueDescriptor(response.opaqueData.dataDescriptor);
                    self.setOpaqueValue(response.opaqueData.dataValue);
                    self.realSubmit();
                }
            });
        },

        realSubmit: function () {
            this.$selector.trigger('realSubmit');
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
