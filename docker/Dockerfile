FROM bitnami/magento:2.3.1

EXPOSE 80

VOLUME /bitnami
VOLUME /source
VOLUME /dist

ENV PRECONFIGURE="" \
    WHITELABEL="" \
    BUILD_ARTIFACT="undefined" \
    SHOP_PGC_URL="sandbox" \
    SHOP_PGC_USER="test-user" \
    SHOP_PGC_PASSWORD="test-pass" \
    SHOP_PGC_API_KEY="key" \
    SHOP_PGC_SECRET="secret" \
    SHOP_PGC_SEAMLESS="1" \
    SHOP_PGC_INTEGRATION_KEY="intkey" \
    REPOSITORY="https://github.com/user/repo" \
    BRANCH="master"

RUN apt-get update && apt-get install --no-install-recommends -y \
  git \
  unzip \
  && apt-get autoclean

COPY start.sh /start.sh
COPY whitelabel_vars.php /whitelabel.php

ENTRYPOINT ["/start.sh"]
