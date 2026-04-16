/**
 * Selected elements widget logic methods
 * @return widget
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.amwrapSelected', {
        options: {
            activeClass: '-active',
            addToCart: '#product-addtocart-button',
            wrapper: '[data-amwrap-js="wrapper-block"]',
            name: '[data-amwrap-js="selected-name"]',
            editBtn: '[data-amwrap-js="edit-step"]',
            removeBtn: '[data-amwrap-js="remove-step"]',
            finishBtn: '[data-amwrap-js="finish-step"]',
            wrapSelectCheckbox: '[data-amwrap-js="select-finish"]'
        },

        _create: function () {
            var self = this,
                options = self.options;

            options.wrapper = this.element.siblings(options.wrapper);
            options.wrapSelectCheckbox = $(options.wrapSelectCheckbox);

            if (!options.wrapper.length) {
                return;
            }

            options.finishBtn = options.wrapper.find(options.finishBtn);
            options.name = this.element.find(options.name);

            self.element.on('click', options.editBtn, function (e) {
                e.preventDefault();
                options.wrapSelectCheckbox.prop('checked', false);
                options.wrapper.amwrap('show');
                options.wrapper.amwrap('setAddToCartDisabled', true);
                self.hide();
                self.edit();
            });

            self.element.on('click', options.removeBtn, function (e) {
                e.preventDefault();
                options.wrapSelectCheckbox.prop('checked', false);
                options.wrapper.amwrap('show').amwrap('hideWrap');
                self.hide();
                self.remove();
            });

            $(options.finishBtn).on('click', function () {
                self.setName(options.wrapper.amwrapToolbar('getTitle'));
                options.wrapper.amwrap('hide');
                self.show();
            });

            $(document).on('ajax:addToCart', function () {
                if (!options.wrapper[0].isConnected) { // Check if gift wrapper is exist on page
                    return;
                }

                options.wrapper.amwrap('show').amwrap('hideWrap');
                self.hide();
                self.remove();
            });
        },

        setName: function (name) {
            this.options.name.html(name);
        },

        remove: function () {
            this.options.wrapper.amwrapToolbar('clear');
        },

        edit: function () {
            this.options.wrapper.amwrapToolbar('setStep', 0);
        },

        show: function () {
            this.element.addClass(this.options.activeClass);
        },

        hide: function () {
            this.element.removeClass(this.options.activeClass);
        }
    });

    return $.mage.amwrapSelected;
});
