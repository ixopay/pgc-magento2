<?php

namespace Pgc\Pgc\Gateway\Request;

use LogicException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;

class VoidRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDO */
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $pspReference = $payment->getTransactionId();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new LogicException('Order payment should be provided.');
        }

        return [
            'merchantTransactionId' => $order->getOrderIncrementId() . '-void-' . date('Y-m-d'),
            'referenceUuid' => str_replace('-void', '', $pspReference)
        ];
    }
}
