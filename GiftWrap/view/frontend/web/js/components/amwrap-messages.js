/**
 * Messages component logic methods
 * @return object
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return {
        options: {
            classes: {
                default: 'amwrap-message',
                type: '-%-message',
            },
            messageSelector: '[data-amwrap-js="amwrap-message"]',
            timer: 3000
        },

        showMessage: function (elem, message, type) {
            var options = this.options,
                msg = $.mage.__(message),
                msgTemplate = $('<p>').attr({
                    'class': options.classes.default,
                    'data-amwrap-js': options.classes.default
                }).text(msg),
                messageElement;

            this.removeMessage(elem);

            if (type === 'error' || typeof type === 'undefined') {
                $(elem).append(msgTemplate);
                this.removeMessage(elem, options.timer);
            }

            if (type === 'success' || type === 'warning') {
                $(elem).prepend(msgTemplate);
            }

            messageElement = $(elem).find(options.messageSelector).addClass(options.classes.type.replace('%', type));

            return messageElement;
        },

        removeMessage: function (elem, timer) {
            var options = this.options;

            elem = $(elem).find(options.messageSelector);

            if (typeof timer === 'undefined') {
                elem.remove();
                return;
            }

            setTimeout(function () {
                elem.remove();
            }, timer);
        }
    };
});
