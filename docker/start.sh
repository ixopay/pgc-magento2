#!/bin/bash
set -euo pipefail

error_exit() {
    echo "$1" 1>&2
	exit 1
}

fix_permissions() {
    find /opt/bitnami/magento/htdocs -type f -exec chmod 644 {} \;
    find /opt/bitnami/magento/htdocs -type d -exec chmod 755 {} \;
    find /opt/bitnami/magento/htdocs/var -type d -exec chmod 777 {} \;
    find /opt/bitnami/magento/htdocs/pub/media -type d -exec chmod 777 {} \;
    find /opt/bitnami/magento/htdocs/pub/static -type d -exec chmod 777 {} \;
    chmod 777 /opt/bitnami/magento/htdocs/app/etc
    chmod 644 /opt/bitnami/magento/htdocs/app/etc/*.xml
}

fix_symlink() {
    unlink $1
    rm -rf $1
    cp -rfLH $2 $1 || :
    chown -R bitnami:daemon $1
    chmod -R 775 $1
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

    DB_FIELD_NAME="pgc"
    mkdir -p /opt/bitnami/magento/htdocs/app/code/
    if [ "${BUILD_ARTIFACT}" != "undefined" ]; then
        if [ -f /dist/paymentgatewaycloud.zip ]; then
            echo -e "Using Supplied zip ${BUILD_ARTIFACT}"
            ZIP_NAME=$(basename "${BUILD_ARTIFACT}") || error_exit "Failed to get ZIP Name"
            mkdir /tmp/source
            unzip /dist/paymentgatewaycloud.zip -d /tmp/source
            cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
        else
            error_exit "Faled to build!, there is no such file: ${BUILD_ARTIFACT}"
        fi
    else
        if [ ! -d "/source/.git" ] && [ ! -f  "/source/.git" ]; then
            echo -e "Checking out branch ${BRANCH} from ${REPOSITORY}"
            git clone $REPOSITORY /tmp/paymentgatewaycloud  || error_exit "Failed to clone Repository $REPOSITORY"
            cd /tmp/paymentgatewaycloud
            git checkout $BRANCH || error_exit "Failed to checkout $BRANCH"
            if [ ! -z "${WHITELABEL}" ]; then
                echo -e "Whitelabeling for Magento not Supported yet!"
                cp -R /tmp/paymentgatewaycloud/* /opt/bitnami/magento/htdocs/app/code/
                #echo -e "Running Whitelabel Script for ${WHITELABEL}"
                #echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" || error_exit "Failed to run Whitelabel Script for '$WHITELABEL'"
                #DEST_FILE="$(echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" | tail -n 1 | sed 's/.*Created file "\(.*\)".*/\1/g')"
                #DB_FIELD_NAME="$(php /whitelabel.php snakeCase "${WHITELABEL}")" || error_exit "Failed to extract DB-Field Name"
                #unzip "${DEST_FILE}" -d /tmp/source || error_exit "Failed to extract ZIP"
                #cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
            else
                cp -R /tmp/paymentgatewaycloud/* /opt/bitnami/magento/htdocs/app/code/
            fi
        else
            echo -e "Using Development Source!"
            cd /source/
            if [ ! -z "${WHITELABEL}" ]; then
                echo -e "Whitelabeling for Magento not Supported yet!"
                cp -R /source/* /opt/bitnami/magento/htdocs/app/code/
                #echo -e "Running Whitelabel Script for ${WHITELABEL}"
                #echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" || error_exit "Failed to run Whitelabel Script for '$WHITELABEL'"
                #DEST_FILE="$(echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" | tail -n 1 | sed 's/.*Created file "\(.*\)".*/\1/g')"
                #DB_FIELD_NAME="$(php /whitelabel.php snakeCase "${WHITELABEL}")" || error_exit "Failed to extract DB-Field Name"
                #unzip "${DEST_FILE}" -d /tmp/source || error_exit "Failed to extract ZIP"
                #cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
            else
                cp -R /source/* /opt/bitnami/magento/htdocs/app/code/
            fi
        fi
    fi
    chown -R bitnami:daemon /opt/bitnami/magento/htdocs

    rm -rf /opt/bitnami/magento/htdocs/generated/code/Magento
    
    chown -R bitnami:daemon /opt/bitnami/magento/htdocs
    chmod -R 775 /opt/bitnami/magento/htdocs
    chmod -R 777 /opt/bitnami/magento/htdocs/pub/media
    fix_permissions

    php /opt/bitnami/magento/htdocs/bin/magento module:enable Pgc_Pgc --clear-static-content || error_exit "Failed to enable Extension"
    php /opt/bitnami/magento/htdocs/bin/magento setup:di:compile || error_exit "Failed to compile Magento Classes"

    echo -e "Import Products"

    if [ ! -d "/magento2-sample-data" ]; then
        echo -e "Checking out branch 2.3.3 from https://github.com/magento/magento2-sample-data"
        git clone https://github.com/magento/magento2-sample-data /magento2-sample-data || error_exit "Failed to clone sample data"
        cd /magento2-sample-data
        git checkout 2.3.3 || error_exit "Failed to checkout sample data Branch"
        php -f /magento2-sample-data/dev/tools/build-sample-data.php -- --ce-source="/opt/bitnami/magento/htdocs/" || error_exit "Failed to install sample data"
    fi

    # Rebuild cache and classes
    chown -R bitnami:daemon /magento2-sample-data/pub/media/catalog
    php /opt/bitnami/magento/htdocs/bin/magento setup:upgrade || error_exit "Failed to perform Magento Upgrade"
    #php /opt/bitnami/magento/htdocs/bin/magento setup:di:compile
    php /opt/bitnami/magento/htdocs/bin/magento cache:flush || error_exit "Failed to flush Cache"

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
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/seamless "$SHOP_PGC_SEAMLESS" || :
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

    echo -e "Flushing Cache"

    php /opt/bitnami/magento/htdocs/bin/magento cache:flush || error_exit "Failed to flush Cache"

    chown -R bitnami:daemon /opt/bitnami/magento/htdocs
    chmod -R 775 /opt/bitnami/magento/htdocs
    chmod -R 777 /opt/bitnami/magento/htdocs/pub/media
    chmod -R 777 /magento2-sample-data

    touch /setup_complete

    echo -e "Setup Complete! You can access the instance at: ${MAGENTO_HOST}"
    
    if [ $PRECONFIGURE ]; then
        exit 0
    else
        # Keep script Running
        trap : TERM INT; (while true; do sleep 1m; done) & wait
    fi

else

    # Flush Cache on startup
    sleep 30s
    php /opt/bitnami/magento/htdocs/bin/magento cache:flush

    # Keep script Running
    trap : TERM INT; (while true; do sleep 1m; done) & wait

fi
