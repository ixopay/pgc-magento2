define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: "pgc_creditcard",
                component: "Pgc_Pgc/js/view/payment/method-renderer/creditcard"
            },
        );

        return Component.extend({});
    }
);
