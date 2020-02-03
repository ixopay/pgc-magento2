FROM bitnami/magento:2

EXPOSE 80

VOLUME /bitnami
VOLUME /source

ENV ALLOW_EMPTY_PASSWORD="no" \
    BITNAMI_APP_NAME="magento" \
    BITNAMI_IMAGE_VERSION="2.3.3-debian-9-r30" \
    ELASTICSEARCH_HOST="" \
    ELASTICSEARCH_PORT_NUMBER="" \
    EXTERNAL_HTTPS_PORT_NUMBER="443" \
    EXTERNAL_HTTP_PORT_NUMBER="80" \
    MAGENTO_ADMINURI="admin" \
    MAGENTO_DATABASE_NAME="bitnami_magento" \
    MAGENTO_DATABASE_PASSWORD="" \
    MAGENTO_DATABASE_USER="bn_magento" \
    MAGENTO_EMAIL="user@example.com" \
    MAGENTO_FIRSTNAME="FirstName" \
    MAGENTO_HOST="127.0.0.1" \
    MAGENTO_LASTNAME="LastName" \
    MAGENTO_MODE="developer" \
    MAGENTO_PASSWORD="bitnami1" \
    MAGENTO_USERNAME="user" \
    MARIADB_HOST="mariadb" \
    MARIADB_PORT_NUMBER="3306" \
    MARIADB_ROOT_PASSWORD="" \
    MARIADB_ROOT_USER="root" \
    MYSQL_CLIENT_CREATE_DATABASE_NAME="" \
    MYSQL_CLIENT_CREATE_DATABASE_PASSWORD="" \
    MYSQL_CLIENT_CREATE_DATABASE_PRIVILEGES="ALL" \
    MYSQL_CLIENT_CREATE_DATABASE_USER="" \
    PATH="/opt/bitnami/apache/bin:/opt/bitnami/php/bin:/opt/bitnami/php/sbin:/opt/bitnami/mysql/bin:/opt/bitnami/magento/bin:$PATH" \
    PRECONFIGURE="" \
    SHOP_PGC_URL="sandbox" \
    SHOP_PGC_USER="test-user" \
    SHOP_PGC_PASSWORD="test-pass" \
    SHOP_PGC_API_KEY="key" \
    SHOP_PGC_SECRET="secret" \
    SHOP_PGC_INTEGRATION_KEY="intkey" \
    REPOSITORY="https://github.com/user/repo" \
    BRANCH="master"

RUN apt-get update && apt-get install -y \
  git \
  && apt-get autoclean

COPY start.sh /start.sh

ENTRYPOINT ["/start.sh"]
