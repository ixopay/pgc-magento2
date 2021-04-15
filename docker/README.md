# Docker-Compose

The docker-compose setup is not a direct part of the plugin. Its sole purpose is
for setting up quickly a demo or development environment.

All docker compose setups build on to of Bitnami's Magento images. Refer to
- [Bitnami](https://bitnami.com/stack/magento/containers)
- [Github](https://github.com/bitnami/bitnami-docker-magento)
- [Docker Hub](https://hub.docker.com/r/bitnami/magento/)
for further details and configuration.

Whichever docker-compose file from below may be used, the 4 most common
container interactions are.

```bash
# Start setup
docker-compose up
# Remove entire setup
docker-compose down --volumes
# Access docker container as root
docker-compose exec magento bash
# Access container as non-privileged user
docker-compose exec -u daemon -w /bitnami/magento magento bin/magento
```

Use a compose file of your choice by specifying the file via `--file` option:
`docker-compose --file /path/to/docker-compose.yml [...]`


## System requirements

Apart from docker and docker-compose, Elastic Search requires that the
`vm.max_map_count` is set to at least 262144. You can check if the limit is
properly set by running `sysctl vm.max_map_count` and set the limit according -
and if necessary - via `sudo sysctl -w vm.max_map_count=262144`.


## docker-compose.yml

Only for IXOPAY's internal use. IXOPAY developers may use this setup for
connecting the Magento demo shop with their local gateway instance via Traefik's
load-balancer.


## docker-compose.dev.yml

This setup may be used for spinning up a quick local shop from the current
tree's state. It may be used for local development.

Note: while the code is mounted into the container at
`/paymentgatewaycloud/Pgc`, changes are not automatically copied to the correct
location. You'll have to run `docker/pgc-source-code.sh` from within the
container in order to have the updated code copied at the correct location.


## docker-compose.pull.yml

This may be used for setting up a demo shop. While bootstrapping the Magento
container, the source code is checked out at `$BRANCH` from `$REPOSITORY`.
