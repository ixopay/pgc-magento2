<?php

namespace CloudPay\Client\CustomerProfile;

use CloudPay\Client\Json\ResponseObject;

/**
 * Class GetProfileResponse
 *
 * @package CloudPay\Client\CustomerProfile
 *
 * @property bool $profileExists
 * @property string $profileGuid
 * @property string $customerIdentification
 * @property string $preferredMethod
 * @property CustomerData $customer
 * @property PaymentInstrument[] $paymentInstruments
 */
class GetProfileResponse extends ResponseObject {

}
