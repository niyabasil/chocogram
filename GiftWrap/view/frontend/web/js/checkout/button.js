/**
 * Default Gift button logic
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    var wrapConfig = window.checkoutConfig.amGiftWrap;

    return Component.extend({
        defaults: {
            template: 'Amasty_GiftWrap/checkout/button',
            status: ko.observable(wrapConfig.giftWrappingCheckoutButtonAvailable),
            isVisible: ko.observable(false)
        },

        options: {
            openTriggerSelector: '[data-amwrap-js="gift-button"]',
            popupSelector: '[data-amwrap-js="popup-block"]'
        },

        /**
         * Check gift wrapping option availability.
         *
         * @returns {Boolean}
         */
        getIsAvailable: function () {
            return wrapConfig.giftWrappingAvailable;
        },

        /**
         * Check gift wrapping button availability.
         *
         * @returns {Boolean}
         */
        getIsButtonEnabled: function () {
            return wrapConfig.giftWrappingCheckoutButtonAvailable;
        },

        /**
         * Init js after Component is rendered.
         *
         * @returns {void}
         */
        afterKoRender: function () {
            this._showButton();
            this._initPopup();
        },

        /**
         * Set disable/enable gift button
         *
         * @param {Boolean} data
         * @returns {void}
         */
        buttonStatus: function (data) {
            this.status(data);
        },

        /**
         * Check if amwrapCart component is loaded, then show the button
         *
         * @returns {void}
         */
        _showButton: function () {
            var popup = $(this.options.popupSelector);

            if (typeof popup.amwrapCart != 'undefined') {
                this.isVisible(true);
            }
        },

        /**
         * Init click on button to show the popup.
         *
         * @returns {void}
         */
        _initPopup: function () {
            var options = this.options,
                popup = $(options.popupSelector);

            $(options.openTriggerSelector).on('click', function () {
                popup.amwrapPopup('open');
                popup.amwrapCart('showOptionList');
            });
        }
    });
});
