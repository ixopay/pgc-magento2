<?php

namespace CloudPay\Client\Transaction;

use CloudPay\Client\Transaction\Base\AbstractTransactionWithReference;
use CloudPay\Client\Transaction\Base\AmountableInterface;
use CloudPay\Client\Transaction\Base\AmountableTrait;
use CloudPay\Client\Transaction\Base\ItemsInterface;
use CloudPay\Client\Transaction\Base\ItemsTrait;

/**
 * Capture: Charge a previously preauthorized transaction.
 *
 * @package CloudPay\Client\Transaction
 */
class Capture extends AbstractTransactionWithReference implements AmountableInterface, ItemsInterface {
    use AmountableTrait;
    use ItemsTrait;
}
