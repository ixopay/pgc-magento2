#!/usr/bin/env bash
set -euo pipefail

error_exit() {
    echo "$1" 1>&2
    exit 1
}

echo "Linking Source to Extension folder"

mkdir -p /bitnami/magento/app/code
cd /source_code
find . -maxdepth 1 -mindepth 1 -type d -exec ln -s /source_code/{} /bitnami/magento/app/code/{} \;
cd -

echo "Activate Extension"

php /bitnami/magento/bin/magento module:enable Pgc_Pgc || error_exit "Failed to activate Extension!"

echo "Update Magento"

php /bitnami/magento/bin/magento setup:upgrade || error_exit "Failed to upgrade magento!"

echo "Compiling PHP Classes"

php /bitnami/magento/bin/magento setup:di:compile || error_exit "Failed to compile php classes!"

echo "Flush Cache"

php /bitnami/magento/bin/magento cache:flush || error_exit "Failed to flush cache!"

echo "Fixing Permissions"

chown -R daemon:root /bitnami/magento/var
chmod -R 775 /bitnami/magento/var
