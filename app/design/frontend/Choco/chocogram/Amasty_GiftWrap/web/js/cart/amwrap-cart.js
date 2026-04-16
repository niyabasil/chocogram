/**
 * Default Module logic for cart page
 * @return widget
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'Magento_Customer/js/customer-data',
    'amwrapReload',
    'amwrapLoader',
    'mage/validation',
    'amwrapMessages',
    'uiRegistry',
    'mage/translate',
], function ($,_, mageTemplate, customerData, amwrapReload, loader, validation, messages, registry, $t) {
    'use strict';

    $.widget('mage.amwrapCart', {
        options: {
            deleteWrapUrl: '',
            chosenItemTemplate: '',
            titleTemplate: '',
            activeClass: '-active',
            editClass: '-edit-wrap',
            uncheckedClass: '-unchecked',
            itemIdAttr: 'data-item-id',
            wrapAvailableAttribute: 'am_available_for_wrapping',
            selectors: {
                wrapperBlock: '[data-amwrap-js="wrapper-block"]',
                optionList: '[data-amwrap-js="option-list"]',
                finishBtn: '[data-amwrap-js="finish-step"]',
                itemWrapBtn: '[data-amwrap-js="wrap-button"]',
                itemWrapDiv: '[data-amwrap-js="wrap-item"]',
                wrapAllBtn: '[data-amwrap-js="wrap-all"]',
                wrapTargetBtn: '[data-amwrap-js="wrap-target"]',
                itemsListContent: '[data-amwrap-js="items-list-content"]',
                existingSection: '[data-amwrap-js="choose-existing-wrap"]',
                listCheckbox: '[data-amwrap-js="list-item-checkbox"]',
                listStep: '[data-amwrap-js="list-next-step"]',
                listBtnWrap: '[data-amwrap-js="items-list-btnwrap"]',
                removeWrap: '[data-amwrap-js="remove-step"]',
                editWrap: '[data-amwrap-js="edit-step"]',
                addGiftWrap: '[data-amwrap-js="cart-button"]',
                addedGiftWrap: '[data-amwrap-js="added-block"]',
                listItemForm: '[data-amwrap-js="list-items-form"]',
                listItem: '[data-amwrap-js="list-item"]',
                listItemQty: '[data-amwrap-js="qty"]',
                wrapperInner: '[data-amwrap-js="wrapper"]',
                itemsList: '[data-amwrap-js="items-list"]',
                generatedTitle: '[data-amwrap-js="generated-title"]',
                openTrigger: '[data-amwrap-popup="open"]',
                deleteCheckbox: '[data-amwrap-js="delete-checkbox"]',
            },
            stepsTitle: {
                default: $t('Make It a Gift'),
                wrapAll: $t('Wrap All Available Items'),
                chooseItems: $t('Choose Items for Wrapping'),
                editWrap: $t('Edit Gift Wrap: '),
            },
            stepsDescription: {
                chooseItems: $t('Choose Items you want to wrap (all these items will be wrapped together).')
            }
        },

        _create: function () {
            var options = this.options,
                giftButton = registry.get('amgiftwrap');

            this._resolveElements();
            this._initButtons();
            this.element.amwrapPopup('setHeader', options.stepsTitle.default, options.selectors.wrapperBlock);
            options.selectors.wrapperBlock = this.element.find(options.selectors.wrapperBlock);
            this.hide(options.selectors.wrapperBlock);

            if(giftButton) {
                giftButton.isVisible(true);
            }

            loader.buttonsLoadingState(false);

            // fix create GiftWrap without quote_wrap_data in Magento_Customer/js/customer-data
            const data = customerData.get('cart')();
            if (!data?.quote_wrap_data) {
                customerData.reload(['cart'], true);
            }
        },

        _deleteGiftWrap: function (id) {
            var self = this;

            $.ajax({
                url: this.options.deleteWrapUrl,
                method: 'POST',
                data: {id: id},
                beforeSend: function () {
                    loader.showLoader();
                },
                success: function (response) {
                    amwrapReload(response, self._getAllProducts());
                }
            }).always(function () {
                loader.hideLoader();
            });
        },

        _makeItemsForWrap: function (data) {
            var self = this;

            if (typeof data.items === 'undefined') return;

            this.clearItemListsContent();

            $.each(data.items, function (index, itemData) {
                if (itemData[self.options.wrapAvailableAttribute] && itemData['has_free_qty']) {
                    self._generateTemplate(itemData);
                }
            });
        },

        _makeItemsForEdit: function (data, wrapId) {
            var self = this,
                wrapItems = data.quote_wrap_data[wrapId]['quote_item_ids'],
                result = [];

            if (typeof data.items === 'undefined') return;

            this.clearItemListsContent();

            result = data.items.filter(function (itemData) {
                return Object.keys(wrapItems).indexOf(itemData['item_id']) > -1;
            });

            $.each(result, function (index, itemData) {
                if (itemData['item_id']) {
                    self._generateTemplate(itemData, wrapId, wrapItems);
                }
            });
        },

        _generateTemplate: function (itemData, wrapId, wrapItems) {
            var self = this,
                options = self.options;

            wrapItems = typeof wrapItems === 'undefined' ? [] : wrapItems;

            var html = (mageTemplate(options.chosenItemTemplate, {
                data: {
                    product_name: itemData.product_name,
                    product_image_src: itemData.product_image.src,
                    product_image_alt: itemData.product_image.alt,
                    product_qty: itemData.qty_for_wrap,
                    product_wrapped_qty: +wrapItems[itemData.item_id],
                    item_id: itemData.item_id,
                    options: itemData.options,
                    isEdit: wrapId
                }
            }));

            html = _.unescape(html);

            $(options.selectors.itemsListContent).append(html);
        },

        clearItemListsContent: function () {
            this.element.find(this.options.selectors.itemsListContent).html('');
        },

        _resolveElements: function () {
            this.options.selectors.finishBtn = $(this.options.selectors.finishBtn);
        },

        _initButtons: function () {
            var self = this,
                options = self.options;

            $(document).ajaxComplete(function () {
                $(options.selectors.openTrigger).on('click', function (e) {
                    if (e.target.dataset.amwrapJs !== 'edit-step') {
                        options.selectors.wrapperBlock.amwrapToolbar('clear');
                    }
                });
            });

            $(options.selectors.wrapAllBtn).on('click', function (e) {
                e.preventDefault();
                self.element.amwrapPopup('setHeader', options.stepsTitle.wrapAll, options.selectors.wrapperBlock);
                self.hideOptionList();
                self.show(options.selectors.wrapperBlock);
                self.resolveExistingSection();
                self.options.selectors.finishBtn.amwrapSave('setItemIds', self._getWrapProducts());
                self._showMessageForNotAvailableProducts();
            });

            $(options.selectors.wrapTargetBtn).on('click', function () {
                self.disableButton(options.selectors.listStep);
                self._makeItemsForWrap(customerData.get('cart')());
                self.element.amwrapPopup('setHeader', options.stepsTitle.chooseItems, options.selectors.itemsList, options.stepsDescription.chooseItems);
                self._itemsListCheckboxEvents('adding');
                self._itemsListSelect();
                self.hideOptionList();
                self.show(options.selectors.listBtnWrap);
                self.show(options.selectors.itemsList);
            });

            $(options.selectors.itemWrapDiv).on('click', options.selectors.itemWrapBtn, function () {
                $(options.selectors.wrapperInner).removeClass('-active');
                if ($(this).attr(self.options.itemIdAttr)) {
                    self.show(options.selectors.wrapperBlock);
                    self.resolveExistingSection();
                    self.options.selectors.finishBtn.amwrapSave('setItemIds', [$(this).attr(self.options.itemIdAttr)]);
                }
            });

            $(options.selectors.addedGiftWrap).on('click', options.selectors.removeWrap, function (e) {
                self._deleteGiftWrap(e.target.dataset.amwrapId);
            });

            $(options.selectors.addedGiftWrap).on('click', $(options.selectors.editWrap), function (e) {
                if (e.target.dataset.amwrapJs === 'edit-step') {
                    options.selectors.wrapperBlock.amwrapToolbar('clear');
                    self._editWrap(e);
                }
            });

            $(options.selectors.addedGiftWrap).on('click', $(options.selectors.addGiftWrap), function (e) {
                if (e.target.dataset.amwrapJs === 'cart-button') {
                    self.element.amwrapCart('showOptionList');
                }
            });
        },

        _showMessageForNotAvailableProducts: function () {
            var allProducts = this._getAllProducts().length,
                wrapProducts = this._getWrapProducts().length;

            if (allProducts > wrapProducts) {
                messages.showMessage(
                    this.options.selectors.wrapperBlock,
                    'There %1 product%2 in your cart not available for wrapping.'
                        .replace('%1', allProducts - wrapProducts > 1 ? 'are' : 'is a')
                        .replace('%2', allProducts - wrapProducts > 1 ? 's' : ''),
                    'warning'
                ).addClass('-top');
            }
        },

        _editWrap: function (element) {
            this.data = customerData.get('cart')();

            var options = this.options,
                wrapId = element.target.dataset.amwrapId,
                wraps = this.data.quote_wrap_data[wrapId]['quote_item_ids'],
                wrapTitle = this.data.quote_wrap_data[wrapId]['wrap_name'],
                isAvailableForWrap = this.data.free_qty_for_wrap[Object.keys(wraps)[0]];

            this.element.addClass(options.editClass);

            if (Object.keys(wraps).length > 1 || +wraps[Object.keys(wraps)[0]] > 1 || isAvailableForWrap) {
                this._makeItemsForEdit(this.data, wrapId);
                this.element.amwrapPopup('setHeader', options.stepsTitle.editWrap, options.selectors.itemsList, wrapTitle);
                this.hideOptionList();
                this.show(options.selectors.listBtnWrap);
                this.show(options.selectors.itemsList);
                this._itemsListSelect(wrapId);
                this._itemsListCheckboxEvents('editing');
                this.enableButton(options.selectors.listStep);

            } else {
                this.element.amwrapPopup('setHeader', options.stepsTitle.editWrap, options.selectors.wrapperBlock, wrapTitle);
                this.show(options.selectors.wrapperBlock);
                this.options.selectors.wrapperBlock.amwrap('showWrap');
                this.options.selectors.wrapperBlock.amwrap('editGiftWrap', wrapId);
                this.options.selectors.finishBtn.amwrapSave('setItemIds', this._getAllProducts());
                this.options.selectors.finishBtn.amwrapSave('setWrapId', wrapId);
            }
        },

        _itemsListCheckboxEvents: function (condition) {
            var self = this,
                options = self.options;

            options.checkbox = self.element.find(options.selectors.listCheckbox);

            if (condition === 'editing') {
                self._validateQty(options.checkbox, condition);
                self._removeListItem(options.checkbox, options.selectors.listItem);
            }

            if (condition === 'adding') {
                options.checkbox.on('change', function () {
                    if (options.checkbox.filter(':checked').length > 0) {
                        self.enableButton(options.selectors.listStep);
                    } else {
                        self.disableButton(options.selectors.listStep);
                    }
                });
            }

            options.checkbox.on('change', function () {
                self._validateQty(this);
            });
        },

        _removeListItem: function (element, parent) {
            var self = this,
                options = self.options;

            if (element.length === 1) {
                $(options.selectors.deleteCheckbox).hide();
            }

            element.on('click', function () {
                var inputQty = $(this).closest(parent).slideUp('fast').find(options.selectors.listItemQty);

                inputQty.val(inputQty.attr('max'));
            });

            element.on('change', function () {
                if (options.checkbox.filter(':not(:checked)').length <= 1) {
                    $(options.selectors.deleteCheckbox).hide();
                }
            });
        },

        _validateQty: function (elem, state) {
            var self = this,
                options = self.options;

            options.input = $(elem).parents(options.selectors.listItem).find(options.selectors.listItemQty);

            if (!options.input.length) {
                return;
            }

            if ($(elem).prop('checked') || state === 'editing') {
                options.input.removeClass(options.uncheckedClass);
                self.enableButton(options.input);
            } else {
                options.input.val(parseInt(options.input.attr('max')));
                $(options.selectors.listItemForm).valid();
                validation('clearError', options.input);
                options.input.addClass(options.uncheckedClass);
                self.disableButton(options.input);
            }
        },

        _itemsListSelect: function (amwrapId) {
            var self = this,
                options = self.options;

            $(options.selectors.listStep).off().on('click', function (e) {
                e.preventDefault();

                if ($(options.selectors.listItemForm).valid() && amwrapId) {
                    self._showWrapToEdit(amwrapId);
                }

                if ($(options.selectors.listItemForm).valid() && !amwrapId) {
                    self._showWrapToAdd();
                }
            });
        },

        _showWrapToAdd: function () {
            var self = this,
                options = self.options;

            self.element.amwrapPopup('setHeader', options.stepsTitle.default, options.selectors.wrapperBlock);
            self.show(options.selectors.wrapperBlock);
            self.resolveExistingSection();
            self.hide(options.selectors.itemsList);
            self.hide(options.selectors.listBtnWrap);
            self.options.selectors.finishBtn.amwrapSave('setItemIds', self._getSelectedProducts());
        },

        _showWrapToEdit: function (amwrapId) {
            var self = this,
                options = self.options,
                wrapTitle = this.data.quote_wrap_data[amwrapId]['wrap_name'];

            self.hide(options.selectors.itemsList);
            self.hide(options.selectors.listBtnWrap);
            self.element.amwrapPopup('setHeader', options.stepsTitle.editWrap, options.selectors.wrapperBlock, wrapTitle);
            self.show(options.selectors.wrapperBlock);
            self.options.selectors.wrapperBlock.amwrap('showWrap');
            self.options.selectors.wrapperBlock.amwrap('editGiftWrap', amwrapId);
            self.options.selectors.finishBtn.amwrapSave('setItemIds', self._getSelectedProducts(true));
            self.options.selectors.finishBtn.amwrapSave('setWrapId', amwrapId);
        },

        _getSelectedProducts: function (noFilter) {
            var self = this,
                options = self.options,
                toWrap = [];

            options.checkbox = self.element.find(options.selectors.listCheckbox);

            var items = options.checkbox;

            if (!noFilter) {
                items = items.filter(':checked');
            }

            $.each(items, function (index, item) {
                toWrap.push(item.dataset.itemId);
            });

            return toWrap;
        },

        disableButton: function (elem) {
            $(elem).removeClass(this.options.activeClass);
        },

        enableButton: function (elem) {
            $(elem).addClass(this.options.activeClass);
        },

        hideOptionList: function () {
            $(this.options.selectors.optionList).removeClass(this.options.activeClass);
        },

        showOptionList: function () {
            $(this.options.selectors.optionList).addClass(this.options.activeClass);
        },

        show: function (elem) {
            $(elem).addClass(this.options.activeClass);
        },

        hide: function (elem) {
            $(elem).removeClass(this.options.activeClass);
        },

        resolveExistingSection: function () {
            var self = this,
                options = this.options;

            if (!self.element.find(options.selectors.existingSection).length) {
                self.options.selectors.wrapperBlock.amwrap('showWrap');
            }
            self.element.find(options.selectors.existingSection).addClass(options.activeClass);
        },

        _getWrapProducts: function () {
            var self = this,
                cartData = customerData.get('cart')(),
                availableForWrap = [];

            if (typeof cartData.items !== 'undefined') {
                $.each(cartData.items, function (index, itemData) {
                    if (itemData[self.options.wrapAvailableAttribute]
                      && itemData['has_free_qty']) {
                        availableForWrap.push(itemData.item_id);
                    }
                });
            }

            return availableForWrap;
        },

        _getAllProducts: function () {
            var cartData = customerData.get('cart')(),
                products = [];

            if (typeof cartData.items !== 'undefined') {
                $.each(cartData.items, function (index, itemData) {
                        products.push(itemData.item_id);
                });
            }

            return products;
        },

        resetContent: function () {
            var options = this.options;

            this.element.amwrapPopup('setHeader', options.stepsTitle.default, options.selectors.wrapperBlock);
            this.hideOptionList();
            this.hide(options.selectors.wrapperBlock);
            this.hide(options.selectors.wrapperInner);
            this.hide(options.selectors.itemsList);
            this.hide(options.selectors.existingSection);
            messages.removeMessage(options.selectors.wrapperInner);
            messages.removeMessage(options.selectors.wrapperBlock);
        }
    });

    return $.mage.amwrapCart;
});
