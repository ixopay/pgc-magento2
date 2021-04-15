<?php

namespace Pgc\Pgc\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Pgc\Pgc\Helper\Data;

class VaultDataBuilder implements BuilderInterface
{
    const REFERENCE_UUID = 'referenceUuid';
    const TRANSACTION_INDICATOR = 'transactionIndicator';

    /**
     * @var Data
     */
    protected Data $_helper;

    /**
     * VaultDataBuilder constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        //$data = $payment->getAdditionalInformation();
        /** @var PaymentTokenInterface $token */
        $token = $payment->getExtensionAttributes()->getVaultPaymentToken();
        $area = $this->_helper->getShoppingArea();

        if ($token) {
            return [
                self::REFERENCE_UUID => $token->getGatewayToken(),
                self::TRANSACTION_INDICATOR => $area == 'adminhtml' ? 'CARDONFILE-MERCHANT-INITIATED' : 'CARDONFILE'
            ];
        }

        return [];
    }
}
