<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pgc\Pgc\Observer\DataAssignObserver;

class PaymentDataBuilder implements BuilderInterface
{
    const TRANSACTION_TOKEN = 'transactionToken';

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $paymentJsToken = $payment->getAdditionalInformation(
            DataAssignObserver::TRANSACTION_TOKEN
        );

        if (!empty($paymentJsToken)) {
            return [
                self::TRANSACTION_TOKEN => $paymentJsToken
            ];
        }

        return [];
    }
}
