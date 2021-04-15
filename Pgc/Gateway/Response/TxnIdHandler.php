<?php

namespace Pgc\Pgc\Gateway\Response;

use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class TxnIdHandler implements HandlerInterface
{
    const TXN_ID = 'uuid';
    const PAYMENT_TYPE = 'returnType';
    const RETURN_TYPE_FINISHED = 'FINISHED';
    const RETURN_TYPE_REDIRECT = 'REDIRECT';
    const REDIRECT_URL = 'redirectUrl';
    const RETURN_TYPE_HTML = 'HTML';
    const RETURN_TYPE_PENDING = 'PENDING';
    const RETURN_TYPE_ERROR = 'ERROR';
    const REDIRECT_TYPE_IFRAME = 'iframe';
    const REDIRECT_TYPE_FULLPAGE = 'fullpage';
    const SCHEDULE_STATUS_ACTIVE = 'ACTIVE';
    const SCHEDULE_STATUS_PAUSED = 'PAUSED';
    const SCHEDULE_STATUS_CANCELLED = 'CANCELLED';
    const SCHEDULE_STATUS_ERROR = 'ERROR';
    const SCHEDULE_STATUS_CREATE_PENDING = 'CREATE-PENDING';

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        /** @var Payment $payment */
        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setIsTransactionClosed($this->shouldCloseTransaction());

        if ($response[self::PAYMENT_TYPE] === self::RETURN_TYPE_REDIRECT || $response[self::PAYMENT_TYPE] === self::RETURN_TYPE_PENDING) {
            $payment->setIsTransactionPending(true);
        }

        if ($response[self::PAYMENT_TYPE] === self::RETURN_TYPE_REDIRECT) {
            $payment->setAdditionalInformation(self::REDIRECT_URL, $response[self::REDIRECT_URL]);
        }
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return false;
    }
}
