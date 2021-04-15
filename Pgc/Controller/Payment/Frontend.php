<?php

namespace Pgc\Pgc\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;

class Frontend extends Action
{
    const REDIRECT_URL = 'redirectUrl';
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * Frontend constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param UrlInterface $urlBuilder
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        UrlInterface $urlBuilder,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->session = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $order = $this->session->getLastRealOrder();
        $paymentResult = $order->getPayment();
        $additionalInformation = $paymentResult->getAdditionalInformation();
        if (isset($additionalInformation[self::REDIRECT_URL]) && !empty($additionalInformation[self::REDIRECT_URL])) {
            $response->setData([
                'type' => 'redirect',
                'url' => $additionalInformation[self::REDIRECT_URL]
            ]);

            return $response;
        }

        $response->setData([
            'type' => 'finished',
        ]);

        return $response;
    }
}
