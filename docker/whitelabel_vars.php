<?php
$operation = $argv[1] ?? null;
$name = $argv[2] ?? null;

if (empty($name)) {
    line('Name must not be empty');
    usage();
    exit;
}

if (empty($operation)) {
    line('Operation must not be empty');
    usage();
    exit;
}

switch ($operation) {
    case 'snakeCase':
        line(snakeCase($name));
        break;
    case 'pascalCase':
        line(pascalCase($name));
        break;
    case 'camelCase':
        line(camelCase($name));
        break;
    case 'identifierCase':
        line(identifierCase($name));
        break;
    case 'kebabCase':
        line(kebabCase($name));
        break;
    case 'constantCase':
        line(constantCase($name));
        break;                                                                       
    default:
        line($name);
        break;
}

/**
 * Helper functions below
 */

/**
 * print usage info
 */
function usage()
{
    warn('Usage: php build.php [operation] [name]');
    line('Example: php build.php snakeCase "My Payment Provider"');
    line();
}

/**
 * @param string $string
 * @return string
 */
function camelCase($string)
{
    return lcfirst(pascalCase($string));
}

/**
 * @param string $string
 * @return string
 */
function kebabCase($string)
{
    return str_replace('_', '-', snakeCase($string));
}

/**
 * @param string $string
 * @return string
 */
function pascalCase($string)
{
    return ucfirst(str_replace(' ', '', ucwords(strtolower(preg_replace('/^a-z0-9]+/', ' ', $string)))));
}

/**
 * @param string $string
 * @return string
 */
function snakeCase($string)
{
    return strtolower(str_replace(' ', '_', ucwords(preg_replace('/^a-z0-9]+/', ' ', $string))));
}

/**
 * @param string $string
 * @return mixed
 */
function identifierCase($string)
{
    return strtolower(pascalCase($string));
}

/**
 * @param string $string
 * @return mixed
 */
function constantCase($string)
{
    return strtoupper(snakeCase($string));
}

/**
 * @param null $message
 */
function line($message = null)
{
    echo $message . "\n";
}

/**
 * do not allow to reference a directory outside of this script's path
 *
 * @param string $dir
 * @return string
 */
function sanitizeDirInput($dir)
{
    return ltrim(str_replace('../', '', $dir), '/');
}