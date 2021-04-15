<?php

function camelCase(string $string): string
{
    return lcfirst(pascalCase($string));
}

function kebabCase(string $string): string
{
    return str_replace('_', '-', snakeCase($string));
}

function pascalCase(string $string): string
{
    return ucfirst(str_replace(' ', '', ucwords(strtolower(preg_replace('/^A-Za-z0-9]+/', ' ', $string)))));
}

function snakeCase(string $string): string
{
    if(ctype_lower($string)) {
        return $string;
    }

    $string = preg_replace('/\s+/u', '', ucwords($string));

    return strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $string));
}

function identifierCase(string $string): string
{
    return strtolower(pascalCase($string));
}

function constantCase(string $string): string
{
    return strtoupper(snakeCase($string));
}
