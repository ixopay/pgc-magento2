#!/bin/bash
set -euo pipefail

error_exit() {
    echo "$1" 1>&2
	exit 1
}

fix_permissions() {
    find /opt/bitnami/magento/htdocs -type f -not -path "/opt/bitnami/magento/htdocs/app/code/*" -exec chmod 644 {} \;
    find /opt/bitnami/magento/htdocs -type d -not -path "/opt/bitnami/magento/htdocs/app/code/*" -exec chmod 755 {} \;
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

    fix_symlink /opt/bitnami/magento/htdocs /bitnami/magento/htdocs
    fix_symlink /opt/bitnami/magento/htdocs/app/design /bitnami/magento/htdocs/app/design || :
    fix_symlink /opt/bitnami/magento/htdocs/app/code /bitnami/magento/htdocs/app/code || :

    echo -e "Installing PGC Extension"

    DB_FIELD_NAME="pgc"
    mkdir -p /opt/bitnami/magento/htdocs/app/code/
    if [ "${BUILD_ARTIFACT}" != "undefined" ]; then
        if [ -f /dist/paymentgatewaycloud.zip ]; then
            fix_symlink /opt/bitnami/magento/htdocs/app/code /bitnami/magento/htdocs/app/code || :
            if [ ! -d "/opt/bitnami/magento/htdocs/app/code" ]; then
                mkdir /opt/bitnami/magento/htdocs/app/code
            fi
            echo -e "Using Supplied zip ${BUILD_ARTIFACT}"
            ZIP_NAME=$(basename "${BUILD_ARTIFACT}") || error_exit "Failed to get ZIP Name"
            mkdir /tmp/source
            unzip /dist/paymentgatewaycloud.zip -d /tmp/source
            cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
        else
            error_exit "Faled to build!, there is no such file: ${BUILD_ARTIFACT}"
        fi
    else
        if [ ! -d "/opt/bitnami/magento/htdocs/app/code/Pgc" ] && [ ! -f  "/opt/bitnami/magento/htdocs/app/code/Pgc" ]; then
            fix_symlink /opt/bitnami/magento/htdocs/app/code /bitnami/magento/htdocs/app/code || :
            if [ ! -d "/opt/bitnami/magento/htdocs/app/code" ]; then
                mkdir /opt/bitnami/magento/htdocs/app/code
            fi
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
            if [ ! -z "${WHITELABEL}" ]; then
                echo -e "Whitelabeling for Magento not Supported yet!"
                #echo -e "Running Whitelabel Script for ${WHITELABEL}"
                #echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" || error_exit "Failed to run Whitelabel Script for '$WHITELABEL'"
                #DEST_FILE="$(echo "y" | php build.php "gateway.mypaymentprovider.com" "${WHITELABEL}" | tail -n 1 | sed 's/.*Created file "\(.*\)".*/\1/g')"
                #DB_FIELD_NAME="$(php /whitelabel.php snakeCase "${WHITELABEL}")" || error_exit "Failed to extract DB-Field Name"
                #unzip "${DEST_FILE}" -d /tmp/source || error_exit "Failed to extract ZIP"
                #cp -R /tmp/source/* /opt/bitnami/magento/htdocs/app/code/
            else
                echo -e "Nothing to do."
            fi
        fi
    fi
    chown -R bitnami:daemon /opt/bitnami/magento/htdocs

    rm -rf /opt/bitnami/magento/htdocs/generated/code/Magento
    
    chmod -R 777 /opt/bitnami/magento/htdocs/pub/media
    fix_permissions

    php /opt/bitnami/magento/htdocs/bin/magento module:enable Pgc_Pgc --clear-static-content || error_exit "Failed to enable Extension"
    php /opt/bitnami/magento/htdocs/bin/magento setup:di:compile || error_exit "Failed to compile Magento Classes"

    echo -e "Import Products"

    if [ ! -d "/magento2-sample-data" ]; then
        echo -e "Checking out branch 2.3.5 from https://github.com/magento/magento2-sample-data"
        git clone https://github.com/magento/magento2-sample-data /magento2-sample-data || error_exit "Failed to clone sample data"
        cd /magento2-sample-data
        git checkout 2.3.5 || error_exit "Failed to checkout sample data Branch"
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
        php /opt/bitnami/magento/htdocs/bin/magento config:set pgc/general/password "${SHOP_PGC_PASSWORD}"
        php /opt/bitnami/magento/htdocs/bin/magento config:set pgc/general/host "$SHOP_PGC_URL"

        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/active 1
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/api_key "$SHOP_PGC_API_KEY"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/shared_secret "$SHOP_PGC_SECRET"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/integration_key "$SHOP_PGC_INTEGRATION_KEY"
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/sort_order 1
        php /opt/bitnami/magento/htdocs/bin/magento config:set payment/pgc_creditcard/seamless "$SHOP_PGC_SEAMLESS" || :
    fi

    # Fix Transaction ID
    UNIX_TIMESTAMP=$(date +'%s')
    mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`sequence_order_1\` SET sequence_value = '${UNIX_TIMESTAMP}';"

    if [ $DEMO_CUSTOMER_PASSWORD ]; then
        echo -e "Creating Demo Customer"
        # Create Customer Entity
        DEMO_USER_ID=$(mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`customer_entity\` (website_id, gender, dob, email, group_id, store_id, created_at, updated_at, is_active, created_in, firstname, lastname, password_hash, rp_token, rp_token_created_at, failures_num) VALUES (1, 1, '1991-11-05', 'RobertZJohnson@einrot.com', 1, 1, NOW(), NOW(), 1, 'Default Store View', 'Johnson', 'Robert Z.', CONCAT(SHA2('xxxxxxxx${DEMO_CUSTOMER_PASSWORD}', 256), ':xxxxxxxx:1'), '', NOW(), 0); SELECT LAST_INSERT_ID();" | tail -n1)

        # Create Address Entity
        DEMO_ADDRESS_ID=$(mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`customer_address_entity\` (parent_id, created_at, updated_at, is_active, city, company, country_id, firstname, lastname, postcode, region, region_id, street, telephone) VALUES (${DEMO_USER_ID}, NOW(), NOW(), 1, 'Springfield', 'Ixolit', 'US', 'Johnson', 'Robert Z.', 62703, 'Illinois', 23, '242 University Hill Road', '217-585-5994'); SELECT LAST_INSERT_ID();" | tail -n1)

        # Update Address of Customer
        mysql -B -h mariadb -u root bitnami_magento -e "UPDATE \`customer_entity\` SET default_billing=${DEMO_ADDRESS_ID}, default_shipping=${DEMO_ADDRESS_ID} WHERE entity_id = ${DEMO_USER_ID};"

        # Tell Magento about the new Customer
        mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`customer_grid_flat\` (entity_id, name, email, group_id, created_at, website_id, confirmation, created_in, dob, gender, taxvat, lock_expires, shipping_full, billing_full, billing_firstname, billing_lastname, billing_telephone, billing_postcode, billing_country_id, billing_region, billing_street, billing_city, billing_fax, billing_vat_id, billing_company) VALUES (${DEMO_USER_ID}, 'Robert Z. Johnson', 'RobertZJohnson@einrot.com', '1', NOW(), '1', NULL, 'Default Store View', '1991-11-05', 1, NULL, NULL, '242 University Hill Road Springfield Illinois 62703', '242 University Hill Road Springfield Illinois 62703', 'Robert Z.', 'Johnson', '217-585-5994', '62703', 'US', 'Illinois', '242 University Hill Road', 'Springfield', NULL, NULL, NULL) ON DUPLICATE KEY UPDATE \`name\` = VALUES(\`name\`), \`email\` = VALUES(\`email\`), \`group_id\` = VALUES(\`group_id\`), \`created_at\` = VALUES(\`created_at\`), \`website_id\` = VALUES(\`website_id\`), \`confirmation\` = VALUES(\`confirmation\`), \`created_in\` = VALUES(\`created_in\`), \`dob\` = VALUES(\`dob\`), \`gender\` = VALUES(\`gender\`), \`taxvat\` = VALUES(\`taxvat\`), \`lock_expires\` = VALUES(\`lock_expires\`), \`shipping_full\` = VALUES(\`shipping_full\`), \`billing_full\` = VALUES(\`billing_full\`), \`billing_firstname\` = VALUES(\`billing_firstname\`), \`billing_lastname\` = VALUES(\`billing_lastname\`), \`billing_telephone\` = VALUES(\`billing_telephone\`), \`billing_postcode\` = VALUES(\`billing_postcode\`), \`billing_country_id\` = VALUES(\`billing_country_id\`), \`billing_region\` = VALUES(\`billing_region\`), \`billing_street\` = VALUES(\`billing_street\`), \`billing_city\` = VALUES(\`billing_city\`), \`billing_fax\` = VALUES(\`billing_fax\`), \`billing_vat_id\` = VALUES(\`billing_vat_id\`), \`billing_company\` = VALUES(\`billing_company\`)"
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

    find /opt/bitnami/magento/htdocs -type f -not -path "/opt/bitnami/magento/htdocs/app/code/*" -exec chown -R bitnami:daemon {} \;
    find /opt/bitnami/magento/htdocs -type d -not -path "/opt/bitnami/magento/htdocs/app/code/*" -exec chmod 755 {} \;
    chmod -R 777 /opt/bitnami/magento/htdocs/generated
    chmod -R 777 /opt/bitnami/magento/htdocs/var
    chmod -R 777 /opt/bitnami/magento/htdocs/pub
    chmod -R 777 /magento2-sample-data

    touch /setup_complete
   
    if [ $PRECONFIGURE ]; then
        # Disable Login Captcha
        #php /opt/bitnami/magento/htdocs/bin/magento config:set msp_securitysuite_recaptcha/backend/enabled 0
        #php /opt/bitnami/magento/htdocs/bin/magento config:set msp_securitysuite_recaptcha/frontend/enabled 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set admin/security/use_case_sensitive_login 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set admin/captcha/enable 0 
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/online_customers/section_data_lifetime 60
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/create_account/auto_group_assign 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/create_account/viv_disable_auto_group_assign_default 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/create_account/generate_human_friendly_id 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/address/middlename_show 0
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/address/telephone_show req
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/address/company_show opt
        php /opt/bitnami/magento/htdocs/bin/magento config:set customer/captcha/enable 0
        exit 0
    else
        # Keep script Running
        if [ $MAGENTO_HOST ]; then
            echo -e "Updating Shop URL to: ${MAGENTO_HOST}"
            # Update Hostname
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}:${MAGENTO_HTTP}/"
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "http://${MAGENTO_HOST}:${MAGENTO_HTTP}/"
            if [[ "${SCHEMA}" == "https" ]]; then
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "https://${MAGENTO_HOST}:${MAGENTO_HTTPS}/"
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "https://${MAGENTO_HOST}:${MAGENTO_HTTPS}/"
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_frontend 1
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 1
            else
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_frontend 0
                php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 0
            fi
        fi

        php /opt/bitnami/magento/htdocs/bin/magento cache:flush || error_exit "Failed to flush Cache"

        echo -e "Setup Complete! You can access the instance at: ${MAGENTO_HOST}"

        trap : TERM INT; (while true; do sleep 1m; done) & wait
    fi

else
    
    if [ ! -d "/bitnami/magento" ]; then
      ln -s /opt/bitnami/magento /bitnami/magento
    fi

    if [ $MAGENTO_HOST ]; then
        echo -e "Updating Shop URL to: ${MAGENTO_HOST}"
        # Update Hostname
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
        php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "http://${MAGENTO_HOST}/"
        if [[ "${SCHEMA}" == "https" ]]; then
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/unsecure/base_url "https://${MAGENTO_HOST}/"
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/base_url "https://${MAGENTO_HOST}/"
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_frontend 1
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 1
        else
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_frontend 0
            php /opt/bitnami/magento/htdocs/bin/magento config:set web/secure/use_in_adminhtml 0
        fi
    fi

    # Fix Transaction ID
    UNIX_TIMESTAMP=$(date +'%s')
    mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`sequence_order_1\` SET sequence_value = '${UNIX_TIMESTAMP}';"

    # Flush Cache on startup
    php /opt/bitnami/magento/htdocs/bin/magento cache:flush

    # Keep script Running
    trap : TERM INT; (while true; do sleep 1m; done) & wait

fi
