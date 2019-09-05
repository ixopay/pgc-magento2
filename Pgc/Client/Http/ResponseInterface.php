<?php

namespace Pgc\Client\Http;

/**
 * Interface ResponseInterface
 *
 * @package Pgc\Client\Http
 */
interface ResponseInterface {

    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param array $config
     *
     * @return mixed
     */
    public function json(array $config = array());

}
