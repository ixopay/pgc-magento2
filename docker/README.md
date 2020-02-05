# Docker demo & development environment

We supply ready to use Docker environments for plugin development & testing. 

**Warning!** This docker image is dedicated for development & demo usage, we don't recommended to use it in production.

---

## Usage

> **NOTE**: Elasticsearch dependency will not start successfully before you adjust `sysctl -w vm.max_map_count=262144` for your local Docker engine.
> The following Stackoverflow article should explain steps for multiple Docker engines:
> https://stackoverflow.com/questions/41192680/update-max-map-count-for-elasticsearch-docker-container-mac-host

To quickly spawn a Magento test shop with a plugin tagged at our Github repository:
Clone our plugin repository and run the following command from the plugin root directory:

```bash
 # MAGENTO_PASSWORD must conatin a numeral consist of 8+ chars
 REPOSITORY="https://github.com/ixopay/pgc-magento2" \
 BRANCH="master" \
 MAGENTO_HOST="127.0.0.1" \
 MAGENTO_USERNAME="dev" \
 MAGENTO_PASSWORD="develop1" \
  docker-compose -f docker-compose.github.yml up --build --force-recreate --renew-anon-volumes
```

To develop and test plugin changes, you can run the following docker-compose command from the plugin root directory, to start a Magento shop & initialize a database with a bind mounted version of the plugin. The shop will be accessible via: `http://127.0.0.1/admin`.

```bash
 # MAGENTO_PASSWORD must conatin a numeral consist of 8+ chars
 BITNAMI_IMAGE_VERSION="latest" \
 MAGENTO_HOST="127.0.0.1" \
 MAGENTO_USERNAME="dev" \
 MAGENTO_PASSWORD="develop1" \
  docker-compose up --build --force-recreate --renew-anon-volumes
```

To test a build you generated via build.php run the following command from the plugin root directory:

```bash
 # MAGENTO_PASSWORD must conatin a numeral consist of 8+ chars
 php build.php sandbox.paymentgateway.cloud "My Payment Provider"
 BITNAMI_IMAGE_VERSION="latest" \
 BUILD_ARTIFACT="${PWD}/dist/magento2-my-payment-provider-1.0.0.zip" \
 MAGENTO_HOST="127.0.0.1" \
 MAGENTO_USERNAME="dev" \
 MAGENTO_PASSWORD="develop1" \
  docker-compose up --build --force-recreate --renew-anon-volumes
```

Please note:

- By running the command we always run a complete `--build` for the shop container, `--force-recreate` to delete previous containers and always delete the previous instance's storage volumes via `--renew-anon-volumes`. We don't support to change variables without rebuilding the full container.
- We currently use Bitnami Docker images as base for the environment and add our plugin.
- Further environment variables can be set, please take a look at `docker/Dockerfile` for a complete list.

### Customize Settings

Defaults for the Docker build are configured in the `docker-compose` files. You can either:
 - set variables via environment variable or (like above)
 - persist them in the `environment:` section of the respective docker-compose file.

### Platform credentials

To successfully test a payment flow you will need merchant credentials for the payment platform and set them via the following environment variables:

> These Options are ignored when using an pre-generated zip-file!
> Please Configure the Payment-Settings via the Admin-Interface (e.g.: https://127.0.0.1/admin)

```bash
 # Base url for payment plaform API
 SHOP_PGC_URL="https://sandbox.paymentgateway.cloud"
 # Credentials for payment platform API
 SHOP_PGC_USER="test-user"
 SHOP_PGC_PASSWORD="test-pass"
 SHOP_PGC_API_KEY="key"
 SHOP_PGC_SECRET="secret"
 SHOP_PGC_INTEGRATION_KEY="int-key"
```

Additional platform specific settings:

```bash
 # Enable or disable payments for specific schemes
 SHOP_PGC_CC_AMEX="True"
 SHOP_PGC_CC_DINERS="True"
 SHOP_PGC_CC_DISCOVER="True"
 SHOP_PGC_CC_JCB="True"
 SHOP_PGC_CC_MAESTRO="True"
 SHOP_PGC_CC_MASTERCARD="True"
 SHOP_PGC_CC_UNIOPNPAY="True"
 SHOP_PGC_CC_VISA="True"
 # Either use "debit" or "preauthorize" transaction requests
 SHOP_PGC_CC_TYPE="debit"
 SHOP_PGC_CC_TYPE_AMEX="debit"
 SHOP_PGC_CC_TYPE_DINERS="debit"
 SHOP_PGC_CC_TYPE_DISCOVER="debit"
 SHOP_PGC_CC_TYPE_JCB="debit"
 SHOP_PGC_CC_TYPE_MAESTRO="debit"
 SHOP_PGC_CC_TYPE_MASTERCARD="debit"
 SHOP_PGC_CC_TYPE_UNIOPNPAY="debit"
 SHOP_PGC_CC_TYPE_VISA="debit"
 ```