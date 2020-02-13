#!/bin/bash
# set -x

fix_symlink() {
    unlink $1
    mkdir $1
    cp -r $2/* $1/
    cp -r $2/.* $1/
    chown -R bitnami:daemon $1
}

echo -e "Starting Magento"

/app-entrypoint.sh /run.sh &

if [ ! -f "/setup_complete" ]; then

    echo -e "Waiting for Magento to Initialize"

    while [ ! -f "/bitnami/magento/.initialized" ]; do sleep 2s; done

    while (! $(curl --silent -H "Host: ${MAGENTO_HOST}" http://localhost:80/index.php/ | grep "CMS homepage" > /dev/null)); do sleep 2s; done

    fix_symlink /opt/bitnami/magento/htdocs/var /bitnami/magento/htdocs/var
    fix_symlink /opt/bitnami/magento/htdocs/pub/static /bitnami/magento/htdocs/pub/static
    fix_symlink /opt/bitnami/magento/htdocs/pub/media /bitnami/magento/htdocs/pub/media
    fix_symlink /opt/bitnami/magento/htdocs/app/etc /bitnami/magento/htdocs/app/etc
    fix_symlink /opt/bitnami/magento/htdocs/app/design /bitnami/magento/htdocs/app/design
    fix_symlink /opt/bitnami/magento/htdocs/app/code /bitnami/magento/htdocs/app/code

    echo -e "Installing PGC Extension"

    mkdir -p /opt/bitnami/magento/htdocs/app/code/
    if [ "${BUILD_ARTIFACT}" != "undefined" ]; then
        if [ -f /dist/paymentgatewaycloud.zip ]; then
            echo -e "Using Supplied zip ${BUILD_ARTIFACT}"
            ZIP_NAME=$(basename "${BUILD_ARTIFACT}")
            mkdir /tmp/source
            unzip /dist/paymentgatewaycloud.zip -d /tmp/source
            cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
        else
            echo "Faled to build!, there is no such file: ${BUILD_ARTIFACT}"
            exit 1
        fi
    else
        if [ ! -d "/source/.git" ] && [ ! -f  "/source/.git" ]; then
            echo -e "Checking out branch ${BRANCH} from ${REPOSITORY}"
            git clone $REPOSITORY /tmp/paymentgatewaycloud
            cd /tmp/paymentgatewaycloud
            git checkout $BRANCH
            cp -R /tmp/paymentgatewaycloud/* /opt/bitnami/magento/htdocs/app/code/
        else
            echo -e "Using Development Source!"
            cp -R /source/* /opt/bitnami/magento/htdocs/app/code/
        fi
    fi
    chown -R bitnami:daemon /opt/bitnami/magento/htdocs

    php /opt/bitnami/magento/htdocs/bin/magento module:enable Pgc_Pgc

    echo -e "Import Products"

    if [ ! -d "/magento2-sample-data" ]; then
        echo -e "Checking out branch 2.3.3 from https://github.com/magento/magento2-sample-data"
        git clone https://github.com/magento/magento2-sample-data /magento2-sample-data
        cd /magento2-sample-data
        git checkout 2.3.3
        php -f /magento2-sample-data/dev/tools/build-sample-data.php -- --ce-source="/opt/bitnami/magento/htdocs/"
    fi

    # Rebuild cache and classes
    php /opt/bitnami/magento/htdocs/bin/magento setup:upgrade
    chown -R bitnami:daemon /magento2-sample-data/pub/media/catalog
    php /opt/bitnami/magento/htdocs/bin/magento setup:di:compile
#    php /opt/bitnami/magento/htdocs/bin/magento cache:flush

    echo -e "Configuring Magento"

    # Disable other payment Providers
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/amazonlogin/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/amazon_payment/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/klarna_kp/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/payflow_link/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/wps_express/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/payflowpro/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/payflowpro_cc_vault/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/payflow_express/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/payflow_advanced/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/braintree_cc_vault/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/braintree_paypal/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/braintree/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/paypal_billing_agreement/active 0
    php /opt/bitnami/magento/htdocs/bin/magento config:set payment/paypal_express/active 0

    echo -e "Configuring Extension"

    # Enable PGC Payment Providers
    if [ $SHOP_PGC_URL ]; then
        php /opt/bitnami/magento/htdocs/bin/magento config:set pgc/general/username "$SHOP_PGC_USER"
        php /opt/bitnami/magento/htdocs/bin/magento config:set pgc/general/password "$SHOP_PGC_PASSWORD"
        php /opt/bitnami/magento/htdocs/bin/magento config:set pgc/general/host "$SHOP_PGC_URL"

        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/active 1
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/api_key "$SHOP_PGC_API_KEY"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/shared_secret "$SHOP_PGC_SECRET"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/integration_key "$SHOP_PGC_INTEGRATION_KEY"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/sort_order 1
    fi

    # Where to use https per default
    php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_frontend 0
    if [ $PRECONFIGURE ]; then
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "https://${MAGENTO_HOST}/"
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 1
    else
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "http://${MAGENTO_HOST}/"
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 0
    fi

    php /opt/bitnami/magento/htdocs/bin/magento cache:flush

    chown -R bitnami:daemon /opt/bitnami/magento/htdocs
    find /opt/bitnami/magento/htdocs -type f -exec chmod 644 {} \;
    find /opt/bitnami/magento/htdocs -type d -exec chmod 755 {} \;
    find /opt/bitnami/magento/htdocs/var -type d -exec chmod 777 {} \;
    find /opt/bitnami/magento/htdocs/pub/media -type d -exec chmod 777 {} \;
    find /opt/bitnami/magento/htdocs/pub/static -type d -exec chmod 777 {} \;
    chmod 777 /opt/bitnami/magento/htdocs/app/etc
    chmod 644 /opt/bitnami/magento/htdocs/app/etc/*.xml

    touch /setup_complete

    echo -e "Setup Complete! You can access the instance at: ${MAGENTO_HOST}"
    
    if [ $PRECONFIGURE ]; then
        kill 1
    else
        # Keep script Running
        trap : TERM INT; (while true; do sleep 1m; done) & wait
    fi

else

    # Update URL
    # sleep 30s
    # php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
    # php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "https://${MAGENTO_HOST}/"


    # Keep script Running
    trap : TERM INT; (while true; do sleep 1m; done) & wait

fi
