<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\CaptureRequest;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CaptureRequestTest extends TestCase
{
    /**
     * @var CaptureRequest
     */
    private CaptureRequest $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Payment|MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @covers \Pgc\Pgc\Gateway\Request\CaptureRequest::build
     */
    public function testBuild()
    {
        $incrementId = 'b3b99d';
        $merchantTransactionId = $incrementId . '-capture-' . date('Y-m-d h:i:s');
        $amount = 10.00;
        $currency_code = 'USD';

        $transactionId = '65ba2e7739b29f4ecbf9-capture';
        $referenceUuid = str_replace('-capture', '', $transactionId);

        $expected = [
            'merchantTransactionId' => $merchantTransactionId,
            'amount' => $amount,
            'referenceUuid' => $referenceUuid,
            'currency' => $currency_code
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->willReturn('USD');

        $this->orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($incrementId);

        $this->payment->expects(static::once())
            ->method('getTransactionId')
            ->willReturn($transactionId);

        static::assertEquals($expected, $this->builder->build($buildSubject));
    }

    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);

        $configMock = $this->createMock(ConfigInterface::class);
        $helperMock = $this->createMock(Helper::class);
        $this->builder = new CaptureRequest($configMock, $helperMock);
    }
}
