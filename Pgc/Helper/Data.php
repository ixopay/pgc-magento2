<?php

namespace Pgc\Pgc\Helper;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Region;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const SUCCESS_URL = 'checkout/onepage/success';
    const PAYMENT_REDIRECT_CANCEL = 'pgc/payment/redirect?status=cancel';
    const PAYMENT_REDIRECT_ERROR = 'pgc/payment/redirect?status=error';
    const PAYMENT_CALLBACK = 'pgc/payment/callback';
    const SEAMLESS_INTEGRATION = 'payment/pgc_creditcard/seamless';
    const THREED_SECURE_VERIFICATION = 'payment/pgc_creditcard/use_3dsecure';
    const ENABLE_SIGNATURE = 'payment/pgc_creditcard/signature';
    const CURRENT_TRANSACTION_FLAG_IN_MINUTE = 1;
    const MERCHANT_SECRET_KEY = 'payment/pgc_creditcard/shared_secret';
    const MERCHANT_API_KEY = 'payment/pgc_creditcard/api_key';

    const SUCCESS_URL_MAX_LENGTH = 512;
    const CANCEL_URL_MAX_LENGTH = 512;
    const ERROR_URL_MAX_LENGTH = 512;
    const CALLBACK_URL_MAX_LENGTH = 512;

    const SIGNATURE_ALGO = 'sha512';
    const SIGNATURE_HTTP_METHOD = 'POST';
    const SIGNATURE_CONTENT_TYPE = 'application/json; charset=utf-8';
    const MODULE_PAYMENT = 'Pgc_Pgc';
    const HEADER_X_SOURCE_PLATFORM = 'magento';
    const HEADER_SDK_TYPE = 'magento_plugin';
    const FRONTEND_TRANSACTION_INDICATOR = 'SINGLE';
    const BACKEND_TRANSACTION_INDICATOR = 'MOTO';
    const SANDBOX_URL = 'https://sandbox.ixopay.com/api/v3/transaction/';
    const PROD_URL = 'https://gateway.ixopay.com/api/v3/transaction/';
    const SANDBOX_HOST_URL = 'https://sandbox.ixopay.com/';
    const PROD_HOST_URL = 'https://gateway.ixopay.com/';
    const ENCRYPTED_CONFIG_FIELDS = ['api_key', 'shared_secret', 'integration_key', 'password'];
    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var State
     */
    protected State $_state;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $_encryptor;

    /**
     * @var ModuleListInterface
     */
    protected ModuleListInterface $_moduleList;

    /**
     * @var ProductMetadataInterface
     */
    protected ProductMetadataInterface $productMetadata;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var Region
     */
    protected Region $regionModel;

    /**
     * @var StringUtils
     */
    protected StringUtils $string;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected \Magento\Checkout\Model\Session $session;

    /**
     * @var CountryFactory
     */
    protected CountryFactory $countryFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CountryFactory $countryFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Session $customerSession
     * @param Region $regionModel
     * @param State $state
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param StringUtils $string
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CountryFactory $countryFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        Session $customerSession,
        Region $regionModel,
        State $state,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        StringUtils $string,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeManager = $storeManager;
        $this->countryFactory = $countryFactory;
        $this->session = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->regionModel = $regionModel;
        $this->_state = $state;
        $this->scopeConfig = $scopeConfig;
        $this->_encryptor = $encryptor;
        $this->string = $string;
        $this->_moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param $paymentMethodCode
     * @param null $storeId
     * @return bool|mixed
     */
    public function getPaymentConfigDataFlag($field, $paymentMethodCode, $storeId = null): bool
    {
        return $this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId, true);
    }

    /**
     * @param $field
     * @param $path
     * @param null $storeId
     * @param false $flag
     * @return mixed
     */
    public function getConfigData($field, $path, $storeId = null, $flag = false)
    {
        $path .= '/' . $field;

        if (!$flag) {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
        }
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfigData($field, $storeId = null)
    {
        return $this->getConfigData($field, 'pgc/general', $storeId);
        /* if (in_array($field, self::ENCRYPTED_CONFIG_FIELDS)) {
            return $this->_encryptor->decrypt($this->getConfigData($field, 'pgc/general', $storeId));
        } else {
            return $this->getConfigData($field, 'pgc/general', $storeId);
        } */
    }

    /**
     * @param $field
     * @param $paymentMethodCode
     * @param null $storeId
     * @return mixed
     */
    public function getPaymentConfigData($field, $paymentMethodCode, $storeId = null)
    {
        return $this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId);
        /* if (in_array($field, self::ENCRYPTED_CONFIG_FIELDS)) {
            return $this->_encryptor->decrypt($this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId));
        } else {
            return $this->getConfigData($field, 'payment/' . $paymentMethodCode, $storeId);
        } */
    }

    /**
     * @return mixed
     */
    public function seamlessIntegrationCheck()
    {
        return $this->scopeConfig->getValue(
            self::SEAMLESS_INTEGRATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function checkSignatureFlag()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_SIGNATURE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSuccessUrl(): string
    {
        $successUrl = $this->_storeManager->getStore()->getBaseUrl() . self::SUCCESS_URL;
        return $this->getTruncateString($successUrl, self::SUCCESS_URL_MAX_LENGTH);
    }

    /**
     * @param null $stringValue
     * @param null $length
     * @return string
     */
    public function getTruncateString($stringValue = null, $length = null): string
    {
        return $this->string->substr($stringValue, 0, $length);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCancelUrl(): string
    {
        $cancelUrl = $this->_storeManager->getStore()->getBaseUrl() . self::PAYMENT_REDIRECT_CANCEL;
        return $this->getTruncateString($cancelUrl, self::CANCEL_URL_MAX_LENGTH);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getErrorUrl(): string
    {
        $errorUrl = $this->_storeManager->getStore()->getBaseUrl() . self::PAYMENT_REDIRECT_ERROR;
        return $this->getTruncateString($errorUrl, self::ERROR_URL_MAX_LENGTH);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCallbackUrl(): string
    {
        $callbackUrl = $this->_storeManager->getStore()->getBaseUrl() . self::PAYMENT_CALLBACK;
        return $this->getTruncateString($callbackUrl, self::CALLBACK_URL_MAX_LENGTH);
    }

    /**
     * @param $billingAddressRegionCode
     * @param $billingAddressCountryCode
     * @return string
     */
    public function getBillingRegionName($billingAddressRegionCode, $billingAddressCountryCode): string
    {
        $billingRegionId = $this->regionModel->loadByCode(
            $billingAddressRegionCode,
            $billingAddressCountryCode
        )->getId();

        return $this->regionModel->load($billingRegionId)->getName();
    }

    /**
     * @param $shippingAddressRegionCode
     * @param $shippingAddressCountryId
     * @return string
     */
    public function getShippingRegionName($shippingAddressRegionCode, $shippingAddressCountryId): string
    {
        $shippingRegionId = $this->regionModel->loadByCode(
            $shippingAddressRegionCode,
            $shippingAddressCountryId
        )->getId();

        return $this->regionModel->load($shippingRegionId)->getName();
    }

    /**
     * @return array
     */
    public function getCustomer3dInfo(): array
    {
        $customer3dInfo = [];
        $customer3dInfo['3ds:channel'] = '02';
        $customer3dInfo['3ds:transType'] = '01';
        $customer3dInfo['3ds:challengeIndicator'] = '02';
        $customer3dInfo['3ds:authenticationIndicator'] = '01';
        $customer3dInfo['3ds:paymentAccountAgeIndicator'] = '01';

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $customer3dInfo['3ds:cardholderAccountAgeIndicator'] = $this->getCardholderAccountAgeIndicator($customer);
            $customer3dInfo['3ds:cardholderAccountDate'] = $this->getCardholderAccountDate($customer);
            $customer3dInfo['3ds:cardholderAccountChangeIndicator'] = $this->getCardholderAccountChangeIndicator($customer);
            $customer3dInfo['3ds:cardholderAccountLastChange'] = $this->getCardholderAccountLastChange($customer);
            $customer3dInfo['3dsecure'] = $this->getThreeDSecureVerification();
            $customer3dInfo['email'] = $customer->getEmail();
        } else {
            $customer3dInfo['3ds:cardholderAccountAgeIndicator'] = '01';
            $customer3dInfo['3dsecure'] = $this->getThreeDSecureVerification();
        }
        return $customer3dInfo;
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCardholderAccountAgeIndicator($customer): string
    {
        $current_date = strtotime(date('Y-m-d H:i:s')); // or your date as well
        $customer_account_date = strtotime($customer->getCreatedAt());
        $datediff = $current_date - $customer_account_date;
        $roundedDateDiffInDays = $datediff / (60 * 60 * 24);

        if ($roundedDateDiffInDays < 1) {
            $convertDaysIntoMinutes = $roundedDateDiffInDays * (24 * 60);
            if ($convertDaysIntoMinutes <= self::CURRENT_TRANSACTION_FLAG_IN_MINUTE) {
                return '02';
            } else {
                return '03';
            }
        }

        if ($roundedDateDiffInDays < 30) {
            return '03';
        }

        if ($roundedDateDiffInDays > 30 && $roundedDateDiffInDays < 60) {
            return '04';
        }

        if ($roundedDateDiffInDays > 60) {
            return '05';
        }

        return '01';
    }

    /**
     * @param $customer
     * @return mixed|string
     */
    private function getCardholderAccountDate($customer): string
    {
        $customer_created_date = explode(" ", $customer->getCreatedAt());
        return $customer_created_date[0];
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCardholderAccountChangeIndicator($customer): string
    {
        $current_date = strtotime(date('Y-m-d H:i:s'));
        $customer_account_date = strtotime($customer->getUpdatedAt());
        $datediff = $current_date - $customer_account_date;
        $roundedDateDiffInDays = $datediff / (60 * 60 * 24);

        if ($roundedDateDiffInDays < 1) {
            $convertDaysIntoMinutes = $roundedDateDiffInDays * (24 * 60);
            if ($convertDaysIntoMinutes <= self::CURRENT_TRANSACTION_FLAG_IN_MINUTE) {
                return '01';
            } else {
                return '02';
            }
        }

        if ($roundedDateDiffInDays < 30) {
            return '02';
        }

        if ($roundedDateDiffInDays > 30 && $roundedDateDiffInDays < 60) {
            return '03';
        }

        if ($roundedDateDiffInDays > 60) {
            return '04';
        }

        return '01';
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCardholderAccountLastChange($customer): string
    {
        $customer_updated_date = explode(" ", $customer->getUpdatedAt());
        return $customer_updated_date[0];
    }

    /**
     * Three d secure verification
     *
     * @return string
     */
    public function getThreeDSecureVerification(): string
    {
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            self::THREED_SECURE_VERIFICATION,
            $scope
        );
    }

    /**
     * @return false|string
     */
    public function getShopperIPAddress()
    {
        return $this->_remoteAddress->getRemoteAddress();
    }

    /**
     * @param $fieldsToBeTruncate
     * @return array
     */
    public function getTruncatedString($fieldsToBeTruncate): array
    {
        $result = [];
        foreach ($fieldsToBeTruncate as $fieldLengthKey => $fieldVal) {
            $explodeFieldLength = explode("#", $fieldLengthKey);
            $result[$explodeFieldLength[0]] = $this->string->substr($fieldVal, 0, $explodeFieldLength[1]);
        }
        return $result;
    }

    /**
     * @param $fieldsToBeTruncate
     * @return array
     */
    public function getShippingTruncatedString($fieldsToBeTruncate): array
    {
        $result = [];
        foreach ($fieldsToBeTruncate as $fieldLengthKey => $fieldVal) {
            $explodeFieldLength = explode("#", $fieldLengthKey);
            $result[$explodeFieldLength[0]] = $this->string->substr($fieldVal, 0, $explodeFieldLength[1]);
        }
        return $result;
    }

    /**
     * @param $body
     * @param $transactionType
     * @return array
     */
    public function getXSignatureData($body, $transactionType): array
    {
        $xSignatureData = [];
        $currentTimeStamp = $this->getCurrentTimeStamp();
        $xSignatureData['signature_time_stamp'] = $currentTimeStamp;
        $requestUrl = $this->getSignatureRequestURL($transactionType);
        $md5Body = md5(json_encode($body));
        $messageBody = $this->getMessageBody($md5Body, $currentTimeStamp, $requestUrl);
        $xSignatureData['signature_hmac_data'] = $this->getSignatureHmacData($messageBody);

        return $xSignatureData;
    }

    /**
     * @return string
     */
    public function getCurrentTimeStamp(): string
    {
        $time = time();
        $check = $time + date("Z", $time);
        return strftime("%a, %d %b %Y %H:%M:%S UTC", $check);
    }

    /**
     * Return request url
     * @param $transactionType
     * @return string
     */
    public function getSignatureRequestURL($transactionType): string
    {
        return '/api/v3/transaction/' . $this->getPgcConfigValue(self::MERCHANT_API_KEY) . $transactionType;
    }

    /**
     * @param $configField
     * @return string
     */
    public function getPgcConfigValue($configField): string
    {
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($configField, $scope);
        /* return $this->_encryptor->decrypt(
            $this->scopeConfig->getValue(
                $configField,
                $scope
            )
        ); */
    }

    /**
     * @param $md5Body
     * @param $timeStamp
     * @param $requestUrl
     * @return string
     */
    private function getMessageBody($md5Body, $timeStamp, $requestUrl): string
    {
        $signatureMethod = self::SIGNATURE_HTTP_METHOD;
        $contentType = self::SIGNATURE_CONTENT_TYPE;
        $messageBody = "$signatureMethod\n";
        $messageBody .= "$md5Body\n";
        $messageBody .= "$contentType\n";
        $messageBody .= "$timeStamp\n";
        $messageBody .= $requestUrl;

        return $messageBody;
    }

    /**
     * @param $messageBody
     * @return string
     */
    private function getSignatureHmacData($messageBody): string
    {
        $signatureKey = $this->getPgcConfigValue(self::MERCHANT_SECRET_KEY);
        $hashMacData = hash_hmac(self::SIGNATURE_ALGO, $messageBody, $signatureKey, true);
        return base64_encode($hashMacData);
    }

    /**
     * Return x-Signature Key
     * @param $request
     * @return string
     */
    public function getResponseXSignature($request): string
    {
        $md5ResponseBody = md5($request->getContent());
        $responseDate = $request->getHeader('Date');
        $responseCallbackUrl = '/' . self::PAYMENT_CALLBACK;
        $messageBody = $this->getMessageBody($md5ResponseBody, $responseDate, $responseCallbackUrl);

        return $this->getSignatureHmacData($messageBody);
    }

    /**
     * Return additional header data
     * @return array
     */
    public function getAdditionalHeaderData(): array
    {
        $additionalHeaderArray = [];
        $additionalHeaderArray['header_x_source_platform'] = self::HEADER_X_SOURCE_PLATFORM;
        $additionalHeaderArray['header_x_sdk_platform_version'] = $this->getMagentoShopVersion();
        $additionalHeaderArray['header_x_sdk_type'] = self::HEADER_SDK_TYPE;
        $additionalHeaderArray['header_x_sdk_version'] = $this->getModuleVersion();

        return $additionalHeaderArray;
    }

    /**
     * Return current magento version
     * @return string
     */
    public function getMagentoShopVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Return module version
     * @return string
     */
    public function getModuleVersion(): string
    {
        return $this->_moduleList->getOne(self::MODULE_PAYMENT)['setup_version'];
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getTransactionIndicatorVal(): string
    {
        $transactionIndicator = '';
        $areaInfo = $this->getShoppingArea();
        if ($areaInfo == 'frontend' || $areaInfo == 'webapi_rest') {
            $transactionIndicator = self::FRONTEND_TRANSACTION_INDICATOR;
        } elseif ($areaInfo == 'adminhtml') {
            $transactionIndicator = self::BACKEND_TRANSACTION_INDICATOR;
        }
        return $transactionIndicator;
    }

    /**
     * Return which area is using
     * @throws LocalizedException
     */
    public function getShoppingArea(): string
    {
        return $this->_state->getAreaCode();
    }

    /**
     * Return Api request Uri
     * @return string
     */
    public function getApiUri(): string
    {
        $apiURL = self::PROD_URL;
        if ($this->isSandboxMode()) {
            $apiURL = self::SANDBOX_URL;
        }

        return $apiURL;
    }

    /**
     * Return host request Uri
     * @return string
     */
    public function getHostUrl(): string
    {
        $hostUrl = self::PROD_HOST_URL;
        if ($this->isSandboxMode()) {
            $hostUrl = self::SANDBOX_HOST_URL;
        }

        return $hostUrl;
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->scopeConfig->getValue('pgc/general/sandbox', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
