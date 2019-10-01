<?php

namespace Pgc\Pgc\Controller\Payment;

use Pgc\Client\Transaction\Debit;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;

class Frontend extends Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    private $paymentInformation;

    /**
     * @var Data
     */
    private $paymentHelper;

    /**
     * @var \Pgc\Pgc\Helper\Data
     */
    private $pgcHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Frontend constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformation,
        Data $paymentHelper,
        \Pgc\Pgc\Helper\Data $pgcHelper,
        UrlInterface $urlBuilder,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->session = $checkoutSession;
        $this->paymentInformation = $paymentInformation;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
        $this->pgcHelper = $pgcHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $request = $this->getRequest()->getPost()->toArray();
        $response = $this->resultJsonFactory->create();

        $paymentMethod = 'pgc_creditcard';

        //TODO: SELECT CORRECT PAYMENT SETTINGS
        \Pgc\Client\Client::setApiUrl($this->pgcHelper->getGeneralConfigData('host'));
        $client = new \Pgc\Client\Client(
            $this->pgcHelper->getGeneralConfigData('username'),
            $this->pgcHelper->getGeneralConfigData('password'),
            $this->pgcHelper->getPaymentConfigData('api_key', $paymentMethod, null),
            $this->pgcHelper->getPaymentConfigData('shared_secret', $paymentMethod, null)
        );

        $order = $this->session->getLastRealOrder();

        $debit = new Debit();
        if ($this->pgcHelper->getPaymentConfigDataFlag('seamless', $paymentMethod)) {
            $token = (string) $request['token'];

            if (empty($token)) {
                die('empty token');
            }

            $debit->setTransactionToken($token);
        }
        $debit->addExtraData('3dsecure', 'OPTIONAL');

        $debit->setTransactionId($order->getIncrementId());
        $debit->setAmount(\number_format($order->getGrandTotal(), 2, '.', ''));
        $debit->setCurrency($order->getOrderCurrency()->getCode());

        $customer = new \Pgc\Client\Data\Customer();
        $customer->setFirstName($order->getCustomerFirstname());
        $customer->setLastName($order->getCustomerLastname());
        $customer->setEmail($order->getCustomerEmail());

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress !== null) {
            $customer->setBillingAddress1($billingAddress->getStreet()[0]);
            $customer->setBillingPostcode($billingAddress->getPostcode());
            $customer->setBillingCity($billingAddress->getCity());
            $customer->setBillingCountry($billingAddress->getCountryId());
            $customer->setBillingPhone($billingAddress->getTelephone());
        }
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress !== null) {
            $customer->setShippingCompany($shippingAddress->getCompany());
            $customer->setShippingFirstName($shippingAddress->getFirstname());
            $customer->setShippingLastName($shippingAddress->getLastname());
            $customer->setShippingAddress1($shippingAddress->getStreet()[0]);
            $customer->setShippingPostcode($shippingAddress->getPostcode());
            $customer->setShippingCity($shippingAddress->getCity());
            $customer->setShippingCountry($shippingAddress->getCountryId());
        }

        $debit->setCustomer($customer);

        $baseUrl = $this->urlBuilder->getRouteUrl('pgc');

        $debit->setSuccessUrl($this->urlBuilder->getUrl('checkout/onepage/success'));
        $debit->setCancelUrl($baseUrl . 'payment/redirect');
        $debit->setErrorUrl($baseUrl . 'payment/redirect');

        $debit->setCallbackUrl($baseUrl . 'payment/callback');

        $this->prepare3dSecure2Data($debit, $order);

        $paymentResult = $client->debit($debit);

        if (!$paymentResult->isSuccess()) {
            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;
        }

        if ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_ERROR) {

            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_REDIRECT) {

            $response->setData([
                'type' => 'redirect',
                'url' => $paymentResult->getRedirectUrl()
            ]);

            return $response;

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_PENDING) {
            //payment is pending, wait for callback to complete

            //setCartToPending();

        } elseif ($paymentResult->getReturnType() == \Pgc\Client\Transaction\Result::RETURN_TYPE_FINISHED) {

            // todo: move to callback controller

            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus(Order::STATE_PROCESSING);

            /** @var Order\Payment $payment */
            $payment = $order->getPayment();
            $payment->setTransactionId($paymentResult->getPurchaseId());
            $payment->setLastTransId($paymentResult->getPurchaseId());
            $payment->addTransaction('capture');

            $orderResource = $this->_objectManager->get($order->getResourceName());
            $orderResource->save($order);

            $response->setData([
                'type' => 'finished',
            ]);
        }

        return $response;
    }

    private function prepare3dSecure2Data(Debit $debit, Order $order)
    {
        $debit->addExtraData('3ds:channel', '02'); // Browser
        $debit->addExtraData('3ds:authenticationIndicator ', '01'); // Payment transaction

        if ($order->getCustomerIsGuest()) {
            $debit->addExtraData('3ds:cardholderAuthenticationMethod', '01');
            $debit->addExtraData('3ds:cardholderAccountAgeIndicator', '01');
        } else {
            $debit->addExtraData('3ds:cardholderAuthenticationMethod', '02');
            //$debit->addExtraData('3ds:cardholderAccountDate', \date('Y-m-d', $order->getCustomer()->getCreatedAtTimestamp()));
        }

        //$debit->addExtraData('3ds:shipIndicator', \date('Y-m-d', $order->getCustomer()->getCreatedAtTimestamp()));

        if ($order->getShippigAddressId() == $order->getBillingAddressId()) {
            $debit->addExtraData('3ds:billingShippingAddressMatch ', 'Y');
        } else {
            $debit->addExtraData('3ds:billingShippingAddressMatch ', 'N');
        }

    }
}
