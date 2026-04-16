/**
 * Data Colector widget logic methods
 * Collected selected items for last step
 * @return widget
 */

define([
    'jquery',
    'mage/translate',
    'amwrapSelectedProd'
], function ($) {
    'use strict';

    $.widget('mage.amwrapDataCollector', {
        options: {
            wrapper: '[data-amwrap-js="wrapper-block"]',
            steps: '[data-amwrap-js="steps"]',
            step: '[data-amwrap-js="step"]',
            nextBtn: '[data-amwrap-js="next-step"]',
            checkbox: '[data-amwrap-js="checkbox"]',
            activeClass: '-active',
            defaultCardName: $.mage.__('Gift Message'),
            parent: $('<li class="amwrap-choose"></li>'),
            editBtn: $('<button class="amwrap-edit-btn" data-amwrap-js="edit-step"></button>'),
            nameBlockNode: $('<p class="amwrap-name-block"></p>'),
            nameNode: $('<span class="amwrap-name"></span>'),
            imgNode: $('<div class="amwrap-img-block"></div>'),
            descNode: $('<span class="amwrap-description"></span>'),
            priceNode: $('<span class="amwrap-price"></span>'),
            cardMessage: '[data-amwrap-js="card-message"]',
            item: '[data-amwrap-js="item"]',
            chooseList: '[data-amwrap-js="choose-list"]',
            img: '[data-amwrap-js="img"]',
            stepCard: '[data-amwrap-step="card"]',
            name: '[data-amwrap-js="name"]',
            price: '[data-amwrap-js="price"]',
            desc: '[data-amwrap-js="desc"]',
            cardStatus: false
        },

        _create: function () {
            var self = this,
                options = self.options,
                wrapper = $(this.element).closest(options.wrapper);

            options.toolbar = wrapper;
            options.dataList = wrapper.find(options.chooseList);
            options.steps = wrapper.find(options.steps);

            options.parent = options.parent.clone();
            options.imgNode = options.imgNode.clone();
            options.descNode = options.descNode.clone();
            options.nameNode = options.nameNode.clone();
            options.nameBlockNode = options.nameBlockNode.clone();
            options.priceNode = options.priceNode.clone();
            options.editBtn = options.editBtn.clone();
            options.cardMessage = wrapper.find(options.cardMessage);
            options.stepCard = wrapper.find(options.stepCard);

            if (+options.step === 1) {
                options.cardMessage.on('change', function () {
                    self.setCardMessage($(this).val());
                });
            }

            self.element.on("click", options.item, function () {
                var options = self.options,
                    checkbox = $(this).find(options.checkbox);

                if (checkbox.attr('checked')) {
                    self.removeItem();
                }
            });

            self.createItem();
        },

        changeItem: function (elem) {
            var options = this.options,
                price = $(elem).find(options.price).html(),
                name = $(elem).find(options.name).html(),
                img = $(elem).find(options.img).clone(),
                description = $(elem).find(options.desc).html();

            options.cardStatus = true;
            options.priceNode.html(price);
            options.imgNode.html(img).show();
            options.nameNode.html(name);
            options.parent.addClass(options.activeClass);

            if (+options.step === 0) {
                options.descNode.html(description);
                options.toolbar.amwrapToolbar('setTitle', name);
            }

            if (+options.step === 1) {
                this.setCardMessage(options.cardMessage.val());
            }
        },

        createItem: function () {
            var options = this.options;

            options.editBtn.on("click", function () {
                options.toolbar.amwrapToolbar('setStep', +options.step);

                return false;
            });

            if (+options.step === 1) {
                options.nameNode.html(options.defaultCardName)
            }

            options.nameBlockNode.html([
                options.nameNode,
                options.descNode
            ]);

            options.parent.html([
                options.imgNode,
                options.nameBlockNode,
                options.priceNode,
                options.editBtn
            ]);

            options.parent.addClass('_order-' + options.step);
            options.dataList.append(options.parent);

        },

        removeItem: function () {
            var options = this.options;

            options.cardStatus = false;
            options.parent.removeClass(options.activeClass);

            if (+options.step === 1 && options.cardMessage.val().trim()) {
                options.parent.addClass(options.activeClass);
                options.imgNode.hide();
                options.priceNode.hide();
                options.nameNode.html(options.defaultCardName);
                options.stepCard.find(options.item + '.' + options.activeClass).removeClass(options.activeClass);
                this.setCardMessage(options.cardMessage.val());
            }

            if (+options.step === 0) {
                options.toolbar.amwrapToolbar('setTitle');
                options.toolbar.amwrapToolbar('disableNextBtn');
            }
        },

        setCardMessage: function (msg) {
            var options = this.options;

            if (!msg && !options.cardStatus) {
                options.parent.removeClass(options.activeClass);
                options.cardMessage.val('');
                options.descNode.text(msg);
                this.removeItem();

                return false;
            }

            if ((options.stepCard.length && !options.stepCard.find(options.item + '.' + options.activeClass).length) || (!msg.trim() && !options.cardStatus)) {
                options.parent.removeClass(options.activeClass);

                return false;
            }

            options.parent.addClass(options.activeClass);
            options.descNode.text(msg);
        }
    });

    return $.mage.amwrapDataCollector;
});
