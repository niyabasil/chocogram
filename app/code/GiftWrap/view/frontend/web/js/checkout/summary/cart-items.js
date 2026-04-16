/**
 * Default Cart items logic
 */

define([
    'ko',
    'Magento_Checkout/js/model/totals',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
    'amwrapSummaryUpdate'
], function (ko, totals, Component, stepNavigator, quote, amwrapSummaryUpdate) {
    'use strict';

    var wrapConfig = window.checkoutConfig.amGiftWrap;
    quote.totals.subscribe(function () {
        amwrapSummaryUpdate.updateData(false);
    });

    return Component.extend({
        defaults: {
            template: 'Amasty_GiftWrap/checkout/cart-items'
        },
        totals: totals.totals(),
        items: ko.observable([]),
        itemsCount: ko.observable(wrapConfig.itemsCount),
        maxCartItemsToDisplay: window.checkoutConfig.maxCartItemsToDisplay,
        cartUrl: window.checkoutConfig.cartUrl,

        /**
         * Returns wrap items
         *
         * @returns {Array}
         */
        getItems: function () {
            return this.items;
        },

        /**
         * Returns bool value for items to check if avaliable
         *
         * @returns {Boolean}
         */
        getIsAvailable: function () {
            return wrapConfig.giftWrappingAvailable && !quote.isVirtual();
        },

        /**
         * Returns cart items qty
         *
         * @returns {Number}
         */
        getItemsQty: function () {
            return wrapConfig.wrapItemsCount;
        },

        /**
         * Returns count of cart line items
         *
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            return parseInt(wrapConfig.itemsCount, 10);
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            // Set initial items to observable field
            this.setItems(JSON.parse(wrapConfig.wrapItemsJson));
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items) {
            if (items && items.length > 0) {
                items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
            }
            this.items(items);
        },

        /**
         * Set items count to observable field
         *
         * @param {Object} items
         */
        setItemsCount: function (items) {
            this.itemsCount(items);
        },

        /**
         * Returns bool value for items block state (expanded or not)
         *
         * @returns {Boolean}
         */
        isItemsBlockExpanded: function () {
            return quote.isVirtual() || stepNavigator.isProcessed('shipping');
        }
    });
});
