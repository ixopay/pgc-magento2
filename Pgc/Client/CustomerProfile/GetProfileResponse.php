<?php

namespace Pgc\Client\CustomerProfile;

use Pgc\Client\Json\ResponseObject;

/**
 * Class GetProfileResponse
 *
 * @package Pgc\Client\CustomerProfile
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
