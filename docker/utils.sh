#!/usr/bin/env bash

# set -x

as_daemon() {
    su daemon -s /bin/bash -c "$*"
}
