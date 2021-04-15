<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class SaveCardRequest implements BuilderInterface
{
    const WITH_REGISTER = 'withRegister';

    /**
     * @param array $buildSubject
     * @return array|bool[]
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        if (!empty($data[VaultConfigProvider::IS_ACTIVE_CODE])) {
            return [
                self::WITH_REGISTER => true
            ];
        }

        return [];
    }
}
