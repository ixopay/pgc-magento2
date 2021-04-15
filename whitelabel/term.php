<?php

function line(string $msg = ''): void
{
    echo sprintf("%s\n", $msg);
}

function error(string $msg = ''): void
{
    echo sprintf("\e[0;31m[ERROR] %s\e[0m\n", $msg);
}

function warn(string $msg = ''): void
{
    echo sprintf("\e[0;33m[WARN] %s\e[0m\n", $msg);
}

function info(string $msg = ''): void
{
    echo sprintf("\e[0;36m[INFO] %s\e[0m\n", $msg);
}

function success(string $msg = ''): void
{
    echo sprintf("\e[0;32m[SUCCESS] %s\e[0m\n", $msg);
}

function highlight(string $msg = ''): void
{
    echo sprintf("\e[0;34m%s\e[0m\n", $msg);
}

function debug(string $msg = ''): void
{
    if(wantsDebug()) {
        echo sprintf("\e[1;33m[DEBUG] %s\e[0m\n", $msg);
    }
}

function prompt(string $msg): void
{
    echo sprintf("\e[0;35m%s[y/n]\e[0m\n", $msg);

    $fd = fopen('php://stdin', 'r');
    if($fd === false) {
        error('Failed reading from stdin');
        exit;
    }

    $line = fgets($fd);
    if($line === false || trim($line) !== 'y') {
        warn('Abort');
        exit;
    }

    fclose($fd);
}
