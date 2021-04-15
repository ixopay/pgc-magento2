<?php

namespace Pgc\Pgc\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Pgc\Pgc\Helper\Data;

class CardDetailsHandler implements HandlerInterface
{
    const CARD_NUMBER = 'cc_number';
    const CREDIT_CARD_CODE = 'pgc_creditcard';

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * CardDetailsHandler constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        if ($this->getSeamlessConfiguration()) {
            $paymentDO = SubjectReader::readPayment($handlingSubject);
            $payment = $paymentDO->getPayment();
            ContextHelper::assertOrderPayment($payment);
            $creditCard = $response['returnData'];
            if (!empty($creditCard)) {
                $payment->setCcLast4($creditCard->lastFourDigits);
                $payment->setCcExpMonth($creditCard->expiryMonth);
                $payment->setCcExpYear($creditCard->expiryYear);
                // set card details to additional info
                $payment->setAdditionalInformation(self::CARD_NUMBER, 'xxxx-' . $creditCard->lastFourDigits);
            }
        }
    }

    /**
     * @return bool
     */
    private function getSeamlessConfiguration(): bool
    {
        return $this->helper->getPaymentConfigDataFlag(
            'seamless',
            self::CREDIT_CARD_CODE
        );
    }
}
