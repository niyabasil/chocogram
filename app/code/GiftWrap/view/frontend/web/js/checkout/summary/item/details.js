/**
 * Children details logic
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_GiftWrap/checkout/item/details'
        },

        /**
         * @param {Object} quoteItem Name
         * @return {String}
         */
        getName: function (quoteItem) {
            return quoteItem.name;
        },

        /**
         * @param {Object} giftWrap Price
         * @return {String}
         */
        getPrice: function (giftWrap) {
            return giftWrap['price'];
        },

        /**
         * @param {Object} giftWrap Card name
         * @return {String}
         */
        getCard: function (giftWrap) {
            return giftWrap['card'];
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getCardSrc: function (item) {
            if (item['card_image']) {
                return item['card_image'];
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getCardAlt: function (item) {
            if (item['card']) {
                return item['card'];
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function (item) {
            if (item['image']) {
                return item['image'];
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function (item) {
            if (item['name']) {
                return item['name'];
            }

            return null;
        }
    });
});
