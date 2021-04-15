<?php

namespace Pgc\Pgc\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ThreeDSecure implements OptionSourceInterface
{
    const THREE_D_OFF = 'OFF';
    const THREE_D_OPTIONAL = 'OPTIONAL';
    const THREE_D_MANDATORY = 'MANDATORY';

    /**
     * Possible 3D-Secure Authentication types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::THREE_D_OFF,
                'label' => 'Off',
            ],
            [
                'value' => self::THREE_D_OPTIONAL,
                'label' => 'Optional'
            ],
            [
                'value' => self::THREE_D_MANDATORY,
                'label' => 'Mandatory'
            ]
        ];
    }
}
