<?php

namespace Pgc\Pgc\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data as Helper;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\MethodInterface;
use Pgc\Pgc\Gateway\Config\Config as GatewayConfig;
use Pgc\Pgc\Model\Ui\ConfigProvider;

class Form extends Cc
{
    /**
     * @var GatewayConfig
     */
    protected GatewayConfig $gatewayConfig;

    /**
     * @var Helper
     */
    private Helper $paymentDataHelper;

    /**
     * Form constructor.
     * @param Context $context
     * @param Config $paymentConfig
     * @param GatewayConfig $gatewayConfig
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        GatewayConfig $gatewayConfig,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->gatewayConfig = $gatewayConfig;
        $this->paymentDataHelper = $helper;
    }

    /**
     * @return bool
     */
    public function useCcv(): bool
    {
        return true;
        //$this->gatewayConfig->isCcvEnabled();
    }

    /**
     * Check if vault enabled
     * @return bool
     * @throws LocalizedException
     */
    public function isVaultEnabled(): bool
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * Get configured vault payment for payment
     * @return MethodInterface
     * @throws LocalizedException
     */
    private function getVaultPayment(): MethodInterface
    {
        return $this->paymentDataHelper->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }
}
