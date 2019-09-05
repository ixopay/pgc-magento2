<?php

namespace Pgc\Pgc\Model\Payment;

use Pgc\Pgc\Model\Ui\ConfigProvider;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class CreditCard implements MethodInterface
{
    protected $code = ConfigProvider::CREDITCARD_CODE;

    /**
     * CreditCard constructor.
     * @param string $_code
     */
    public function __construct()
    {
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //todo add functionality later
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //todo add functionality later
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getFormBlockType()
    {
        // TODO: Implement getFormBlockType() method.
    }

    public function getTitle()
    {
        return $this->getConfiguredValue('title');
    }

    public function setStore($storeId)
    {
        // TODO: Implement setStore() method.
    }

    public function getStore()
    {
        // TODO: Implement getStore() method.
    }

    public function canOrder()
    {
        // TODO: Implement canOrder() method.
    }

    public function canAuthorize()
    {
        // TODO: Implement canAuthorize() method.
    }

    public function canCapture()
    {
        // TODO: Implement canCapture() method.
    }

    public function canCapturePartial()
    {
        // TODO: Implement canCapturePartial() method.
    }

    public function canCaptureOnce()
    {
        // TODO: Implement canCaptureOnce() method.
    }

    public function canRefund()
    {
        // TODO: Implement canRefund() method.
    }

    public function canRefundPartialPerInvoice()
    {
        // TODO: Implement canRefundPartialPerInvoice() method.
    }

    public function canVoid()
    {
        // TODO: Implement canVoid() method.
    }

    public function canUseInternal()
    {
        // TODO: Implement canUseInternal() method.
    }

    public function canUseCheckout()
    {
        // TODO: Implement canUseCheckout() method.
    }

    public function canEdit()
    {
        // TODO: Implement canEdit() method.
    }

    public function canFetchTransactionInfo()
    {
        // TODO: Implement canFetchTransactionInfo() method.
    }

    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        // TODO: Implement fetchTransactionInfo() method.
    }

    public function isGateway()
    {
        return true;
    }

    public function isOffline()
    {
        return false;
    }

    public function isInitializeNeeded()
    {
        // TODO: Implement isInitializeNeeded() method.
    }

    public function canUseForCountry($country)
    {
        // TODO: Implement canUseForCountry() method.
    }

    public function canUseForCurrency($currencyCode)
    {
        // TODO: Implement canUseForCurrency() method.
    }

    public function getInfoBlockType()
    {
        // TODO: Implement getInfoBlockType() method.
    }

    public function getInfoInstance()
    {
        // TODO: Implement getInfoInstance() method.
    }

    public function setInfoInstance(InfoInterface $info)
    {
        // TODO: Implement setInfoInstance() method.
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function order(InfoInterface $payment, $amount)
    {
        // TODO: Implement order() method.
    }

    public function refund(InfoInterface $payment, $amount)
    {
        // TODO: Implement refund() method.
    }

    public function cancel(InfoInterface $payment)
    {
        // TODO: Implement cancel() method.
    }

    public function void(InfoInterface $payment)
    {
        // TODO: Implement void() method.
    }

    public function canReviewPayment()
    {
        // TODO: Implement canReviewPayment() method.
    }

    public function acceptPayment(InfoInterface $payment)
    {
        // TODO: Implement acceptPayment() method.
    }

    public function denyPayment(InfoInterface $payment)
    {
        // TODO: Implement denyPayment() method.
    }

    public function getConfigData($field, $storeId = null)
    {
        // TODO: Implement getConfigData() method.
    }

    public function assignData(DataObject $data)
    {
        // TODO: Implement assignData() method.
    }

    public function isAvailable(CartInterface $quote = null)
    {
        // TODO: Implement isAvailable() method.
    }

    public function isActive($storeId = null)
    {
        // TODO: Implement isActive() method.
    }

    public function initialize($paymentAction, $stateObject)
    {
        // TODO: Implement initialize() method.
    }

    public function getConfigPaymentAction()
    {
        // TODO: Implement getConfigPaymentAction() method.
    }



}
