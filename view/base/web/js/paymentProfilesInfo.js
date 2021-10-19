/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'jquery/ui',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function ($) {
    'use strict';

    $.widget('mage.paymentProfilesInfo', {
        options: {
            formSelector: '.customer-payment-profile',
            deleteActionSelector: '.action-delete',
            saveActionSelector: '.action-save'
        },

        /**
         * Create widget
         * @private
         */
        _create: function () {
            this._bind();
            this._initWrapper();
            this.element.trigger('contentUpdated');
        },

        /**
         * Remove unneeded css class
         * @private
         */
        _initWrapper: function() {
            this.element.parent().removeClass('admin__scope-old');
        },

        /**
         * Event binding, will monitor change, keyup and paste events.
         * @private
         */
        _bind: function () {
            $(this.options.deleteActionSelector, this.element).on('click', $.proxy(this._deleteProfile, this));
            $(this.options.formSelector, this.element).on('realSubmit', $.proxy(this._realSave, this));
            $(this.options.formSelector, this.element).on('submit', $.proxy(this._saveProfile, this)).validation();
        },

        /**
         * @param event
         * @private
         */
        _deleteProfile: function (event) {
            event.preventDefault();
            var form = $(event.target).parent();

            if (confirm($.mage.__("Are you sure you want to delete this?"))) {
                $.post(
                    $(form).attr('action'), $(form).serialize(), function (event, data) {
                        if(data.success) {
                            $(event.target).closest('li').slideUp();
                        } else if (typeof data.message != 'undefined') {
                            alert(data.message);
                        }
                    }.bind(this, event)
                );
            }
        },

        /**
         * @param event
         * @private
         */
        _saveProfile: function (event) {
            event.preventDefault();

            var form = this.element.find(this.options.formSelector);
            form.data("validator").settings.submitHandler = null;
            if (form.validation() && form.validation('isValid') === false) {
                return;
            }
            this.element.find(this.options.saveActionSelector).prop('disabled', true);
            form.data('preventSave', false);
            form.trigger('authorizeCimSave');
        },

        /**
         * @param event
         * @private
         */
        _realSave: function (event) {
            event.preventDefault();

            var form = this.element.find(this.options.formSelector);
            if (form.data('preventSave') === false) {
                $.post(
                    form.attr('action'), form.serialize(), function (data) {
                        if(typeof data.message != 'undefined') {
                            this.element.find(this.options.saveActionSelector).removeProp('disabled');
                            alert(data.message);
                        } else if (typeof data.url != 'undefined') {
                            window.location.href = data.url;
                        } else {
                            this.element.find(this.options.saveActionSelector).removeProp('disabled');
                            this.element.html(
                                $($.parseHTML(data.trim(), document, true)).html()
                            );
                            this._create();
                            $('html, body').animate({
                                scrollTop: 0
                            }, 500);
                            this.element.trigger('contentUpdated');
                        }
                    }.bind(this)
                );
            } else {
                this.element.find(this.options.saveActionSelector).removeProp('disabled');
            }
        }
    });

    return $.mage.paymentProfilesInfo;
});
