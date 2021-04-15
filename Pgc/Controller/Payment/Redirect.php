<?php

namespace Pgc\Pgc\Controller\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;

class Redirect extends Action implements CsrfAwareActionInterface
{
    const CHECKOUT_URL = 'checkout/cart';

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(Context $context, Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute(): RedirectResult
    {
        /**
         * @var $resultRedirect RedirectResult
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //$methodName = $this->getRequest()->getParam('method');

        $this->checkoutSession->restoreQuote();

        $this->messageManager->addNoticeMessage(__('order_error'));
        $resultRedirect->setPath(self::CHECKOUT_URL, ['_secure' => true]);

        return $resultRedirect;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
