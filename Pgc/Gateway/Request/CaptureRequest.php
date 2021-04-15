<?php

namespace Pgc\Pgc\Gateway\Request;

use LogicException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Helper\Data;

class CaptureRequest implements BuilderInterface
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
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $pspReference = $payment->getTransactionId();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new LogicException('Order payment should be provided.');
        }

        return [
            'merchantTransactionId' => $order->getOrderIncrementId() . '-capture-' . date('Y-m-d h:i:s'),
            'amount' => $this->formatPrice(SubjectReader::readAmount($buildSubject)),
            'referenceUuid' => str_replace('-capture', '', $pspReference),
            'currency' => $order->getCurrencyCode()
        ];
    }
}
