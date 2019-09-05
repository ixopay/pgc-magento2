<?php

namespace Pgc\Client\Transaction;

use Pgc\Client\Transaction\Base\AbstractTransaction;
use Pgc\Client\Transaction\Base\AddToCustomerProfileInterface;
use Pgc\Client\Transaction\Base\AddToCustomerProfileTrait;
use Pgc\Client\Transaction\Base\OffsiteInterface;
use Pgc\Client\Transaction\Base\OffsiteTrait;
use Pgc\Client\Transaction\Base\ScheduleInterface;
use Pgc\Client\Transaction\Base\ScheduleTrait;

/**
 * Register: Register the customer's payment data for recurring charges.
 *
 * The registered customer payment data will be available for recurring transaction without user interaction.
 *
 * @package Pgc\Client\Transaction
 */
class Register extends AbstractTransaction implements OffsiteInterface, ScheduleInterface, AddToCustomerProfileInterface {
    use OffsiteTrait;
    use ScheduleTrait;
    use AddToCustomerProfileTrait;
}
