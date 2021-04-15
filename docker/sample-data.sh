#!/usr/bin/env bash

# set -x

source /utils.sh

as_daemon mkdir -p /bitnami/magento/app/code

apt-get update && apt-get install git -y

echo -e "Import Sample Products"
if [ ! -d "/magento2-sample-data" ]; then
    echo -e "Checking out branch 2.4.2 from https://github.com/magento/magento2-sample-data"
    git clone -b 2.4.2 https://github.com/magento/magento2-sample-data /magento2-sample-data
    chown -R daemon:root /magento2-sample-data
    cd /magento2-sample-data
    as_daemon php -f /magento2-sample-data/dev/tools/build-sample-data.php -- --ce-source="/bitnami/magento/"
fi

cd /bitnami/magento
as_daemon bin/magento setup:upgrade
