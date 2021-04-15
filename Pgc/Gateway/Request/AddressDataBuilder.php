<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Pgc\Pgc\Helper\Data;
use Magento\Payment\Gateway\Helper\SubjectReader;

class AddressDataBuilder implements BuilderInterface
{
    const CUSTOMER = 'customer';
    const SHIPPING_ADDRESS = 'shipping';
    const BILLING_ADDRESS = 'billing';
    const BILLING_FIRST_NAME = 'billingFirstName';
    const SHIPPING_FIRST_NAME = 'shippingFirstName';
    const BILLING_LAST_NAME = 'billingLastName';
    const SHIPPING_LAST_NAME = 'shippingLastName';
    const COMPANY = 'company';
    const SHIPPING_COMPANY = 'shippingCompany';
    const BILLING_STREET_ADDRESS1 = 'billingAddress1';
    const SHIPPING_STREET_ADDRESS1 = 'shippingAddress1';
    const BILLING_STREET_ADDRESS2 = 'billingAddress2';
    const SHIPPING_STREET_ADDRESS2 = 'shippingAddress2';
    const EXTENDED_ADDRESS = 'extendedAddress';
    const LOCALITY = 'locality';
    const BILLING_CITY = 'billingCity';
    const SHIPPING_CITY = 'shippingCity';
    const REGION = 'region';
    const BILLING_STATE = 'billingState';
    const SHIPPING_STATE = 'shippingState';
    const BILLING_POSTAL_CODE = 'billingPostcode';
    const SHIPPING_POSTAL_CODE = 'shippingPostcode';
    const COUNTRY_CODE = 'countryCodeAlpha2';
    const BILLING_COUNTRY = 'billingCountry';
    const SHIPPING_COUNTRY = 'shippingCountry';
    const BILLING_PHONE = 'billingPhone';
    const SHIPPING_PHONE = 'shippingPhone';

    const SHIPPING_FIRST_NAME_MAX_LENGTH = 50;
    const SHIPPING_LAST_NAME_MAX_LENGTH = 50;
    const SHIPPING_COMPANY_MAX_LENGTH = 50;
    const BILLING_COMPANY_MAX_LENGTH = 50;
    const BILLING_STREET_ADDRESS1_MAX_LENGTH = 50;
    const SHIPPING_STREET_ADDRESS1_MAX_LENGTH = 50;
    const BILLING_STREET_ADDRESS2_MAX_LENGTH = 50;
    const SHIPPING_STREET_ADDRESS2_MAX_LENGTH = 50;
    const BILLING_CITY_MAX_LENGTH = 30;
    const SHIPPING_CITY_MAX_LENGTH = 30;
    const BILLING_STATE_MAX_LENGTH = 30;
    const SHIPPING_STATE_MAX_LENGTH = 30;
    const BILLING_POSTAL_CODE_MAX_LENGTH = 8;
    const SHIPPING_POSTAL_CODE_MAX_LENGTH = 8;
    const BILLING_PHONE_MAX_LENGTH = 20;
    const SHIPPING_PHONE_MAX_LENGTH = 20;

    /**
     * @var Data
     */
    protected Data $_helper;

