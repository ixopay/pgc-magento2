<?php

namespace CloudPay\CloudPay\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;

class Callback extends Action implements CsrfAwareActionInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \CloudPay\CloudPay\Helper\Data
     */
    private $cloudPayHelper;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \CloudPay\CloudPay\Helper\Data $cloudPayHelper
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->cloudPayHelper = $cloudPayHelper;
    }

    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $notification = $request->getContent();

        if ($request->getMethod() !== 'POST') {
            die('invalid request');
        }

        $xml =\ simplexml_load_string($notification);
        $data = \json_decode(json_encode($xml),true);

        if (empty($data)) {
            die('invalid request');
        }

        $incrementId = $data['transactionId'];

        /** @var Order $order */
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId($incrementId);

        if (empty($order->getId())) {
            return false;
        }

        //TODO: SELECT CORRECT PAYMENT SETTINGS
        \CloudPay\Client\Client::setApiUrl($this->cloudPayHelper->getGeneralConfigData('host'));
        $client = new \CloudPay\Client\Client(
            $this->cloudPayHelper->getGeneralConfigData('username'),
            $this->cloudPayHelper->getGeneralConfigData('password'),
            $this->cloudPayHelper->getPaymentConfigData('api_key', 'cloudpay_creditcard', null),
            $this->cloudPayHelper->getPaymentConfigData('shared_secret', 'cloudpay_creditcard', null)
        );

        $queryString = $request->getServerValue('QUERY_STRING');
        if (empty($request->getHeader('date')) ||
            empty($request->getHeader('authorization')) ||
            $client->validateCallback($notification, $queryString, $request->getHeader('date'), $request->getHeader('authorization'))) {

            die('invalid callback');
        }

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        /** @var Order\Payment $payment */
        $payment = $order->getPayment();
        $payment->setTransactionId($data['purchaseId']);
        $payment->setLastTransId($data['purchaseId']);
        $payment->addTransaction('capture');

        $orderResource = $this->_objectManager->get($order->getResourceName());
        $orderResource->save($order);

        die('OK');
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
