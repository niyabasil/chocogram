/**
 * Default save logic methods
 * @return widget
 */

define([
    'jquery',
    'amwrapReload',
    'amwrapLoader'
], function ($, amwrapReload, loader) {
    'use strict';

    $.widget('mage.amwrapSave', {
        options: {
            wrapItemsUrl: '',
            updateWrapUrl: '',
            itemIds: [],
            wrapId: null,
            popupBlock: '[data-amwrap-js="popup-block"]',
            inputsSelector: '[data-amwrap-js="popup-data"] :input'
        },

        _create: function () {
            this.element.on('click', this._save.bind(this));
        },

        _save: function () {
            var self = this,
                data = $(this.options.inputsSelector).serializeArray(),
                url = '';

            if (this.options.wrapId === null) {
                $.each(self.options.itemIds, function (index, itemId) {
                    data.push({
                        name: 'itemsIds[]',
                        value: itemId
                    });
                });
                url = self.options.wrapItemsUrl;
            } else {
                data.push({
                    name: 'amwrap[existing_wrap_id]',
                    value: this.options.wrapId
                });
                url = self.options.updateWrapUrl;
            }
            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                beforeSend: function () {
                    loader.showLoader();
                },
                success: function (response) {
                    amwrapReload(response, self.options.itemIds);
                }
            }).always(function () {
                loader.hideLoader();
                $(self.options.popupBlock).amwrapCart('clearItemListsContent');
            });
        },

        setItemIds: function (itemIds) {
            this.options.itemIds = itemIds;
            this.options.wrapId = null;
        },

        setWrapId: function (wrapId) {
            this.options.wrapId = wrapId;
        }
    });

    return $.mage.amwrapSave;
});
