/**
 * Toolbar component logic methods
 * @return object
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.amwrapToolbar', {
        options: {
            wrapper: '[data-amwrap-js="wrapper-block"]',
            slick: '[data-amwrap-js="slide-list"]',
            activeClass: '-active',
            checkedClass: '-checked',
            hiddenClass: '-hidden',
            checkbox: '[data-amwrap-js="checkbox"]',
            title: '[data-amwrap-js="toolbar-block"] .amwrap-title',
            block: '[data-amwrap-js="toolbar-block"]',
            pager: '[data-amwrap-js="toolbar-pager"]',
            messageWrap: '[data-amwrap-js="message-wrap"]',
            finishBtn: '[data-amwrap-js="finish-step"]',
            nextBtn: '[data-amwrap-js="next-step"]',
            cardMessage: '[data-amwrap-js="card-message"]',
            prevBtn: '[data-amwrap-js="prev-step"]',
            steps: '[data-amwrap-js="steps"]',
            step: '[data-amwrap-js="step"]',
            chooseList: '[data-amwrap-js="choose-list"]',
            wrapSelectCheckbox: '[data-amwrap-js="select-finish"]',
            stepQty: 0,
            isMessageWithoutCard: null
        },

        _create: function () {
            var options = this.options;

            options.steps = this.element.find(options.steps);
            options.nextBtn = this.element.find(options.nextBtn);
            options.prevBtn = this.element.find(options.prevBtn);
            options.finishBtn = this.element.find(options.finishBtn);
            options.block = this.element.find(options.block);
            options.pager = this.element.find(options.pager);
            options.title = this.element.find(options.title);
            options.chooseList = this.element.find(options.chooseList);
            options.messageWrap = this.element.find(options.messageWrap);
            options.cardMessage = this.element.find(options.cardMessage);
        },

        next: function () {
            this.setStep(++this.options.stepQty);
        },

        prev: function () {
            this.setStep(--this.options.stepQty);
        },

        setStep: function (index) {
            var options = this.options;

            options.stepQty = index;
            this.setToolbar(index);

            options.finishBtn.addClass(options.hiddenClass);
            options.nextBtn.addClass(options.hiddenClass);
            options.prevBtn.addClass(options.hiddenClass);

            if (index < 1) {
                options.nextBtn.removeClass(options.hiddenClass);
            }

            if (index === $(options.steps).children().length - 1) {
                options.finishBtn.removeClass(options.hiddenClass);
                options.prevBtn.removeClass(options.hiddenClass);
            }

            if (index > 0 && index < $(options.steps).children().length - 1) {
                options.nextBtn.removeClass(options.hiddenClass);
                options.prevBtn.removeClass(options.hiddenClass);
            }

            $(options.steps).find('.' + options.activeClass + options.step).removeClass(options.activeClass);
            $(options.steps).children().each(function (i, elem) {
                if (i === index) {
                    $(elem).addClass(options.activeClass).find(options.slick).slick('setPosition');

                    return false;
                }
            });
        },

        disableNextBtn: function () {
            this.options.nextBtn.removeClass(this.options.activeClass);
        },

        activateNextBtn: function () {
            this.options.nextBtn.addClass(this.options.activeClass);
        },

        setToolbar: function (index) {
            var options = this.options;

            options.pager.children().each(function (i, elem) {
                $(elem).removeClass(options.checkedClass + ' ' + options.activeClass);

                if (i === index) {
                    $(elem).addClass(options.activeClass);
                }

                if (i < index) {
                    $(elem).addClass(options.checkedClass);
                }
            });
        },

        setTitle: function (name) {
            if (name) {
                this.options.title.html(name);
            } else {
                this.options.title.html($.mage.__('Choose the Gift Wrap'));
            }
        },

        getTitle: function () {
            return this.options.title.html();
        },

        clear: function () {
            var options = this.options,
                steps = this.element.find(options.step),
                activeElems = steps.find('.' + options.activeClass);

            activeElems.each(function (index, item) {
                $(item).removeClass(options.activeClass).find(options.checkbox).prop('checked', false);
            });
            $(options.wrapSelectCheckbox).prop('checked', false);

            steps.each(function (index, item) {
                if (steps.length - 1 === index){
                    return
                }

                $(item).amwrapDataCollector('removeItem');
                $(item).amwrapDataCollector('setCardMessage', false);
            });

            if (options.isMessageWithoutCard) {
                options.messageWrap.addClass(options.hiddenClass);
                options.cardMessage.val('');
            }

            this.setStep(0);
            this.setTitle();
            this.disableNextBtn();
        }
    });

    return $.mage.amwrapToolbar;
});




