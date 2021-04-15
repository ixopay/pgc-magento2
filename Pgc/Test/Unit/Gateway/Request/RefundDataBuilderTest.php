<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\RefundDataBuilder;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefundDataBuilderTest extends TestCase
{
    /**
     * @var RefundDataBuilder
     */
    private RefundDataBuilder $dataBuilder;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    public function testBuild()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()
            ->getMock();

        $buildSubject = [
            'payment' => $paymentDO,
            'amount' => 12.358
        ];

        $increamentId = 'b3b99d';
        $transactionId = '65ba2e7739b29f4ecbf9-refund';
        $merchantTransactionId = $increamentId . '-refund-' . date('Y-m-d');
        $amount = '12.36';
        $currency = 'USD';
        $referenceUuid = str_replace('-refund', '', $transactionId);
        $callbackUrl = 'http://www.paymentixo.com/payment/callback';

        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentModel);

        $paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($increamentId);


        $this->orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->willReturn($currency);

        $paymentModel->expects(static::once())
            ->method('getTransactionId')
            ->willReturn($transactionId);

        $this->helperMock->expects(static::once())
            ->method('getCallbackUrl')
            ->willReturn($callbackUrl);

        static::assertEquals(
            [
                'merchantTransactionId' => $merchantTransactionId,
                'amount' => $amount,
                'currency' => $currency,
                'referenceUuid' => $referenceUuid,
                'callbackUrl' => $callbackUrl
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    protected function setUp(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $this->helperMock = $this->createMock(Helper::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->dataBuilder = new RefundDataBuilder($configMock, $this->helperMock);
    }
}
