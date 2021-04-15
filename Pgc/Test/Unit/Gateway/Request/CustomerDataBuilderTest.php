<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Pgc\Pgc\Gateway\Request\CustomerDataBuilder;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerDataBuilderTest extends TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @var CustomerDataBuilder
     */
    private CustomerDataBuilder $builder;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     */
    public function testBuildReadPaymentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $buildSubject = [
            'payment' => null,
        ];

        $this->builder->build($buildSubject);
    }

    /**
     * @param array $billingData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild(array $billingData, array $expectedResult)
    {
        $billingMock = $this->getBillingMock($billingData);

        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingMock);

        $billingFieldsToBeTruncate = [
            CustomerDataBuilder::FIRST_NAME . "#" . CustomerDataBuilder::FIRST_NAME_MAX_LENGTH => $billingData['first_name'],
            CustomerDataBuilder::LAST_NAME . "#" . CustomerDataBuilder::LAST_NAME_MAX_LENGTH => $billingData['last_name'],
            CustomerDataBuilder::COMPANY . "#" . CustomerDataBuilder::COMPANY_MAX_LENGTH => $billingData['company']
        ];

        $truncatedBillingFieldsReturn = [
            CustomerDataBuilder::FIRST_NAME => $billingData['first_name'],
            CustomerDataBuilder::LAST_NAME => $billingData['last_name'],
            CustomerDataBuilder::COMPANY => $billingData['company']
        ];

        $this->helperMock->expects(static::once())
            ->method('getTruncatedString')
            ->with($billingFieldsToBeTruncate)
            ->willReturn($truncatedBillingFieldsReturn);

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        self::assertEquals($expectedResult, $this->builder->build($buildSubject));
    }

    /**
     * @param array $billingData
     * @return AddressAdapterInterface|MockObject
     */
    private function getBillingMock(array $billingData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        /* $addressMock->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($billingData['identification']); */
        $addressMock->expects(static::once())
            ->method('getEmail')
            ->willReturn($billingData['email']);
        $addressMock->expects(static::once())
            ->method('getFirstname')
            ->willReturn($billingData['first_name']);
        $addressMock->expects(static::once())
            ->method('getLastname')
            ->willReturn($billingData['last_name']);
        $addressMock->expects(static::once())
            ->method('getCompany')
            ->willReturn($billingData['company']);

        return $addressMock;
    }

    /**
     * @return array
     */
    public function dataProviderBuild(): array
    {
        return [
            [
                [
                    'identification' => 0,
                    'email' => 'john@magento.com',
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                    'company' => 'Magento'
                ],
                [
                    CustomerDataBuilder::CUSTOMER => [
                        CustomerDataBuilder::CUSTOMER_ID => '0',
                        CustomerDataBuilder::EMAIL => 'john@magento.com',
                        CustomerDataBuilder::FIRST_NAME => 'John',
                        CustomerDataBuilder::LAST_NAME => 'Smith',
                        CustomerDataBuilder::COMPANY => 'Magento'
                    ]
                ]
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->helperMock = $this->createMock(Helper::class);
        $this->builder = new CustomerDataBuilder($this->helperMock);
    }
}
