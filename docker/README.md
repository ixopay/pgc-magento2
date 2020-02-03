**Warning!** This docker image is dedicated for demo usage, we don't recommended to use it in production.

---

# USAGE

> **NOTE**: Elasticsearch dependency will not start successfully before you adjust `sysctl -w vm.max_map_count=262144` for your local Docker engine.
> The following Stackoverflow article should explain steps for multiple Docker engines:
> https://stackoverflow.com/questions/41192680/update-max-map-count-for-elasticsearch-docker-container-mac-host

To quickly spawn a Magento test shop with a plugin tagged at our github.com repository:

```
 REPOSITORY="https://github.com/ixopay/pgc-magento2" \
 BRANCH="master" \
 MAGENTO_HOST="localhost" \
 MAGENTO_USERNAME=dev \
 MAGENTO_PASSWORD=dev \
  docker-compose -f docker-compose.github.yml up --build --force-recreate --renew-anon-volumes
```

To develop and test plugin changes, you can run the following docker-compose command from the plugin root directory, to start a Magento shop &
initialize a database with a bind mounted version of the plugin. The shop will be accessible via: `http://localhost/admin`.

```
 BITNAMI_IMAGE_VERSION=latest \
 MAGENTO_HOST="localhost" \
 MAGENTO_USERNAME=dev \
 MAGENTO_PASSWORD=dev \
  docker-compose up --build --force-recreate --renew-anon-volumes
```

By running the command we always run a complete `--build` for the shop container, `--force-recreate` to delete previous containers  and always delete
the previous instance's storage volumes via `--renew-anon-volumes`. We currently use Bitnami Docker images as base for the environment and add our plugin.
Further environment variables can be set, please take a look at `docker/Dockerfile` for a complete list.

## Platform credentials

To successfully test a payment flow you will need merchant credentials for the payment platform and set them via the following environment variables:

```
 SHOP_PGC_URL="https://sandbox.paymentgateway.cloud"
 SHOP_PGC_USER="test-user"
 SHOP_PGC_PASSWORD="test-pass"
 SHOP_PGC_API_KEY="key"
 SHOP_PGC_SECRET="secret"
 SHOP_PGC_INTEGRATION_KEY="intkey"
```