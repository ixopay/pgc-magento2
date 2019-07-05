<?php

namespace CloudPay\CloudPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public function getGeneralConfigData($field, $storeId = null)
    {
        return $this->getConfigData($field, 'cloudpay/general', $storeId);
    }

    public function getGeneralConfigDataFlag($field, $storeId = null)
    {
        return $this->getConfigData($field, 'cloudpay/general', $storeId, true);
    }

    public function getPaymentConfigData($field, $paymentMethodCode, $storeId = null)
    {
        return $this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId);
    }

    public function getPaymentConfigDataFlag($field, $paymentMethodCode, $storeId = null)
    {
        return $this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId, true);
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param $field
     * @param $path
     * @param $storeId
     * @param bool|false $flag
     * @return bool|mixed
     */
    public function getConfigData($field, $path, $storeId = null, $flag = false)
    {
        $path .= '/' . $field;

        if (!$flag) {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }
}
