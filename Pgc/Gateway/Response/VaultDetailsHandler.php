<?php

namespace Pgc\Pgc\Gateway\Response;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Pgc\Pgc\Helper\Data;
use Psr\Log\LoggerInterface;

class VaultDetailsHandler implements HandlerInterface
{
    const ccTypes = [
        'visa' => 'VI',
        'mastercard' => 'MC',
        'amex' => 'AE',
        'discover' => 'DI',
        'jcb' => 'JCB',
        'maestrointernational' => 'MI'
    ];

    const CREDIT_CARD_CODE = 'pgc_creditcard';

    /**
     * @var PaymentTokenFactoryInterface
     */
    protected PaymentTokenFactoryInterface $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * VaultDetailsHandler constructor.
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param Json|null $serializer
     */
    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        LoggerInterface $logger,
        Data $helper,
        Json $serializer = null
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        if ($this->getSeamlessConfiguration()) {
            $paymentDO = SubjectReader::readPayment($handlingSubject);
            $payment = $paymentDO->getPayment();
            // add vault payment token entity to extension attributes
            $paymentToken = $this->getVaultPaymentToken($response);
            if (null !== $paymentToken) {
                $extensionAttributes = $this->getExtensionAttributes($payment);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * @return bool
     */
    private function getSeamlessConfiguration(): bool
    {
        return $this->helper->getPaymentConfigDataFlag(
            'seamless',
            self::CREDIT_CARD_CODE
        );
    }

    /**
     * @param $transaction
     * @return PaymentTokenInterface|null
     * @throws Exception
     */
    protected function getVaultPaymentToken($transaction): ?PaymentTokenInterface
    {
        $token = $transaction['uuid'];

        if (empty($token)) {
            return null;
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($this->getExpirationDate($transaction));
        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->getCreditCardType($transaction['returnData']->type),
            'maskedCC' => $transaction['returnData']->lastFourDigits,
            'expirationDate' => $transaction['returnData']->expiryMonth . '/' . $transaction['returnData']->expiryYear
        ]));

        return $paymentToken;
    }

    /**
     * @param $transaction
     * @return string
     * @throws Exception
     */
    private function getExpirationDate($transaction): string
    {
        $expiryYear = strval($transaction['returnData']->expiryYear);
        $expiryMonth = strval($transaction['returnData']->expiryMonth);
        $this->logger->debug($expiryYear);
        $this->logger->debug($expiryMonth);
        $expDate = new DateTime(
            $expiryYear
            . '-'
            . $expiryMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
        $expDate->add(new DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * @param $details
     * @return string
     */
    private function convertDetailsToJSON($details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }

    /**
     * @param $type
     * @return string
     */
    private function getCreditCardType($type): string
    {
        if (isset(self::ccTypes[$type]) && !empty(self::ccTypes[$type])) {
            return self::ccTypes[$type];
        }

        return $type;
    }

    /**
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
