<?php


namespace CloudPay\Client\Data\Result;

/**
 * Class ResultData
 *
 * @package CloudPay\Client\Data\Result
 */
abstract class ResultData {

    /**
     * @return array
     */
    abstract public function toArray();

}
