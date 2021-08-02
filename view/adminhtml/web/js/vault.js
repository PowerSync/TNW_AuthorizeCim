define([
    'jquery',
    'uiComponent'
], function ($, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            $selector: null,
            selector: 'edit_form',
            $container: null
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            self.$container =  $('#' + self.container);
            self.$selector.on(
                'setVaultNotActive.' + self.getCode(),
                function () {
                    self.$selector.off('submitOrder.' + self.getCode());
                }
            );
            self._super();

            self.initEventHandlers();

            return self;
        },

        /**
         * Get payment code
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {
            this.$container.find('[name="payment[token_switcher]"]')
                .on('click', this.selectPaymentMethod.bind(this));
        },

        /**
         * Store payment details
         */
        selectPaymentMethod: function () {
            this.disableEventListeners();
            this.enableEventListeners();
            this.setPaymentDetails();
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.' + this.getCode(), this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
        },

        /**
         * Pre submit for order
         * @returns {Boolean}
         */
        submitOrder: function () {
            if (this.$selector.validate().errorList.length) {
                return false;
            }
            this.setPaymentDetails();
            this.placeOrder();
        },

        /**
         * Place order
         */
        placeOrder: function () {
            this.$selector.trigger('realOrder');
        },

        /**
         * Store payment details
         */
        setPaymentDetails: function () {
            this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
        },
    });
});
