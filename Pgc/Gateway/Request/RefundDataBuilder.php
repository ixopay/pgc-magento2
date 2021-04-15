<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Helper\Data;

class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var Data
     */
    protected Data $_helper;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * RefundDataBuilder constructor.
     *
     * @param ConfigInterface $config
     * @param Data $helper
     */
    public function __construct(
        ConfigInterface $config,
        Data $helper
    ) {
        $this->config = $config;
        $this->_helper = $helper;
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
        $amount = SubjectReader::readAmount($buildSubject);

        return [
            'merchantTransactionId' => $order->getOrderIncrementId() . '-refund-' . date('Y-m-d'),
            'amount' => $this->formatPrice($amount),
            'currency' => $order->getCurrencyCode(),
            'referenceUuid' => str_replace('-refund', '', $pspReference),
            'callbackUrl' => $this->_helper->getCallbackUrl(),
        ];
    }
}
