<?php

function isHostValid(string $host): bool
{
    return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
}
