#!/usr/bin/env bash

# set -x

source /utils.sh

cd /bitnami/magento/

if [ ! -f bin/n98-magerun2.phar ]; then
    # Download n98-magerun2.phar
    as_daemon curl https://files.magerun.net/n98-magerun2.phar -o bin/n98-magerun2.phar
    as_daemon chmod +x bin/n98-magerun2.phar
fi

echo -e "Configuring Magento"
as_daemon bin/magento config:set payment/amazonlogin/active 0
as_daemon bin/magento config:set payment/amazon_payment/active 0
as_daemon bin/magento config:set payment/klarna_kp/active 0
as_daemon bin/magento config:set payment/payflow_link/active 0
as_daemon bin/magento config:set payment/wps_express/active 0
as_daemon bin/magento config:set payment/payflowpro/active 0
as_daemon bin/magento config:set payment/payflowpro_cc_vault/active 0
as_daemon bin/magento config:set payment/payflow_express/active 0
as_daemon bin/magento config:set payment/payflow_advanced/active 0
as_daemon bin/magento config:set payment/braintree_cc_vault/active 0
as_daemon bin/magento config:set payment/braintree_paypal/active 0
as_daemon bin/magento config:set payment/braintree/active 0
as_daemon bin/magento config:set payment/paypal_billing_agreement/active 0
as_daemon bin/magento config:set payment/paypal_express/active 0

if [ -d "/bitnami/magento/app/code/Pgc/Pgc" ]; then
    echo -e "Configuring Pgc Extension"
    as_daemon bin/magento config:set pgc/general/sandbox "$SHOP_PGC_SANDBOX"
    as_daemon bin/magento config:set pgc/general/username "$SHOP_PGC_USER"
    as_daemon bin/n98-magerun2.phar config:store:set --encrypt pgc/general/password "$SHOP_PGC_USER_PW"
    as_daemon bin/n98-magerun2.phar config:store:set --encrypt payment/pgc_creditcard/api_key "$SHOP_PGC_API_KEY"
    as_daemon bin/n98-magerun2.phar config:store:set --encrypt payment/pgc_creditcard/shared_secret "$SHOP_PGC_SHARED_SECRET"
    as_daemon bin/n98-magerun2.phar config:store:set --encrypt payment/pgc_creditcard/integration_key "$SHOP_PGC_PUBLIC_KEY"
    as_daemon bin/magento config:set payment/pgc_creditcard/active 1
    as_daemon bin/magento config:set payment/pgc_creditcard/title \""$SHOP_PGC_TITLE"\"
    as_daemon bin/magento config:set payment/pgc_creditcard/sort_order 1
    as_daemon bin/magento config:set payment/pgc_creditcard/seamless "$SHOP_PGC_SEAMLESS"
    as_daemon bin/magento config:set payment/pgc_creditcard/debug "$SHOP_PGC_DEBUG"
    as_daemon bin/magento config:set payment/pgc_creditcard/use_3dsecure "$SHOP_PGC_3DS"
    as_daemon bin/magento config:set payment/pgc_creditcard/payment_action "$SHOP_PGC_PAYMENT_ACTION"
    as_daemon bin/magento config:set payment/pgc_creditcard/signature "$SHOP_PGC_SIGNATURE"
    as_daemon bin/magento config:set payment/pgc_cc_vault/active "$SHOP_PGC_VAULT"
fi

# Fix Order ID
UNIX_TIMESTAMP=$(date +'%s')
mysql \
    -B \
    --host $MAGENTO_DATABASE_HOST \
    --port $MAGENTO_DATABASE_PORT_NUMBER \
    --user $MAGENTO_DATABASE_USER $MAGENTO_DATABASE_NAME \
    --execute "INSERT INTO \`sequence_order_1\` SET sequence_value = '${UNIX_TIMESTAMP}';"

as_daemon bin/magento setup:static-content:deploy -f
as_daemon bin/magento indexer:reindex
as_daemon bin/magento cache:clean
as_daemon bin/magento cache:flush

echo -e "Setup Complete! You can access the instance at: ${MAGENTO_HOST}"
