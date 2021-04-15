<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pgc\Pgc\Helper\Data;

class CustomerDataBuilder implements BuilderInterface
{
    /**
     * Customer block name
     */
    const CUSTOMER = 'customer';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The customer’s company. 255 character maximum.
     */
    const COMPANY = 'company';

    /**
     * The customer’s email address, comprised of ASCII characters.
     */
    const EMAIL = 'email';

    /**
     * Phone number. Phone must be 10-14 characters and can
     * only contain numbers, dashes, parentheses and periods.
     */
    const PHONE = 'phone';

    /**
     * The customer’s unique identification(email address).
     */
    const CUSTOMER_ID = 'identification';

    /**
     * First name. Firstname must be maximum 50 charecters long.
     */
    const FIRST_NAME_MAX_LENGTH = 50;
    /**
     * Last name. Lastname must be maximum 50 charecters long.
     */
    const LAST_NAME_MAX_LENGTH = 50;
    /**
     * Company. company must be maximum 50 charecters long.
     */
    const COMPANY_MAX_LENGTH = 50;

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
        $billingAddress = $order->getBillingAddress();
        $customerEmail = $billingAddress->getEmail();
        if ($order->getCustomerId()) {
            $identification = $order->getCustomerId();
        } else {
            $identification = 0;
        }

        $billingFieldToBeTruncate = [
            self::FIRST_NAME . "#" . self::FIRST_NAME_MAX_LENGTH => $billingAddress->getFirstname(),
            self::LAST_NAME . "#" . self::LAST_NAME_MAX_LENGTH => $billingAddress->getLastname(),
            self::COMPANY . "#" . self::COMPANY_MAX_LENGTH => $billingAddress->getCompany()
        ];

        $billingTruncatedResult = $this->_helper->getTruncatedString($billingFieldToBeTruncate);

        $billingResult = [
            self::FIRST_NAME => $billingTruncatedResult[self::FIRST_NAME],
            self::LAST_NAME => $billingTruncatedResult[self::LAST_NAME],
            self::COMPANY => $billingTruncatedResult[self::COMPANY]
        ];

        return [
            self::CUSTOMER => [
                self::CUSTOMER_ID => strval($identification),
                self::EMAIL => $customerEmail,
                self::FIRST_NAME => $billingResult[self::FIRST_NAME],
                self::LAST_NAME => $billingResult[self::LAST_NAME],
                self::COMPANY => $billingResult[self::COMPANY]
            ]
        ];
    }
}
