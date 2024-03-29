<?php
namespace Pgc\Pgc\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_LIVE_PUBLISHABLE_KEY = 'live_publishable_key';
    const KEY_LIVE_SECRET_KEY = 'live_secret_key';
    const KEY_TEST_PUBLISHABLE_KEY = 'test_publishable_key';
    const KEY_TEST_SECRET_KEY = 'test_secret_key';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CURRENCY = 'currency';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_CC_TYPES_PAYMENT_MAPPER = 'cctypes_pgc_mapper';
    const KEY_ALLOW_SPECIFIC = 'allowspecific';
    const KEY_SPECIFIC_COUNTRY = 'specificcountry';
    const KEY_SDK_URL = 'sdk_url';
    const RECEIPT_EMAIL = 'receipt_email';


    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Payment config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Return the country specific card type config
     *
     * @param int|null $storeId
     * @return array
     */
    public function getCountrySpecificCardTypeConfig($storeId = null)
    {
        $countryCardTypes = $this->getValue(self::KEY_COUNTRY_CREDIT_CARD, $storeId);
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Gets list of card types available for country.
     *
     * @param string $country
     * @param int|null $storeId
     * @return array
     */
    public function getCountryAvailableCardTypes($country, $storeId = null)
    {
        $types = $this->getCountrySpecificCardTypeConfig($storeId);

        return (!empty($types[$country])) ? $types[$country] : [];
    }

    /**
     * Checks if cvv field is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCvvEnabled($storeId = null)
    {
        return true;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurrency($storeId = null)
    {
        return $this->getValue(self::KEY_CURRENCY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     * @deprecated
     * @see isCvvEnabled($storeId = null)
     */
    public function isCcvEnabled($storeId = null)
    {
        return $this->isCvvEnabled($storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(Config::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getPublishableKey($storeId = null)
    {
        if ($this->isTestMode()) {
            return $this->getValue(self::KEY_TEST_PUBLISHABLE_KEY, $storeId);
        }

        return $this->getValue(self::KEY_LIVE_PUBLISHABLE_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSecretKey($storeId = null)
    {
        if ($this->isTestMode()) {
            return $this->getValue(self::KEY_TEST_SECRET_KEY, $storeId);
        }

        return $this->getValue(self::KEY_LIVE_SECRET_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isTestMode($storeId = null)
    {
        return true;//$this->getEnvironment($storeId) == Environment::ENVIRONMENT_TEST;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReceiptEmailEnabled($storeId = null)
    {
        return (bool) $this->getValue(self::RECEIPT_EMAIL, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getSdkUrl($storeId = null)
    {
        return $this->getValue(self::KEY_SDK_URL, $storeId);
    }
}
