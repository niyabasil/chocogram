/**
 * Default Magento totals logic override
 */

define([
    'Amasty_GiftWrap/js/checkout/summary/totals'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isFullMode: function () {
            return true;
        }
    });
});
