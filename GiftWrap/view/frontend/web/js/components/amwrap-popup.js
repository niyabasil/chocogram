/**
 * PopUp widget logic methods
 * @return widget
 */

define([
    'jquery',
    'amwrapLoader',
    'mage/template'
], function ($, loader, mageTemplate) {
    'use strict';

    $.widget('mage.amwrapPopup', {
        options: {
            openTrigger: '[data-amwrap-popup="open"]',
            closeTrigger: '[data-amwrap-popup="close"]',
            finishBtn: '[data-amwrap-js="finish-step"]',
            slideListSelector: '[data-amwrap-js="slide-list"]',
            generatedTitleSelector: '[data-amwrap-js="generated-title"]',
            receiptHidden: '[data-amwrap-js="receipt-hidden"]',
            activeClass: '-active',
            editClass: '-edit-wrap',
            titleTemplate: '',
        },

        _create: function () {
            var self = this,
                options = self.options;

            loader.buttonsLoadingState(true);

            $(document).ajaxComplete(function () {
                $(options.openTrigger).on('click', function () {
                    self.open();
                });
            });

            $(options.closeTrigger).on('click', function (e) {
                self.close(e.target);
            });

            $(options.finishBtn).on('click', function (e) {
                self.close(e.target);
            });

            this.receiptHidden = this.element.find(this.options.receiptHidden);

            this.receiptHidden.change(function () {
                var $input = $(this),
                    isChecked = $input.prop('checked');

                $input.val(+isChecked);
            });
        },

        open: function () {
            var self = this,
                options = self.options;

            self.element.addClass(options.activeClass);
            $(this.options.slideListSelector).slick('setPosition');

            this.currentReceiptStatus = this.receiptHidden.prop('checked');
        },

        close: function (elem) {
            var options = this.options;

            if (elem.dataset.amwrapPopup === 'close') {
                this.receiptHidden.prop('checked', this.currentReceiptStatus);
                this.element.removeClass(options.activeClass);
                this.element.removeClass(options.editClass);
                this.element.amwrapCart('resetContent');
            }
        },

        setHeader: function (title, target, description) {
            var self = this,
                options = self.options;

            options.title = title;
            options.description = description === false ? '' : description;
            self._clearHeader();

            options.html = (mageTemplate(options.titleTemplate, {
                data: {
                    title: options.title,
                    description: options.description,
                }
            }));

            $(target).prepend(options.html);
        },

        _clearHeader: function() {
            this.element.find(this.options.generatedTitleSelector).remove();
        }
    });

    return $.mage.amwrapPopup;
});
