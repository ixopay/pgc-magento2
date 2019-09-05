<?php

namespace Pgc\Client\Transaction;

use Pgc\Client\Transaction\Base\AbstractTransactionWithReference;
use Pgc\Client\Transaction\Base\AmountableInterface;
use Pgc\Client\Transaction\Base\AmountableTrait;
use Pgc\Client\Transaction\Base\ItemsInterface;
use Pgc\Client\Transaction\Base\ItemsTrait;

/**
 * Capture: Charge a previously preauthorized transaction.
 *
 * @package Pgc\Client\Transaction
 */
class Capture extends AbstractTransactionWithReference implements AmountableInterface, ItemsInterface {
    use AmountableTrait;
    use ItemsTrait;
}
