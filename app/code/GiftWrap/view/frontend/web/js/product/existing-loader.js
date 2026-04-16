/**
 * Logic for loading existing methods
 * @return component
 */

define([
    'jquery',
    'amwrapLoader'
], function ($, loader) {
    'use strict';
    
    function showLoader (element) {
        $('body').trigger('processStart', [$('.amwrap-gift-wrap')]);
    }
    
    function hideLoader () {
        $('body').trigger('processStop');
    }

    return function (config, element) {
        $.ajax({
            url: config.url,
            method: 'GET',
            data: {},
            beforeSend: function () {
                showLoader(element);
                loader.disableButtons();
            },
            success: function (response) {
                if (typeof response.html != 'undefined') {
                    $(element).replaceWith(response.html);
                }
            }
        }).always(function () {
            hideLoader();
            loader.enableButtons();
        });
    };
});
