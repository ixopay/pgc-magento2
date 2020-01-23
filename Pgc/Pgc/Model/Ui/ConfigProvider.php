<?php

namespace Pgc\Pgc\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CREDITCARD_CODE = 'pgc_creditcard';

    /**
     * @var \Pgc\Pgc\Helper\Data
     */
    private $pgcHelper;

    public function __construct(\Pgc\Pgc\Helper\Data $pgcHelper)
    {
        $this->pgcHelper = $pgcHelper;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                static::CREDITCARD_CODE => [
                    'seamless' => $this->pgcHelper->getPaymentConfigDataFlag('seamless', static::CREDITCARD_CODE),
                    'integration_key' => $this->pgcHelper->getPaymentConfigData('integration_key', static::CREDITCARD_CODE)
                ]
            ],
        ];
    }
}