    /**
     * Constructor
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $result = [];
        $result[self::CUSTOMER] = [];

        $billingAddress = $order->getBillingAddress();
        $billingResult = [];
        if ($billingAddress) {
            $billingRegionName = '';
            $billingRegionCode = $billingAddress->getRegionCode();
            $billingCountryId = $billingAddress->getCountryId();
            if ($billingRegionCode) {
                $billingRegionName = $this->_helper->getBillingRegionName($billingRegionCode, $billingCountryId);
            }

            $billingFieldToBeTruncate = [
                self::COMPANY . "#" . self::BILLING_COMPANY_MAX_LENGTH => $billingAddress->getCompany(),
                self::BILLING_STREET_ADDRESS1 . "#" . self::BILLING_STREET_ADDRESS1_MAX_LENGTH => $billingAddress->getStreetLine1(),
                self::BILLING_STREET_ADDRESS2 . "#" . self::BILLING_STREET_ADDRESS2_MAX_LENGTH => $billingAddress->getStreetLine2(),
                self::BILLING_CITY . "#" . self::BILLING_CITY_MAX_LENGTH => $billingAddress->getCity(),
                self::BILLING_POSTAL_CODE . "#" . self::BILLING_POSTAL_CODE_MAX_LENGTH => $billingAddress->getPostcode(),
                self::BILLING_STATE . "#" . self::BILLING_STATE_MAX_LENGTH => $billingRegionName,
                self::BILLING_PHONE . "#" . self::BILLING_PHONE_MAX_LENGTH => $billingAddress->getTelephone()
            ];

            $billingTruncatedResult = $this->_helper->getTruncatedString($billingFieldToBeTruncate);

            $billingResult = [
                self::COMPANY => $billingTruncatedResult[self::COMPANY],
                self::BILLING_STREET_ADDRESS1 => $billingTruncatedResult[self::BILLING_STREET_ADDRESS1],
                self::BILLING_STREET_ADDRESS2 => $billingTruncatedResult[self::BILLING_STREET_ADDRESS2],
                self::BILLING_CITY => $billingTruncatedResult[self::BILLING_CITY],
                self::BILLING_POSTAL_CODE => $billingTruncatedResult[self::BILLING_POSTAL_CODE],
                self::BILLING_STATE => $billingTruncatedResult[self::BILLING_STATE],
                self::BILLING_COUNTRY => $billingCountryId,
                self::BILLING_PHONE => $billingTruncatedResult[self::BILLING_PHONE]
            ];
        }
        $shippingResult = [];
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingRegionName = '';
            $shippingRegionCode = $shippingAddress->getRegionCode();
            $shippingCountryId = $shippingAddress->getCountryId();
            if ($shippingRegionCode) {
                $shippingRegionName = $this->_helper->getShippingRegionName($shippingRegionCode, $shippingCountryId);
            }

            $shippingFieldToBeTruncate = [
                self::SHIPPING_COMPANY . "#" . self::SHIPPING_COMPANY_MAX_LENGTH => $shippingAddress->getCompany(),
                self::SHIPPING_STREET_ADDRESS1 . "#" . self::SHIPPING_STREET_ADDRESS1_MAX_LENGTH => $shippingAddress->getStreetLine1(),
                self::SHIPPING_STREET_ADDRESS2 . "#" . self::SHIPPING_STREET_ADDRESS2_MAX_LENGTH => $shippingAddress->getStreetLine2(),
                self::SHIPPING_CITY . "#" . self::SHIPPING_CITY_MAX_LENGTH => $shippingAddress->getCity(),
                self::SHIPPING_POSTAL_CODE . "#" . self::SHIPPING_POSTAL_CODE_MAX_LENGTH => $shippingAddress->getPostcode(),
                self::SHIPPING_STATE . "#" . self::SHIPPING_STATE_MAX_LENGTH => $shippingRegionName,
                self::SHIPPING_PHONE . "#" . self::SHIPPING_PHONE_MAX_LENGTH => $shippingAddress->getTelephone()
            ];

            $shippingTruncatedResult = $this->_helper->getShippingTruncatedString($shippingFieldToBeTruncate);

            $shippingResult = [
                /* We have commented shippingFirstName/shippingLastName coz it's creating problem in  test unit. when using billingFirstname/billingLastname with shippingFirstName/shippingLastName then test unit works fine but order doesn't placed as engine doesn't support paramaters billingFirstname/billingLastname*/
                //  self::SHIPPING_FIRST_NAME => $shippingAddress->getFirstname(),
                //  self::SHIPPING_LAST_NAME => $shippingAddress->getLastname(),
                self::SHIPPING_COMPANY => $shippingTruncatedResult[self::SHIPPING_COMPANY],
                self::SHIPPING_STREET_ADDRESS1 => $shippingTruncatedResult[self::SHIPPING_STREET_ADDRESS1],
                self::SHIPPING_STREET_ADDRESS2 => $shippingTruncatedResult[self::SHIPPING_STREET_ADDRESS2],
                self::SHIPPING_CITY => $shippingTruncatedResult[self::SHIPPING_CITY],
                self::SHIPPING_POSTAL_CODE => $shippingTruncatedResult[self::SHIPPING_POSTAL_CODE],
                self::SHIPPING_STATE =>  $shippingTruncatedResult[self::SHIPPING_STATE],
                self::SHIPPING_COUNTRY => $shippingCountryId,
                self::SHIPPING_PHONE => $shippingTruncatedResult[self::SHIPPING_PHONE]
            ];
        }
        $result[self::CUSTOMER] = array_merge($billingResult, $shippingResult);
        return $result;
    }
}
