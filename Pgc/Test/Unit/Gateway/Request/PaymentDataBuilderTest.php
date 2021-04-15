<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\PaymentDataBuilder;
use Pgc\Pgc\Observer\DataAssignObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentDataBuilderTest extends TestCase
{
    const TRANSACTION_TOKEN = 'transaction-token';

    /**
     * @var PaymentDataBuilder
     */
    private PaymentDataBuilder $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var MockObject
     */
    private $paymentDO;


    public function testBuild()
    {
        $additionalData = [
            [
                DataAssignObserver::TRANSACTION_TOKEN,
                self::TRANSACTION_TOKEN
            ]
        ];

        $expectedResult = [
            PaymentDataBuilder::TRANSACTION_TOKEN => self::TRANSACTION_TOKEN
        ];

        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        $this->paymentMock->expects(static::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturnMap($additionalData);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);


        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }

    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()
            ->getMock();
        $this->builder = new PaymentDataBuilder();
    }
}
