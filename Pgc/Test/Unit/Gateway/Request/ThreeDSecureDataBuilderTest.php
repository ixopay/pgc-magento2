<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pgc\Pgc\Gateway\Request\ThreeDSecureDataBuilder;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThreeDSecureDataBuilderTest extends TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var ThreeDSecureDataBuilder
     */
    private ThreeDSecureDataBuilder $builder;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @covers \Pgc\Pgc\Gateway\Request\ThreeDSecureDataBuilderTest::build
     * @throws LocalizedException
     */
    public function testBuild()
    {
        $customerParams = $this->getThreesParams();

        $expected = [
            ThreeDSecureDataBuilder::THREE_D_SECURE_DATA => [
                'channel' => '02',
                'transType' => '01',
                'challengeIndicator' => '01',
                'authenticationIndicator' => '01',
                'paymentAccountAgeIndicator' => '01',
                'billingShippingAddressMatch' => 'N',
                '3dsecure' => 'OPTIONAL',
                'cardholderAccountDate' => '2021-01-06',
                'cardholderAccountChangeIndicator' => '0',
                'cardholderAccountLastChange' => '2021-01-18'
            ]
        ];

        $amount = 10.00;

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount
        ];

        $this->paymentDOMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->helperMock->expects(static::once())
            ->method('getShoppingArea')
            ->willReturn('frontend');

        $this->helperMock->expects(static::once())
            ->method('getCustomer3dInfo')
            ->willReturn($customerParams);

        $result = $this->builder->build($buildSubject);

        static::assertEquals($expected, $result);
    }

    /**
     * @return string[]
     */
    private function getThreesParams(): array
    {
        return [
            '3ds:channel' => '02',
            '3ds:transType' => '01',
            '3ds:challengeIndicator' => '01',
            '3ds:authenticationIndicator' => '01',
            '3ds:paymentAccountAgeIndicator' => '01',
            '3ds:cardholderAccountDate' => '2021-01-06',
            '3ds:cardholderAccountChangeIndicator' => '0',
            '3ds:cardholderAccountLastChange' => '2021-01-18',
            '3dsecure' => 'OPTIONAL'
        ];
    }


    protected function setUp(): void
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->helperMock = $this->getMockBuilder(Helper::class)->disableOriginalConstructor()->getMock();
        $this->builder = new ThreeDSecureDataBuilder($this->helperMock);
    }
}
