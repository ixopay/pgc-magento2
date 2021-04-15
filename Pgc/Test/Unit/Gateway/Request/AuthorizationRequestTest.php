<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\AuthorizationRequest;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class AuthorizationRequestTest extends TestCase
{
    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @var AuthorizationRequestBuilder
     */
    private $builder;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     * @covers \Pgc\Pgc\Gateway\Request\AuthorizationRequest::build
     */
    public function testBuild()
    {
        $merchantTransactionId = '000000001';
        $amount = '100.00';
        $currency = 'USD';
        $successUrl = 'http://www.paymentixo.com/checkout/onepage/success';
        $cancelUrl = 'http://www.paymentixo.com/payment/redirect?status=cancel';
        $errorUrl = 'http://www.paymentixo.com/payment/redirect?status=error';
        $callbackUrl = 'http://www.paymentixo.com/payment/callback';
        $transactionIndicatorVal = 'frontend';

        $expected = [
            'merchantTransactionId' => $merchantTransactionId,
            'amount' => $amount,
            'currency' => $currency,
            'successUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
            'errorUrl' => $errorUrl,
            'callbackUrl' => $callbackUrl,
            'transactionIndicator' => $transactionIndicatorVal
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn($amount);

        $this->orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->willReturn($currency);

        $this->helperMock->expects(static::once())
            ->method('getTruncateString')
            ->with($this->orderMock->getOrderIncrementId(), AuthorizationRequest::MERCHANT_TRANSACTION_MAX_LENGTH)
            ->willReturn($merchantTransactionId);

        $this->helperMock->expects(static::once())
            ->method('getSuccessUrl')
            ->willReturn($successUrl);

        $this->helperMock->expects(static::once())
            ->method('getCancelUrl')
            ->willReturn($cancelUrl);

        $this->helperMock->expects(static::once())
            ->method('getErrorUrl')
            ->willReturn($errorUrl);

        $this->helperMock->expects(static::once())
            ->method('getCallbackUrl')
            ->willReturn($callbackUrl);

        $this->helperMock->expects(static::once())
            ->method('getTransactionIndicatorVal')
            ->willReturn($transactionIndicatorVal);

        static::assertEquals($expected, $this->builder->build($buildSubject));
    }

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $configMock = $this->createMock(ConfigInterface::class);
        $this->helperMock = $this->createMock(Helper::class);
        $this->builder = new AuthorizationRequest($configMock, $this->helperMock);
    }
}
