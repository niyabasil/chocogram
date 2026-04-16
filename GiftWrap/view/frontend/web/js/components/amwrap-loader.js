/**
 * Loader component logic methods
 * @return object
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        options: {
            disabledClass: '-amwrap-disabled',
            activeClass: '-active',
            loadingClass: '-loading',
            triggerSelector: '[data-amwrap-loader="trigger"]',
            openTrigger: '[data-amwrap-popup="open"]'
        },

        disableButtons: function () {
            var options = this.options;

            $(options.triggerSelector).addClass(options.disabledClass);
            $(options.openTrigger).removeClass(options.activeClass);
        },

        enableButtons: function () {
            var options = this.options;

            $(options.triggerSelector).removeClass(options.disabledClass);
            $(options.openTrigger).addClass(options.activeClass);
        },

        buttonsLoadingState: function (state) {
            var options = this.options;

            if (state) {
                $(options.openTrigger).addClass(options.loadingClass);
            } else {
                $(options.openTrigger).removeClass(options.loadingClass);
            }
        },

        showLoader: function () {
            $('body').trigger('processStart');
            this.disableButtons();
        },

        hideLoader: function () {
            $('body').trigger('processStop');
            this.enableButtons();
        }
    };
});
