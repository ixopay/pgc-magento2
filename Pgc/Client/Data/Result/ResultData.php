<?php


namespace Pgc\Client\Data\Result;

/**
 * Class ResultData
 *
 * @package Pgc\Client\Data\Result
 */
abstract class ResultData {

    /**
     * @return array
     */
    abstract public function toArray();

}
