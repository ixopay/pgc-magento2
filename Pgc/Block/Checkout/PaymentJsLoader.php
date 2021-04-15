<?php
declare(strict_types=1);

namespace Pgc\Pgc\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Pgc\Pgc\Helper\Data;

class PaymentJsLoader extends Template
{
    /**
     * @var Data
     */
    private Data $helper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Data $helper ,
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    public function getHost()
    {
        return $this->helper->getHostUrl();
        //return $this->helper->getGeneralConfigData('host');
    }
}
