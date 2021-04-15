#!/usr/bin/env bash

# set -x

source /utils.sh

as_daemon rm -rf /bitnami/magento/app/code/Pgc
as_daemon mkdir -p /bitnami/magento/app/code/Pgc

if [ ! -d "/paymentgatewaycloud/Pgc" ]; then
    apt-get update && apt-get install git -y
    echo -e "Installing PGC Extension"
    echo -e "Checking out branch ${BRANCH} from ${REPOSITORY}"
    git clone -b $BRANCH $REPOSITORY /paymentgatewaycloud
fi

as_daemon cp -rf /paymentgatewaycloud/Pgc /bitnami/magento/app/code/Pgc/Pgc

cd /bitnami/magento
as_daemon bin/magento module:enable Pgc_Pgc
as_daemon bin/magento setup:upgrade
as_daemon bin/magento setup:di:compile
