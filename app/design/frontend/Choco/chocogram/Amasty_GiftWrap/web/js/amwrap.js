/**
 * Default Module logic
 * @return widget
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'amwrapMessages',
    'domReady!',
], function ($, customerData, messages) {
    'use strict';

    $.widget('mage.amwrap', {
        options: {
            activeClass: '-active',
            hiddenClass: '-hidden',
            currentProductName: null,
            isMessageDependency: null,
            stepsCount: 2,
            activeInput: '[data-amwrap-js="wrap-active"]',
            checkbox: '[data-amwrap-js="checkbox"]',
            wrapActive: '[data-amwrap-js="wrapper"]',
            wrapText: '[data-amwrap-js="wrapper-text"]',
            slideList: '[data-amwrap-js="slide-list"]',
            toolbarBlock: '[data-amwrap-js="toolbar-block"]',
            toolbarPager: '[data-amwrap-js="toolbar-pager"]',
            item: '[data-amwrap-js="item"]',
            finishBtn: '[data-amwrap-js="finish-step"]',
            nextBtn: '[data-amwrap-js="next-step"]',
            prevBtn: '[data-amwrap-js="prev-step"]',
            existingBtn: '[data-amwrap-js="exist-action"]',
            existingSection: '[data-amwrap-js="choose-existing-wrap"]',
            newBtn: '[data-amwrap-js="new-action"]',
            steps: '[data-amwrap-js="steps"]',
            step: '[data-amwrap-js="step"]',
            stepCard: '[data-amwrap-step="card"]',
            messageWrap: '[data-amwrap-js="message-wrap"]',
            wrapIdAttr: 'data-amwrap-id',
            cartIdAttr: 'data-amcard-id',
            receiptHidden: '[data-amwrap-js="receipt-hidden"]',
            cardMessage: '[data-amwrap-js="card-message"]',
            wrapSelectCheckbox: '[data-amwrap-js="select-finish"]',
            addToCartButtonSelector: '#product-addtocart-button',
            addingWrapErrorMessage: 'You are trying to choose a deleted wrapping. Please reload the page to see the valid content.',
            itemWrapBtn: '[data-amwrap-js="wrap-button"]',
        },

        _create: function () {
            var self = this,
                options = self.options;

            options.activeInput = self.element.find(options.activeInput);
            options.finishBtn = self.element.find(options.finishBtn);
            options.toolbarBlock = self.element.find(options.toolbarBlock);
            options.toolbarPager = self.element.find(options.toolbarPager);
            options.wrapActive = self.element.find(options.wrapActive);
            options.wrapText = self.element.find(options.wrapText);
            options.steps = self.element.find(options.steps);
            options.wrapSelectCheckbox = self.element.find(options.wrapSelectCheckbox);

            options.nextBtn = self.element.find(options.nextBtn).on('click', function (e) {
                var activeStep = options.wrapActive.find(options.step + '.' + options.activeClass),
                    activeItem = activeStep.find(options.item + '.' + options.activeClass);

                e.preventDefault();
                if (activeItem.length) {
                    activeStep.amwrapDataCollector('changeItem', activeItem[0]);
                }
                self.element.amwrapToolbar('next');
            });

            options.prevBtn = self.element.find(options.prevBtn).on('click', function (e) {
                e.preventDefault();
                --options.stepQty;
                self.element.amwrapToolbar('prev');
            });

            self.element.find(options.finishBtn).on('click', function (e) {
                e.preventDefault();
                options.wrapSelectCheckbox.prop('checked', true);
                self.setAddToCartDisabled(false);
            });

            self.element.on('click', options.newBtn, function () {
                self._hideExistingSection();
                self._toggleSteps(self.element.find(options.activeInput)[0]);
                self.element.amwrapToolbar('clear');
                messages.removeMessage(options.wrapActive);
            });

            self.element.on('click', options.itemWrapBtn, function () {
                self._hideExistingSection();
                self._toggleSteps(self.element.find(options.activeInput)[0]);
                self.element.amwrapToolbar('clear');
                messages.removeMessage(options.wrapActive);
            });

            self.element.on('click', options.existingBtn, function () {
                var id = $(this).attr(options.wrapIdAttr),
                    wrapData = self.editGiftWrap(id),
                    message = self._getAddedWrapMessage();

                if (options.isMessageDependency && options.toolbarPager.find('li').length === 2) {
                    options.stepsCount = 1;
                }

                if (wrapData != null) {
                    self.editGiftWrap(id);
                    self._hideExistingSection();
                    self._toggleSteps(self.element.find(options.activeInput)[0]);
                    self.element.amwrapToolbar('setStep', options.stepsCount);
                    messages.showMessage(options.wrapActive, message, 'success');
                } else {
                    messages.showMessage(options.existingSection, options.addingWrapErrorMessage, 'error');
                }
            });

            options.activeInput.on('change', function (e) {
                if (self.element.find(options.existingSection).length) {
                    self._toggleExistingSection(this);

                    if (!e.target.checked){
                        options.wrapActive.removeClass(options.activeClass);
                        options.wrapText.removeClass(options.activeClass);
                    }

                } else {
                    self._toggleSteps(this);
                }
            });

            self.element.on("click", options.item, function () {
                self._toggleElem(this);
                self.resolveGiftMessage(this);
            });
        },

        _getAddedWrapMessage: function() {
            var options = this.options;

            options.name = this.options.currentProductName;

            if (options.name === null) {
                options.message = $.mage.__('Product(s) was added successfully');
                return options.message;
            }

            options.message = $.mage.__('%1 was added successfully').replace('%1', options.name);

            return options.message;
        },

        editGiftWrap: function (id) {
            var self = this,
                options = self.options,
                cart = customerData.get('cart')();

            if (typeof cart.quote_wrap_data != 'undefined') {
                var wrapData = cart.quote_wrap_data[id];

                if (typeof wrapData === 'undefined') return null;

                var wrap = options.steps.find('[' + options.wrapIdAttr + '="' + wrapData.wrap_id + '"]'),
                    card = options.steps.find('[' + options.cartIdAttr + '="' + wrapData.card_id + '"]'),
                    giftMessage = self.element.find(options.cardMessage),
                    receiptHidden = self.element.find(options.receiptHidden);

                if (wrap.length) {
                    wrap.closest(options.step).amwrapDataCollector('changeItem', wrap);
                    self._chooseCheckbox(wrap);
                }

                if (card.length) {
                    card.closest(options.step).amwrapDataCollector('changeItem', card);
                    self._chooseCheckbox(card);
                    self.resolveGiftMessage(card);
                }

                if (wrapData.is_receipt_hidden != '0') {
                    receiptHidden.prop('checked', true);
                } else {
                    receiptHidden.prop('checked', false);
                }

                giftMessage.val(wrapData.gift_message);
                giftMessage.trigger('change');
                self.element.amwrapToolbar('setStep', 0);


                return wrapData;
            }
        },

        _toggleElem: function (elem) {
            var options = this.options,
                checkbox = $(elem).find(options.checkbox);

            $(elem).closest(options.slideList).find('.' + options.activeClass).removeClass(options.activeClass);

            if (checkbox.prop('checked')) {
                checkbox.prop('checked', false);
            } else {
                this._chooseCheckbox(elem);
            }
        },

        resolveGiftMessage: function (elem) {
            var options = this.options,
                parent = $(elem).closest(options.stepCard),
                giftMessage = parent.find(options.messageWrap);

            if (!parent.length) {
                return;
            }

            if (parent.find(options.item + '.' + options.activeClass).length) {
                giftMessage.removeClass(options.hiddenClass);
            } else {
                giftMessage.addClass(options.hiddenClass);
                parent.find(options.cardMessage).val('');
            }
        },

        _chooseCheckbox: function (elem) {
            this.element.amwrapToolbar('activateNextBtn');
            $(elem).addClass(this.options.activeClass);
            $(elem).find(this.options.checkbox).prop('checked', true);
        },

        _toggleSteps: function (elem) {
            if (!elem || elem.checked) {
                this.showWrap();
                this.setAddToCartDisabled(true);
            } else {
                this.hideWrap();
                this.setAddToCartDisabled(false);
            }
        },

        setAddToCartDisabled: function (flag) {
            $(this.options.addToCartButtonSelector).prop('disabled', flag);
        },

        _toggleExistingSection: function (elem) {
            if (elem.checked) {
                this._showExistingSection();
                this.setAddToCartDisabled(true);
            } else {
                this._hideExistingSection();
                this.setAddToCartDisabled(false);
            }
        },

        _hideExistingSection: function () {
            this.element.find(this.options.existingSection).removeClass(this.options.activeClass);
            this.options.wrapText.removeClass(this.options.activeClass);
        },

        _showExistingSection: function () {
            this.element.find(this.options.existingSection).addClass(this.options.activeClass);
            this.options.wrapText.addClass(this.options.activeClass);
        },

        hideWrap: function () {
            this.options.activeInput.prop('checked', false);
            this.options.wrapActive.removeClass(this.options.activeClass);
            this.options.wrapText.removeClass(this.options.activeClass);
        },

        showWrap: function () {
            this.options.wrapActive.addClass(this.options.activeClass);
            this.options.wrapText.addClass(this.options.activeClass);
            $(this.options.slideList).slick('setPosition');
        },

        show: function () {
            this.element.addClass(this.options.activeClass);
        },

        hide: function () {
            this.element.removeClass(this.options.activeClass);
        }
    });

    return $.mage.amwrap;
});
