/**
 * SummaryUpdate component logic methods
 * @return object
 */

define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'uiRegistry',
    'amwrapLoader',
], function ($, getTotalsAction, registry, loader) {
    'use strict';

    return {
        options: {
            deferred: $.Deferred(),
            url: window.checkoutConfig.amGiftWrap.giftWrapCheckoutUpdateUrl,
            giftButtonSelector: 'amgiftwrap',
            wrapItemsSelector: 'wrap_items',
        },

        /**
         * @param {boolean} needReloadTotals
         */
        updateData: function (needReloadTotals) {
            var self = this,
                options = self.options;

            $.ajax({
                type: "GET",
                url: options.url,
                beforeSend: function () {
                    loader.buttonsLoadingState(true);
                },
                success: function (response) {
                    var summaryBlockWrapItems = registry.get({index: options.wrapItemsSelector}),
                        giftButton = registry.get({index: options.giftButtonSelector});

                    // Update Wrappings in Summary block
                    if (summaryBlockWrapItems) {
                        summaryBlockWrapItems.setItems(response.items);
                        summaryBlockWrapItems.setItemsCount(response.items.length);
                    }

                    // Update Gift Wrap Button
                    giftButton.buttonStatus(response.button);

                    if (needReloadTotals) {
                        // Update Order Summary block
                        getTotalsAction([], options.deferred.done(function () {
                            loader.buttonsLoadingState(false);
                        }));
                    }
                }
            });
        }
    };
});
