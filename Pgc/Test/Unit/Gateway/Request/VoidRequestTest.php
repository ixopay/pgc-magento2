<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\VoidRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VoidRequestTest extends TestCase
{
    /**
     * @var Payment|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var VoidRequest
     */
    private VoidRequest $builder;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    public function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $configMock = $this->createMock(ConfigInterface::class);

        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->builder = new VoidRequest($configMock);
    }

    /**
     * @covers \Pgc\Pgc\Gateway\Request\VoidRequest::build
     */
    public function testBuildWithException()
    {
        $incrementId = 'b3b99d67676768';
        $transactionId = '65ba2e773900909b29f4ecbf9-void';
        $merchantTransactionId = $incrementId . '-void-' . date('Y-m-d');
        $referenceUuid = str_replace('-void', '', $transactionId);

        $expected = [
            'merchantTransactionId' => $merchantTransactionId,
            'referenceUuid' => $referenceUuid,
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);


        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);


        $this->orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($incrementId);

        $this->payment->expects(static::once())
            ->method('getTransactionId')
            ->willReturn($transactionId);

        $this->builder->build($buildSubject);
    }

    /**
     * @covers \Pgc\Pgc\Gateway\Request\VoidRequest::build
     */
    public function testBuild()
    {
        $incrementId = 'b3b99d5555555';
        $transactionId = '65ba2e7739b2555559f4ecbf9-void';
        $merchantTransactionId = $incrementId . '-void-' . date('Y-m-d');
        $referenceUuid = str_replace('-void', '', $transactionId);

        $expected = [
            'merchantTransactionId' => $merchantTransactionId,
            'referenceUuid' => $referenceUuid,
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];


        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($incrementId);

        $this->payment->expects(static::once())
            ->method('getTransactionId')
            ->willReturn($transactionId);

        static::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
