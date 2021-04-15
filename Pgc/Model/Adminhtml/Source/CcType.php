<?php

namespace Pgc\Pgc\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as CreditCardType;

class CcType extends CreditCardType
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->getCcTypeLabelMap() as $code => $name) {
            if (in_array($code, $allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }

    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes(): array
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN', 'CUP'];
    }

    /**
     * Returns credit cards types
     *
     * @return array
     */
    public function getCcTypeLabelMap(): array
    {
        return $this->_paymentConfig->getCcTypes();
    }
}
