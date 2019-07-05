<?php

namespace CloudPay\Client\Transaction;

use CloudPay\Client\Transaction\Base\AbstractTransaction;
use CloudPay\Client\Transaction\Base\AddToCustomerProfileInterface;
use CloudPay\Client\Transaction\Base\AddToCustomerProfileTrait;
use CloudPay\Client\Transaction\Base\OffsiteInterface;
use CloudPay\Client\Transaction\Base\OffsiteTrait;
use CloudPay\Client\Transaction\Base\ScheduleInterface;
use CloudPay\Client\Transaction\Base\ScheduleTrait;

/**
 * Register: Register the customer's payment data for recurring charges.
 *
 * The registered customer payment data will be available for recurring transaction without user interaction.
 *
 * @package CloudPay\Client\Transaction
 */
class Register extends AbstractTransaction implements OffsiteInterface, ScheduleInterface, AddToCustomerProfileInterface {
    use OffsiteTrait;
    use ScheduleTrait;
    use AddToCustomerProfileTrait;
}
