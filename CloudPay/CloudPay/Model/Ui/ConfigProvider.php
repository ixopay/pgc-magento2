<?php

namespace CloudPay\CloudPay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CREDITCARD_CODE = 'cloudpay_creditcard';

    /**
     * @var \CloudPay\CloudPay\Helper\Data
     */
    private $cloudPayHelper;

    public function __construct(\CloudPay\CloudPay\Helper\Data $cloudPayHelper)
    {
        $this->cloudPayHelper = $cloudPayHelper;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                static::CREDITCARD_CODE => [
                    'seamless' => $this->cloudPayHelper->getPaymentConfigDataFlag('seamless', static::CREDITCARD_CODE),
                    'integration_key' => $this->cloudPayHelper->getPaymentConfigDataFlag('integration_key', static::CREDITCARD_CODE)
                ]
            ],
        ];
    }
}
