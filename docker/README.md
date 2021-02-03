# Docker demo & development environment

We supply ready to use Docker environments for plugin development & testing.

- `docker-compose.yml` Setup Magento2 instance and configure Pgc Extension (Testing)
- `docker-compose.dev.yml` Setup Magento2 instance with Pgc Extension, but without Configuration (Development)

**Warning!** This docker image is dedicated for development & demo usage, we don't recommended to use it in production.

---

## Usage

Run Development Environment
```
docker-compose -f docker-compose.dev.yml up --force-recreate --renew-anon-volumes

# Reload Extension Source Code in Magento:
docker-compose -f docker-compose.dev.yml exec magento /bin/bash -c "php /opt/bitnami/magento/bin/magento module:enable Pgc_Pgc --clear-static-content"
docker-compose -f docker-compose.dev.yml exec magento /bin/bash -c "php /opt/bitnami/magento/bin/magento setup:upgrade"
docker-compose -f docker-compose.dev.yml exec magento /bin/bash -c "php /opt/bitnami/magento/bin/magento setup:di:compile"
docker-compose -f docker-compose.dev.yml exec magento /bin/bash -c "php /opt/bitnami/magento/bin/magento cache:flush"
```

Run Test Environment
```
docker-compose up --force-recreate --renew-anon-volumes
```


## Configuration

Settings can be supplied as Environment Variables inside the docker-compose file.


| Value                    |           Default            |                       Description                       |
| ------------------------ |:----------------------------:|:-------------------------------------------------------:|
| HTTPS                    |            false             |                  Enable/Disable HTTPS                   |
| REPOSITORY               | https://github.com/user/repo | URL to the Repo where your branded Extension is located |
| Branch                   |            master            |        Which Branch to checkout from REPOSITORY         |
| MAGENTO_PASSWORD         |           bitnami1           |                 Default Admin Password                  |
| MAGENTO_USERNAME         |             user             |                 Default Admin Username                  |
| DEMO_CUSTOMER_PASSWORD   |           customer           |                  Default User Password                  |
| SHOP_PGC_URL             |           sandbox            |                 URL to your Gateway API                 |
| SHOP_PGC_USER            |          test-user           |                    Your Gateway User                    |
| SHOP_PGC_PASSWORD        |          test-pass           |               Your Gateway User Password                |
| SHOP_PGC_API_KEY         |             key              |                  Your Gateway API-Key                   |
| SHOP_PGC_SECRET          |            secret            |                 Your Gateway API-Secret                 |
| SHOP_PGC_INTEGRATION_KEY |           int_key            |              Your Gateway Integration Key               |
| SHOP_PGC_SEAMLESS        |              -1              |     Whether to Enable (1) or Disable (-1) Seamless      |


## Default Credentials:

### User / Customer

> **Login:** RobertZJohnson@einrot.com
>
> **Password:** customer

### Admin

> **Login:** user
>
> **Password:** bitnami1
