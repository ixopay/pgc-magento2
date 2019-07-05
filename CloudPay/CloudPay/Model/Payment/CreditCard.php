<?php

namespace CloudPay\CloudPay\Model\Payment;

use CloudPay\CloudPay\Model\Ui\ConfigProvider;
use Magento\Payment\Model\Method\AbstractMethod;

class CreditCard extends AbstractMethod
{
    protected $_code = ConfigProvider::CREDITCARD_CODE;

    protected $_isGateway = true;

    protected $_canUseInternal = false;
}
