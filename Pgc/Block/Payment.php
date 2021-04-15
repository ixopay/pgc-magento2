<?php

namespace Pgc\Pgc\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pgc\Pgc\Model\Ui\ConfigProvider;

/**
 * Class Payment
 */
class Payment extends Template
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigProvider $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getPaymentConfig(): string
    {
        $payment = $this->config->getConfig()['payment'];
        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();
        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return ConfigProvider::CREDITCARD_CODE;
    }
}
