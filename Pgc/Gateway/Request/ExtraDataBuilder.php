<?php

namespace Pgc\Pgc\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pgc\Pgc\Helper\Data as Helper;

class ExtraDataBuilder implements BuilderInterface
{
    const EXTRA_DATA = 'extraData';
    const COUNTRY_CODE = 'countryCode';

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
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['payment']) || !$buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        return [
            self::EXTRA_DATA => [
                self::COUNTRY_CODE => $this->helper->getCountryByWebsite()
            ]
        ];
    }
}
