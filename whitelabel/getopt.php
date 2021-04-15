<?php

require_once(__DIR__ . '/term.php');

function wantsHelp(): bool
{
    $opts = getopt('h', ['help']);

    return isset($opts['h']) || isset($opts['help']);
}

function wantsDebug(): bool
{
    $opts = getopt('d', ['debug']);

    return isset($opts['d']) || isset($opts['debug']);
}

function getProdHost(): string
{
    $opts = getopt('', ['production-host:']);

    if(empty($opts['production-host']) || ! is_string($opts['production-host'])) {
        warn('missing/invalid --production-host');
        exit;
    }

    return $opts['production-host'];
}

function getSandboxHost(): string
{
    $opts = getopt('', ['sandbox-host:']);

    if(empty($opts['sandbox-host']) || ! is_string($opts['sandbox-host'])) {
        warn('missing/invalid --sandbox-host');
        exit;
    }

    return $opts['sandbox-host'];
}

function getVaultHost(): string
{
    $opts = getopt('', ['vault-host:']);

    if(empty($opts['vault-host']) || ! is_string($opts['vault-host'])) {
        warn('missing/invalid --vault-host');
        exit;
    }

    return $opts['vault-host'];
}

function getVendorName(): string
{
    $opts = getopt('', ['vendor-name:']);

    if(empty($opts['vendor-name']) || ! is_string($opts['vendor-name'])) {
        warn('missing/invalid --vendor-name');
        exit;
    }

    return $opts['vendor-name'];
}

function getPackageName(): string
{
    $opts = getopt('', ['package-name:']);

    if(empty($opts['package-name']) || ! is_string($opts['package-name'])) {
        warn('missing/invalid --package-name');
        exit;
    }

    return $opts['package-name'];
}
