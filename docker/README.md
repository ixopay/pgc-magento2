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
 # MAGENTO_PASSWORD must contain a number and hast to be at least 8 chars long
 REPOSITORY="https://github.com/ixopay/pgc-magento2" \
 BRANCH="master" \
 MAGENTO_HOST="127.0.0.1" \
 MAGENTO_USERNAME="dev" \
 MAGENTO_PASSWORD="develop1" \
  docker-compose -f docker-compose.github.yml up --build --force-recreate --renew-anon-volumes
```

To develop and test plugin changes, you can run the following docker-compose command from the plugin root directory, to start a Magento shop & initialize a database with a bind mounted version of the plugin. The shop will be accessible via: `http://127.0.0.1/admin`.

```bash
 # MAGENTO_PASSWORD must contain a number and hast to be at least 8 chars long
 BITNAMI_IMAGE_VERSION="latest" \
 MAGENTO_HOST="127.0.0.1" \
 MAGENTO_USERNAME="dev" \
 MAGENTO_PASSWORD="develop1" \
  docker-compose up --build --force-recreate --renew-anon-volumes
```

With this setup your source coude is linked into the container, you should be able to see source changes directly in the running instance.
If you oerformed drastical changes it might be required to flush the cache and regenerate magento classes to apply the changes, you can do this with following command:

```bash
docker-compose exec --user bitnami magento /opt/bitnami/php/bin/php /opt/bitnami/magento/htdocs/bin/magento setup:upgrade
```


To test a build you generated via build.php run the following command from the plugin root directory:

```bash
 # MAGENTO_PASSWORD must contain a number and hast to be at least 8 chars long
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
 SHOP_PGC_SEAMLESS="1"
```
