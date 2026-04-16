/**
 * Slick widget initialization methods
 * @return widget
 */

define([
    "jquery",
    "Amasty_Base/vendor/slick/slick.min"
], function ($, slick) {
    'use strict';

    $.widget('mage.amwrapSlick', {
        options: {
            slidesToShow: 4,
            slidesToScroll: 4,
            infinite: false,
            responsive: [
                {
                    breakpoint: 980,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5,
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                },
                {
                    breakpoint: 400,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                },
                {
                    breakpoint: 250,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ],
            wrapper: '[data-amwrap-js="wrapper-block"]'
        },

        _create: function () {
            var self = this,
                options = self.options;

            self.element.not('.slick-initialized').slick(options);
        }
    });

    return $.mage.amwrapSlick;
});
