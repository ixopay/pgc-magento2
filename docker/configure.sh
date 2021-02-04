#!/usr/bin/env bash
# set -x
set -euo pipefail

error_exit() {
    echo "$1" 1>&2
    exit 1
}

echo -e "Installing requirements"

apt-get update && apt-get install git -y

echo -e "Installing PGC Extension"

mkdir -p /bitnami/magento/app/code/
echo -e "Checking out branch ${BRANCH} from ${REPOSITORY}"
curl -LJ "${REPOSITORY}/archive/${BRANCH}.tar.gz" | tar -xz --strip-components=1 --directory=/tmp/paymentgatewaycloud
cd /tmp/paymentgatewaycloud
cp -R /tmp/paymentgatewaycloud/* /bitnami/magento/app/code/
chown -R daemon:root /bitnami/magento/app

php /bitnami/magento/bin/magento module:enable Pgc_Pgc || error_exit "Failed to activate Extension!"

echo -e "Import Sample Products"

if [ ! -d "/magento2-sample-data" ]; then
    echo -e "Checking out branch 2.4.1 from https://github.com/magento/magento2-sample-data"
    git clone -b 2.4.1 https://github.com/magento/magento2-sample-data /magento2-sample-data || error_exit "Failed to clone sample data"
    cd /magento2-sample-data
    php -f /magento2-sample-data/dev/tools/build-sample-data.php -- --ce-source="/bitnami/magento/" || error_exit "Failed to install sample data"
fi

# Rebuild cache and classes
php /bitnami/magento/bin/magento setup:upgrade
chown -R daemon:root /magento2-sample-data/pub/media/catalog
php /bitnami/magento/bin/magento setup:di:compile

echo -e "Configuring Magento"

# Disable other payment Providers
php /bitnami/magento/bin/magento config:set payment/amazonlogin/active 0
php /bitnami/magento/bin/magento config:set payment/amazon_payment/active 0
php /bitnami/magento/bin/magento config:set payment/klarna_kp/active 0
php /bitnami/magento/bin/magento config:set payment/payflow_link/active 0
php /bitnami/magento/bin/magento config:set payment/wps_express/active 0
php /bitnami/magento/bin/magento config:set payment/payflowpro/active 0
php /bitnami/magento/bin/magento config:set payment/payflowpro_cc_vault/active 0
php /bitnami/magento/bin/magento config:set payment/payflow_express/active 0
php /bitnami/magento/bin/magento config:set payment/payflow_advanced/active 0
php /bitnami/magento/bin/magento config:set payment/braintree_cc_vault/active 0
php /bitnami/magento/bin/magento config:set payment/braintree_paypal/active 0
php /bitnami/magento/bin/magento config:set payment/braintree/active 0
php /bitnami/magento/bin/magento config:set payment/paypal_billing_agreement/active 0
php /bitnami/magento/bin/magento config:set payment/paypal_express/active 0

echo -e "Configuring Extension"

# Enable PGC Payment Providers
if [ $SHOP_PGC_URL ]; then
    php /bitnami/magento/bin/magento config:set pgc/general/username "$SHOP_PGC_USER"
    php /bitnami/magento/bin/magento config:set pgc/general/password "$SHOP_PGC_PASSWORD"
    php /bitnami/magento/bin/magento config:set pgc/general/host "$SHOP_PGC_URL"

    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/active 1
    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/api_key "$SHOP_PGC_API_KEY"
    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/shared_secret "$SHOP_PGC_SECRET"
    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/integration_key "$SHOP_PGC_INTEGRATION_KEY"
    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/sort_order 1
    php /bitnami/magento/bin/magento config:set payment/pgc_creditcard/seamless "$SHOP_PGC_SEAMLESS"
fi

# Fix Transaction ID
UNIX_TIMESTAMP=$(date +'%s')
mysql -B -h mariadb -u root bitnami_magento -e "INSERT INTO \`sequence_order_1\` SET sequence_value = '${UNIX_TIMESTAMP}';"

# Demo Customer
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
php /bitnami/magento/bin/magento config:set web/secure/use_in_frontend 0
if [ $HTTPS ]; then
    php /bitnami/magento/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
    php /bitnami/magento/bin/magento config:set web/secure/base_url "https://${MAGENTO_HOST}/"
    php /bitnami/magento/bin/magento config:set web/secure/use_in_adminhtml 1
else
    php /bitnami/magento/bin/magento config:set web/unsecure/base_url "http://${MAGENTO_HOST}/"
    php /bitnami/magento/bin/magento config:set web/secure/base_url "http://${MAGENTO_HOST}/"
    php /bitnami/magento/bin/magento config:set web/secure/use_in_adminhtml 0
fi

echo -e "Flushing Cache"

php /bitnami/magento/bin/magento cache:flush

chown -R daemon:root /bitnami/magento/var
chmod -R 775 /bitnami/magento/var

echo -e "Setup Complete! You can access the instance at: ${MAGENTO_HOST}"

