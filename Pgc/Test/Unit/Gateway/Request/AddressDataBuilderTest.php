<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Pgc\Pgc\Gateway\Request\AddressDataBuilder;
use Pgc\Pgc\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressDataBuilderTest extends TestCase
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
     * @var AddressDataBuilder
     */
    private AddressDataBuilder $builder;

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
     * @param array $addressData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild(array $addressData, array $expectedResult)
    {
        $addressMock = $this->getAddressMock($addressData);

        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($addressMock);
        $this->orderMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($addressMock);


        $billingFieldsToBeTruncate = [
            AddressDataBuilder::COMPANY . "#" . AddressDataBuilder::BILLING_COMPANY_MAX_LENGTH => $addressData['billingCompany'],
            AddressDataBuilder::BILLING_STREET_ADDRESS1 . "#" . AddressDataBuilder::BILLING_STREET_ADDRESS1_MAX_LENGTH => $addressData['billingAddress1'],
            AddressDataBuilder::BILLING_STREET_ADDRESS2 . "#" . AddressDataBuilder::BILLING_STREET_ADDRESS2_MAX_LENGTH => $addressData['billingAddress2'],
            AddressDataBuilder::BILLING_CITY . "#" . AddressDataBuilder::BILLING_CITY_MAX_LENGTH => $addressData['billingCity'],
            AddressDataBuilder::BILLING_POSTAL_CODE . "#" . AddressDataBuilder::BILLING_POSTAL_CODE_MAX_LENGTH => $addressData['billingPostcode'],
            AddressDataBuilder::BILLING_STATE . "#" . AddressDataBuilder::BILLING_STATE_MAX_LENGTH => $addressData['billingState'],
            AddressDataBuilder::BILLING_PHONE . "#" . AddressDataBuilder::BILLING_PHONE_MAX_LENGTH => $addressData['billingPhone']
        ];

        $truncatedBillingFieldsReturn = [
            AddressDataBuilder::COMPANY => $addressData['billingCompany'],
            AddressDataBuilder::BILLING_STREET_ADDRESS1 => $addressData['billingAddress1'],
            AddressDataBuilder::BILLING_STREET_ADDRESS2 => $addressData['billingAddress2'],
            AddressDataBuilder::BILLING_CITY => $addressData['billingCity'],
            AddressDataBuilder::BILLING_POSTAL_CODE => $addressData['billingPostcode'],
            AddressDataBuilder::BILLING_STATE => $addressData['billingState'],
            AddressDataBuilder::BILLING_PHONE => $addressData['billingPhone']
        ];

        $this->helperMock->expects(static::once())
            ->method('getTruncatedString')
            ->with($billingFieldsToBeTruncate)
            ->willReturn($truncatedBillingFieldsReturn);


        $shippingFieldsToBeTruncate = [
            AddressDataBuilder::SHIPPING_COMPANY . "#" . AddressDataBuilder::SHIPPING_COMPANY_MAX_LENGTH => $addressData['shippingCompany'],
            AddressDataBuilder::SHIPPING_STREET_ADDRESS1 . "#" . AddressDataBuilder::SHIPPING_STREET_ADDRESS1_MAX_LENGTH => $addressData['shippingAddress1'],
            AddressDataBuilder::SHIPPING_STREET_ADDRESS2 . "#" . AddressDataBuilder::SHIPPING_STREET_ADDRESS2_MAX_LENGTH => $addressData['shippingAddress2'],
            AddressDataBuilder::SHIPPING_CITY . "#" . AddressDataBuilder::SHIPPING_CITY_MAX_LENGTH => $addressData['shippingCity'],
            AddressDataBuilder::SHIPPING_POSTAL_CODE . "#" . AddressDataBuilder::SHIPPING_POSTAL_CODE_MAX_LENGTH => $addressData['shippingPostcode'],
            AddressDataBuilder::SHIPPING_STATE . "#" . AddressDataBuilder::SHIPPING_STATE_MAX_LENGTH => $addressData['shippingState'],
            AddressDataBuilder::SHIPPING_PHONE . "#" . AddressDataBuilder::SHIPPING_PHONE_MAX_LENGTH => $addressData['shippingPhone']
        ];

        $truncatedShippingFieldsReturn = [
            AddressDataBuilder::SHIPPING_COMPANY => $addressData['shippingCompany'],
            AddressDataBuilder::SHIPPING_STREET_ADDRESS1 => $addressData['shippingAddress1'],
            AddressDataBuilder::SHIPPING_STREET_ADDRESS2 => $addressData['shippingAddress2'],
            AddressDataBuilder::SHIPPING_CITY => $addressData['shippingCity'],
            AddressDataBuilder::SHIPPING_POSTAL_CODE => $addressData['shippingPostcode'],
            AddressDataBuilder::SHIPPING_STATE => $addressData['shippingState'],
            AddressDataBuilder::SHIPPING_PHONE => $addressData['shippingPhone']
        ];

        $this->helperMock->expects(static::once())
            ->method('getShippingTruncatedString')
            ->with($shippingFieldsToBeTruncate)
            ->willReturn($truncatedShippingFieldsReturn);

        $this->helperMock->expects(static::once())
            ->method('getBillingRegionName')
            ->with($addressData['billingState'], $addressData['billingCountry'])
            ->willReturn($addressData['billingState']);

        $this->helperMock->expects(static::once())
            ->method('getShippingRegionName')
            ->with($addressData['shippingState'], $addressData['shippingCountry'])
            ->willReturn($addressData['shippingState']);

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        self::assertEquals($expectedResult, $this->builder->build($buildSubject));
    }

    /**
     * @param array $addressData
     * @return AddressAdapterInterface|MockObject
     */
    private function getAddressMock(array $addressData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        $addressMock->expects(static::exactly(2))
            ->method('getCompany')
            ->willReturn($addressData['billingCompany']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine1')
            ->willReturn($addressData['billingAddress1']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine2')
            ->willReturn($addressData['billingAddress2']);
        $addressMock->expects(static::exactly(2))
            ->method('getCity')
            ->willReturn($addressData['billingCity']);
        $addressMock->expects(static::exactly(2))
            ->method('getPostcode')
            ->willReturn($addressData['billingPostcode']);
        $addressMock->expects(static::exactly(2))
            ->method('getRegionCode')
            ->willReturn($addressData['billingState']);
        $addressMock->expects(static::exactly(2))
            ->method('getCountryId')
            ->willReturn($addressData['billingCountry']);
        $addressMock->expects(static::exactly(2))
            ->method('getTelephone')
            ->willReturn($addressData['billingPhone']);

        $addressMock->expects(static::exactly(2))
            ->method('getCompany')
            ->willReturn($addressData['shippingCompany']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine1')
            ->willReturn($addressData['shippingAddress1']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine2')
            ->willReturn($addressData['shippingAddress2']);
        $addressMock->expects(static::exactly(2))
            ->method('getCity')
            ->willReturn($addressData['shippingCity']);
        $addressMock->expects(static::exactly(2))
            ->method('getRegionCode')
            ->willReturn($addressData['shippingState']);
        $addressMock->expects(static::exactly(2))
            ->method('getCountryId')
            ->willReturn($addressData['shippingCountry']);
        $addressMock->expects(static::exactly(2))
            ->method('getPostcode')
            ->willReturn($addressData['shippingPostcode']);
        $addressMock->expects(static::exactly(2))
            ->method('getTelephone')
            ->willReturn($addressData['shippingPhone']);

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
                    'billingCompany' => 'Magento',
                    'billingAddress1' => 'street1',
                    'billingAddress2' => 'street2',
                    'billingCity' => 'Chicago',
                    'billingPostcode' => '654321',
                    'billingState' => 'IL',
                    'billingCountry' => 'US',
                    'billingPhone' => '555-555-555',
                    'shippingCompany' => 'Magento',
                    'shippingAddress1' => 'street1',
                    'shippingAddress2' => 'street2',
                    'shippingCity' => 'Chicago',
                    'shippingPostcode' => '654321',
                    'shippingState' => 'IL',
                    'shippingCountry' => 'US',
                    'shippingPhone' => '555-555-555'
                ],
                [
                    AddressDataBuilder::CUSTOMER => [
                        AddressDataBuilder::COMPANY => 'Magento',
                        AddressDataBuilder::BILLING_STREET_ADDRESS1 => 'street1',
                        AddressDataBuilder::BILLING_STREET_ADDRESS2 => 'street2',
                        AddressDataBuilder::BILLING_CITY => 'Chicago',
                        AddressDataBuilder::BILLING_POSTAL_CODE => '654321',
                        AddressDataBuilder::BILLING_STATE => 'IL',
                        AddressDataBuilder::BILLING_COUNTRY => 'US',
                        AddressDataBuilder::BILLING_PHONE => '555-555-555',
                        AddressDataBuilder::SHIPPING_COMPANY => 'Magento',
                        AddressDataBuilder::SHIPPING_STREET_ADDRESS1 => 'street1',
                        AddressDataBuilder::SHIPPING_STREET_ADDRESS2 => 'street2',
                        AddressDataBuilder::SHIPPING_CITY => 'Chicago',
                        AddressDataBuilder::SHIPPING_POSTAL_CODE => '654321',
                        AddressDataBuilder::SHIPPING_STATE => 'IL',
                        AddressDataBuilder::SHIPPING_COUNTRY => 'US',
                        AddressDataBuilder::SHIPPING_PHONE => '555-555-555'
                    ]

                ]
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->helperMock = $this->getMockBuilder(Helper::class)->disableOriginalConstructor()->getMock();
        $this->builder = new AddressDataBuilder($this->helperMock);
    }
}
