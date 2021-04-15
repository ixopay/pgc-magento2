<?php

namespace Pgc\Pgc\Gateway\Request;

use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Pgc\Pgc\Helper\Data;

class AuthorizationRequest implements BuilderInterface
{
    use Formatter;

    const MERCHANT_TRANSACTION_MAX_LENGTH = 50;

    const MERCHANT_TRANSACTION_ID = 'merchantTransactionId';

    const AMOUNT = 'amount';

    const CURRENCY = 'currency';

    const SUCCESS_URL = 'successUrl';

    const CANCEL_URL = 'cancelUrl';

    const ERROR_URL = 'errorUrl';

    const CALLBACK_URL = 'callbackUrl';

    const TRANSACTION_INDICATOR = 'transactionIndicator';

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
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['payment']) || !$buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $transactionIndicatorVal = $this->_helper->getTransactionIndicatorVal();

        return [
            self::MERCHANT_TRANSACTION_ID => $this->_helper->getTruncateString(
                $order->getOrderIncrementId(),
                self::MERCHANT_TRANSACTION_MAX_LENGTH
            ),
            self::AMOUNT => $this->formatPrice($order->getGrandTotalAmount()),
            self::CURRENCY => $order->getCurrencyCode(),
            self::SUCCESS_URL => $this->_helper->getSuccessUrl(),
            self::CANCEL_URL => $this->_helper->getCancelUrl(),
            self::ERROR_URL => $this->_helper->getErrorUrl(),
            self::CALLBACK_URL => $this->_helper->getCallbackUrl(),
            self::TRANSACTION_INDICATOR => $transactionIndicatorVal
        ];
    }
}
