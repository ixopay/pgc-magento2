<?php

namespace Pgc\Pgc\Controller\Payment;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Service\InvoiceService;
use Pgc\Pgc\Helper\Data;
use Psr\Log\LoggerInterface;

class Callback extends Action implements CsrfAwareActionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var Json
     */
    protected Json $_json;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var OrderManagementInterface
     */
    private OrderManagementInterface $orderManagement;

    /**
     * @var OrderCommentSender
     */
    private OrderCommentSender $orderCommentSender;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Callback constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Session $checkoutSession
     * @param InvoiceService $invoiceService
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $json
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param OrderCommentSender $orderCommentSender
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Session $checkoutSession,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $orderRepository,
        Json $json,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger,
        Data $helper,
        OrderCommentSender $orderCommentSender
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        $this->_json = $json;
        $this->logger = $logger;
        $this->orderManagement = $orderManagement;
        $this->orderCommentSender = $orderCommentSender;
    }

    public function execute()
    {
        $this->logger->debug('getting respons from payment gateway server');
        $request = $this->getRequest();
        $reponseXSignature = $request->getHeader('X-Signature');
        $this->logger->debug('X-Signature log start');
        $this->logger->debug($reponseXSignature);
        $this->logger->debug('X-Signature log end');
        $calculatedResponseXSignature = $this->helper->getResponseXSignature($request);
        $this->logger->debug('Calculated X-Signature log start');
        $this->logger->debug($calculatedResponseXSignature);
        $this->logger->debug('Calculated X-Signature log end');

        $jsonData = $request->getContent();
        $data = $this->_json->unserialize($jsonData);
        $order = $this->_initOrder($data['merchantTransactionId']);
        $response = [];

        if ($calculatedResponseXSignature != $reponseXSignature) {
            if ($order) {
                $order->addStatusHistoryComment('Order not updating due to signature mismatch');
                $orderResource = $this->_objectManager->get($order->getResourceName());
                $orderResource->save($order);
            }
            $this->logger->debug('Order not updating due to signature mismatch');
            echo 'Order not updating due to signature mismatch';
            exit;
        } else {
            if ($order) {
                if ($data['result'] == 'OK') {
                    try {
                        $order->getPayment()->accept();
                        $orderResource = $this->_objectManager->get($order->getResourceName());
                        $orderResource->save($order);
                        $response['msg'] = 'OK';
                        $this->logger->debug('Payment accepted');
                    } catch (Exception $e) {
                        $response['msg'] = $e->getMessage();
                        $this->logger->debug($e->getMessage());
                    }
                }

                if ($data['result'] == 'ERROR') {
                    try {
                        $msg = '';
                        $code = '';
                        $order->setState(Order::STATE_CANCELED);
                        $order->setStatus(Order::STATE_CANCELED);
                        if (isset($data['message'])) {
                            $msg = $data['message'];
                        }

                        if (isset($data['code'])) {
                            $code = $data['code'];
                        }

                        $order->addStatusHistoryComment($code . '::' . $msg);
                        $orderResource = $this->_objectManager->get($order->getResourceName());
                        $orderResource->save($order);
                        $this->orderCommentSender->send($order, true);
                        $response['msg'] = 'OK';
                        $this->logger->debug($code . '::' . $msg);
                    } catch (Exception $e) {
                        $response['msg'] = $e->getMessage();
                        $this->logger->debug($e->getMessage());
                    }
                }
            } else {
                $this->logger->debug('no order found');
                $response['msg'] = 'no order found';
            }
            echo $response['msg'];
            exit;
        }
    }

    protected function _initOrder($incrementId)
    {
        try {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order');
            $order->loadByIncrementId($incrementId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        }
        return $order;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
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
