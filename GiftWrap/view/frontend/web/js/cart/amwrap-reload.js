/**
 * Default reload logic methods
 * @return function
 */

define([
    'jquery',
    'amwrapSummaryUpdate',
], function ($, summaryUpdate) {
    'use strict';

    var selectors = {
        list_block: '[data-amwrap-js="amwrap-added-list"]',
        existing_block: '[data-amwrap-js="amwrap-existing-list"]',
        itemsButtons: '[data-amwrap-js="wrap-item"][data-item-id="{item_id}"]',
        cart_button: '[data-amwrap-js="cart-button"]'
    };

    return function (data, itemIds) {
        $.each(selectors, function (blockName, blockSelector) {
            if (typeof data[blockName] !== 'undefined') {
                $(blockSelector).replaceWith(data[blockName]);
            }
        });
        $.each(itemIds, function (index, itemId) {
            var blockName = 'item_button_' + itemId,
                blockSelector = selectors.itemsButtons.replace('{item_id}', itemId);

            if (typeof data[blockName] !== 'undefined') {
                $(blockSelector).html($(data[blockName]).html());
            }
        });

        // Trigger update Wraps on the Order Summary block
        summaryUpdate.updateData(true);
    }
});
