/**
 * Default Magento Summary override
 */

define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'mage/translate'
    ],
    function (Component, quote, totals, $t) {
        'use strict';

        var wrapConfig = window.checkoutConfig.amGiftWrap;

        return Component.extend({
            defaults: {
                template: 'Amasty_GiftWrap/summary/totals'
            },
            totals: quote.getTotals(),
            excludingTaxMessage: $t('Excluding Tax'),
            includingTaxMessage: $t('Including Tax'),

            /**
             * Get gift wrapping price based on options.
             * @returns {int}
             */
            getValue: function () {
                var price = 0,
                    wrappingSegment;

                if (
                    this.totals() &&
                    totals.getSegment('am_gift_wrap_quote') &&
                    totals.getSegment('am_gift_wrap_quote').hasOwnProperty('extension_attributes')
                ) {
                    wrappingSegment = totals.getSegment('am_gift_wrap_quote')['extension_attributes'];

                    switch (this.level) {
                        case 'order':
                            price = wrappingSegment.hasOwnProperty('am_gift_wrap_total_price') ?
                                wrappingSegment['am_gift_wrap_total_price'] :
                                0;
                            break;

                        case 'item':
                            price = wrappingSegment.hasOwnProperty('am_gift_wrap_total_price') ?
                                wrappingSegment['am_gift_wrap_total_price'] :
                                0;
                            break;
                    }
                }

                return this.getFormattedPrice(price);
            },

            /**
             * Get gift wrapping price (including tax) based on options.
             * @returns {int}
             */
            getIncludingTaxValue: function () {
                var price = 0,
                    wrappingSegment;

                if (
                    this.totals() &&
                    totals.getSegment('am_gift_wrap_quote') &&
                    totals.getSegment('am_gift_wrap_quote').hasOwnProperty('extension_attributes')
                ) {
                    wrappingSegment = totals.getSegment('am_gift_wrap_quote')['extension_attributes'];

                    switch (this.level) {
                        case 'order':
                            price = wrappingSegment.hasOwnProperty('am_gift_wrap_total_price_incl_tax') ?
                                wrappingSegment['am_gift_wrap_total_price_incl_tax'] :
                                0;
                            break;

                        case 'item':
                            price = wrappingSegment.hasOwnProperty('am_gift_wrap_total_price_incl_tax') ?
                                wrappingSegment['am_gift_wrap_total_price_incl_tax'] :
                                0;
                            break;
                    }
                }

                return this.getFormattedPrice(price);
            },

            /**
             * Check gift wrapping option availability.
             * @returns {Boolean}
             */
            isAvailable: function () {
                var isAvailable = false,
                    wrappingSegment;

                if (!this.isFullMode()) {
                    return false;
                }

                if (
                    this.totals() &&
                    totals.getSegment('am_gift_wrap_quote') &&
                    totals.getSegment('am_gift_wrap_quote').hasOwnProperty('extension_attributes')
                ) {
                    wrappingSegment = totals.getSegment('am_gift_wrap_quote')['extension_attributes'];

                    isAvailable = wrappingSegment.hasOwnProperty('am_gift_wrap_wrap_ids_count') ?
                        wrappingSegment['am_gift_wrap_wrap_ids_count'] > 0 :
                        false;
                }

                return isAvailable;
            },

            /**
             * Check if both gift wrapping prices should be displayed.
             * @returns {Boolean}
             */
            displayBothPrices: function () {
                var displayBothPrices = false;

                switch (this.level) {
                    case 'order':
                        displayBothPrices = !!wrapConfig.displayWrapBothPrices;
                        break;

                    case 'item':
                        displayBothPrices = !!wrapConfig.displayWrapBothPrices;
                        break;

                }

                return displayBothPrices;
            },

            /**
             * Check if gift wrapping prices should be displayed including tax.
             * @returns {Boolean}
             */
            displayPriceInclTax: function () {
                var displayPriceInclTax = false;

                switch (this.level) {
                    case 'order':
                        displayPriceInclTax = !!wrapConfig.displayWrapInclTaxPrice;
                        break;

                    case 'item':
                        displayPriceInclTax = !!wrapConfig.displayWrapInclTaxPrice;
                        break;

                }

                return displayPriceInclTax && !this.displayBothPrices();
            },

            /**
             * Check if gift wrapping prices should be displayed excluding tax.
             * @returns {Boolean}
             */
            displayPriceExclTax: function () {
                return !this.displayPriceInclTax() && !this.displayBothPrices();
            }
        });
    }
);
