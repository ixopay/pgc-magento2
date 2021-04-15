<?php

namespace Pgc\Pgc\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Model\Method\Logger;

class GeneralResponseValidator extends AbstractValidator
{
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * GeneralResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param Logger $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Logger $logger
    ) {
        $this->logger = $logger;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = SubjectReader::readResponse($validationSubject);
        $isValid = true;
        $errorMessages = [];

        if (isset($response['success']) && empty($response['success'])) {
            $errorMsg = __('Error with payment method please select different payment method.');
            $errorCodes = $this->getErrorCodes($response);
            return $this->createResult(false, [__($errorMsg)], $errorCodes);
        }

        return $this->createResult($isValid, $errorMessages);
    }

    /**
     * @param $response
     * @return array
     */
    private function getErrorCodes($response): array
    {
        $codes = [];
        if (isset($response['errors']) && !empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $codes[] = $error->errorCode;
            }
        }

        return $codes;
    }
}
