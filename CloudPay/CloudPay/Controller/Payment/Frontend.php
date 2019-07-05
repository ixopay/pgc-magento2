<?php

namespace CloudPay\CloudPay\Controller\Payment;

use CloudPay\Client\Transaction\Debit;
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
     * @var \CloudPay\CloudPay\Helper\Data
     */
    private $cloudPayHelper;

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
        \CloudPay\CloudPay\Helper\Data $cloudPayHelper,
        UrlInterface $urlBuilder,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->session = $checkoutSession;
        $this->paymentInformation = $paymentInformation;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
        $this->cloudPayHelper = $cloudPayHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $request = $this->getRequest()->getPost()->toArray();
        $response = $this->resultJsonFactory->create();

        $paymentMethod = 'cloudpay_creditcard';

        //TODO: SELECT CORRECT PAYMENT SETTINGS
        \CloudPay\Client\Client::setApiUrl($this->cloudPayHelper->getGeneralConfigData('host'));
        $client = new \CloudPay\Client\Client(
            $this->cloudPayHelper->getGeneralConfigData('username'),
            $this->cloudPayHelper->getGeneralConfigData('password'),
            $this->cloudPayHelper->getPaymentConfigData('api_key', $paymentMethod, null),
            $this->cloudPayHelper->getPaymentConfigData('shared_secret', $paymentMethod, null)
        );

        $order = $this->session->getLastRealOrder();

        $debit = new Debit();
        if ($this->cloudPayHelper->getPaymentConfigDataFlag('seamless', $paymentMethod)) {
            $token = (string) $request['token'];

            if (empty($token)) {
                die('empty token');
            }

            $debit->setTransactionToken($token);
        }

        $debit->setTransactionId($order->getIncrementId());
        $debit->setAmount(\number_format($order->getGrandTotal(), 2, '.', ''));
        $debit->setCurrency($order->getOrderCurrency()->getCode());

        $customer = new \CloudPay\Client\Data\Customer();
        $customer->setFirstName($order->getCustomerFirstname());
        $customer->setLastName($order->getCustomerLastname());
        $customer->setEmail($order->getCustomerEmail());

        $debit->setCustomer($customer);

        $baseUrl = $this->urlBuilder->getRouteUrl('cloudpay');

        $debit->setSuccessUrl($this->urlBuilder->getUrl('checkout/onepage/success'));
        $debit->setCancelUrl($baseUrl . 'payment/redirect');
        $debit->setErrorUrl($baseUrl . 'payment/redirect');

        $debit->setCallbackUrl($baseUrl . 'payment/callback');

        $paymentResult = $client->debit($debit);

        if (!$paymentResult->isSuccess()) {
            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;
        }

        if ($paymentResult->getReturnType() == \CloudPay\Client\Transaction\Result::RETURN_TYPE_ERROR) {

            $response->setData([
                'type' => 'error',
                'errors' => $paymentResult->getFirstError()->getMessage()
            ]);
            return $response;

        } elseif ($paymentResult->getReturnType() == \CloudPay\Client\Transaction\Result::RETURN_TYPE_REDIRECT) {

            $response->setData([
                'type' => 'redirect',
                'url' => $paymentResult->getRedirectUrl()
            ]);

            return $response;

        } elseif ($paymentResult->getReturnType() == \CloudPay\Client\Transaction\Result::RETURN_TYPE_PENDING) {
            //payment is pending, wait for callback to complete

            //setCartToPending();

        } elseif ($paymentResult->getReturnType() == \CloudPay\Client\Transaction\Result::RETURN_TYPE_FINISHED) {

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
}
