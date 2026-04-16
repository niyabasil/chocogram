var config = {
    map: {
        "*": {
            amwrapSlick:                "Amasty_GiftWrap/js/components/amwrap-slick",
            amwrapToolbar:              "Amasty_GiftWrap/js/components/amwrap-toolbar",
            amwrapLoader:               "Amasty_GiftWrap/js/components/amwrap-loader",
            amwrapPopup:                "Amasty_GiftWrap/js/components/amwrap-popup",
            amwrapMessages:             "Amasty_GiftWrap/js/components/amwrap-messages",
            amwrapSummaryUpdate:        "Amasty_GiftWrap/js/components/amwrap-summaryUpdate",
            amwrap:                     "Amasty_GiftWrap/js/amwrap",
            amwrapCart:                 "Amasty_GiftWrap/js/cart/amwrap-cart",
            amwrapSave:                 "Amasty_GiftWrap/js/amwrap-save",
            amwrapSelectedProd:         "Amasty_GiftWrap/js/product/selected-items",
            amwrapExistingLoaderProd:   "Amasty_GiftWrap/js/product/existing-loader",
            amwrapData:                 "Amasty_GiftWrap/js/data-collector",
            amwrapReload:               "Amasty_GiftWrap/js/cart/amwrap-reload"
        }
    },
    shim: {
        'Amasty_GiftWrap/js/components/amwrap-summaryUpdate': [ 'Magento_Checkout/js/model/totals' ]
    }
};