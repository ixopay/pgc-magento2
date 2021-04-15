<?php

namespace Pgc\Pgc\Gateway\Request;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Pgc\Pgc\Helper\Data as Helper;

class ThreeDSecureDataBuilder implements BuilderInterface
{
    use Formatter;

    const THREE_D_SECURE_DATA = 'threeDSecureData';

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * Constructor
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $result = [];
        $shoppingArea = $this->helper->getShoppingArea();

        if ($this->helper->getThreeDSecureVerification() === 'OFF') {
            $result[self::THREE_D_SECURE_DATA] = [
                '3dsecure' => 'OFF'
            ];
            return $result;
        }

        // disable 3d secure for Magento admin
        if ($shoppingArea == 'adminhtml') {
            $result[self::THREE_D_SECURE_DATA] = [
                '3dsecure' => 'OFF'
            ];
            return $result;
        }

        /** @var PaymentTokenInterface $token */
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        if ($shoppingArea == 'frontend' || $shoppingArea == 'webapi_rest') {
            $token = $payment->getExtensionAttributes();
            $paymentAccountAgeIndicator = '01';

            if ($token !== null) {
                /** @var PaymentTokenInterface $vaultToken */
                $vaultToken = $token->getVaultPaymentToken();
                $tokenCreatedAt = $vaultToken ? $vaultToken->getCreatedAt() : null;
                $paymentAccountAgeIndicator = $this->getPaymentAccountAgeIndicator($tokenCreatedAt);
            }

            $customer3dInfo = $this->helper->getCustomer3dInfo();
            $billingShippingAddressMatch = $this->matchBillShip($order);

            $result[self::THREE_D_SECURE_DATA] = [
                'channel' => $customer3dInfo['3ds:channel'],
                'transType' => $customer3dInfo['3ds:transType'],
                'challengeIndicator' => $customer3dInfo['3ds:challengeIndicator'],
                'authenticationIndicator' => $customer3dInfo['3ds:authenticationIndicator'],
                'paymentAccountAgeIndicator' => $paymentAccountAgeIndicator,
                'billingShippingAddressMatch' => $billingShippingAddressMatch,
                '3dsecure' => $customer3dInfo['3dsecure']
            ];

            if (isset($customer3dInfo['email'])) {
                $result[self::THREE_D_SECURE_DATA]['deliveryEmailAddress'] = $customer3dInfo['email'];
            }

            if (isset($customer3dInfo['3ds:cardholderAccountDate'])) {
                $result[self::THREE_D_SECURE_DATA]['cardholderAccountDate'] = $customer3dInfo['3ds:cardholderAccountDate'];
            }

            if (isset($customer3dInfo['3ds:cardholderAccountChangeIndicator'])) {
                $result[self::THREE_D_SECURE_DATA]['cardholderAccountChangeIndicator'] = $customer3dInfo['3ds:cardholderAccountChangeIndicator'];
            }

            if (isset($customer3dInfo['3ds:cardholderAccountLastChange'])) {
                $result[self::THREE_D_SECURE_DATA]['cardholderAccountLastChange'] = $customer3dInfo['3ds:cardholderAccountLastChange'];
            }
        }

        return $result;
    }

    /**
     * @param $createdAt
     * @return string
     */
    private function getPaymentAccountAgeIndicator($createdAt): string
    {
        if (!$createdAt) {
            return '01';
        }

        $current_date = strtotime(date('Y-m-d H:i:s'));
        $customer_account_date = strtotime($createdAt);
        $dateDiff = $current_date - $customer_account_date;
        $roundedDateDiffInDays = $dateDiff / (60 * 60 * 24);

        if ($roundedDateDiffInDays < 1) {
            return '02';
        }

        if ($roundedDateDiffInDays <= 30) {
            return '03';
        }

        if ($roundedDateDiffInDays > 30 && $roundedDateDiffInDays <= 60) {
            return '04';
        }

        if ($roundedDateDiffInDays > 60) {
            return '05';
        }

        return '01';
    }

    /**
     * @param OrderAdapterInterface $order
     * @return string
     */
    private function matchBillShip(OrderAdapterInterface $order): string
    {
        try{
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();

            if (!$billingAddress && !$shippingAddress) {
                return 'N';
            }

            $billingArray = [
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
                'postCode' => $billingAddress->getPostcode(),
                'regionCode' => $billingAddress->getRegionCode(),
                'streetLineOne' => $billingAddress->getStreetLine1(),
                'streetLineTwo' => $billingAddress->getStreetLine2(),
                'telephone' => $billingAddress->getTelephone(),
                'cid' => $billingAddress->getCountryId(),
                'city' => $billingAddress->getCity()
            ];

            $shippingArray = [
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'postCode' => $shippingAddress->getPostcode(),
                'regionCode' => $shippingAddress->getRegionCode(),
                'streetLineOne' => $shippingAddress->getStreetLine1(),
                'streetLineTwo' => $shippingAddress->getStreetLine2(),
                'telephone' => $shippingAddress->getTelephone(),
                'cid' => $shippingAddress->getCountryId(),
                'city' => $shippingAddress->getCity()
            ];

            if ($billingArray === $shippingArray) {
                return 'Y';
            }
            return 'N';
        } catch (Exception $e) {
            return 'N';
        }
    }
}
