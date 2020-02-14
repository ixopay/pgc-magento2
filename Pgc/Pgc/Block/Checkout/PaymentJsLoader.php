<?php
declare(strict_types=1);

namespace Pgc\Pgc\Block\Checkout;

use Magento\Framework\View\Element\Template;

class PaymentJsLoader extends Template
{
    /**
     * @var \Pgc\Pgc\Helper\Data
     */
    private $pgcHelper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param \Pgc\Pgc\Helper\Data $pgcHelper,
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Pgc\Pgc\Helper\Data $pgcHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pgcHelper = $pgcHelper;
    }

    public function getHost()
    {
        return $this->pgcHelper->getGeneralConfigData('host');
    }
}
