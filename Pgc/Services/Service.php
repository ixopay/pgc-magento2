<?php

namespace Pgc\Pgc\Services;

use Exception;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Pgc\Pgc\Helper\Data;
use Psr\Log\LoggerInterface;

class Service
{
    /**
     * API request preauthorize endpoint
     */
    const API_AUTHORIZE_ENDPOINT = '/preauthorize';

    /**
     * API request capture endpoint
     */
    const API_CAPTURE_ENDPOINT = '/capture';

    /**
     * API request void endpoint
     */
    const API_VOID_ENDPOINT = '/void';

    /**
     * API request debit endpoint
     */
    const API_DEBIT_ENDPOINT = '/debit';

    /**
     * API request refund endpoint
     */
    const API_REFUND_ENDPOINT = '/refund';

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $_logger;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * Service constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->helper = $helper;
        $this->_logger = $logger;
    }

    /**
     * @param $body
     * @return array
     */
    public function authorize($body): array
    {
        $uri = $this->getApiRequestUri() . $this->getApiKey() . static::API_AUTHORIZE_ENDPOINT;
        $header = $this->prepareHeader($body, static::API_AUTHORIZE_ENDPOINT);
        $requestBody = array_merge_recursive($header, ['json' => $body]);
        $response = $this->doRequest(
            $uri,
            $requestBody
        );

        $responseBody = (string)$response->getBody()->getContents();
        $this->_logger->debug(print_r(json_decode($responseBody), true));
        return (array)json_decode($responseBody);
    }

    /**
     * @return string
     */
    private function getApiRequestUri(): string
    {
        return $this->helper->getApiUri();
    }

    /**
     * @return string
     */
    private function getApiKey(): string
    {
        $paymentMethod = 'pgc_creditcard';
        return $this->helper->getPaymentConfigData('api_key', $paymentMethod);
    }

    /**
     * @param $body
     * @param $transactionType
     * @return array
     */
    private function prepareHeader($body, $transactionType): array
    {
        $additionalHeaderData = $this->helper->getAdditionalHeaderData();

        if ($this->helper->checkSignatureFlag()) {
            $xSignatureData = $this->helper->getXSignatureData($body, $transactionType);
            return [
                'http_errors' => false,
                'auth' => [
                    $this->getUsername(),
                    $this->getPassword()
                ],
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json',
                    'X-Source-Platform' => $additionalHeaderData['header_x_source_platform'],
                    'X-SDK-PlatformVersion' => $additionalHeaderData['header_x_sdk_platform_version'],
                    'X-SDK-Type' => $additionalHeaderData['header_x_sdk_type'],
                    'X-SDK-Version' => $additionalHeaderData['header_x_sdk_version'],
                    'X-Signature' => $xSignatureData['signature_hmac_data'],
                    'Date' => $xSignatureData['signature_time_stamp']
                ]
            ];
        }

        return [
            'http_errors' => false,
            'auth' => [
                $this->getUsername(),
                $this->getPassword()
            ],
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'X-Source-Platform' => $additionalHeaderData['header_x_source_platform'],
                'X-SDK-PlatformVersion' => $additionalHeaderData['header_x_sdk_platform_version'],
                'X-SDK-Type' => $additionalHeaderData['header_x_sdk_type'],
                'X-SDK-Version' => $additionalHeaderData['header_x_sdk_version']
            ]
        ];
    }

    /**
     * @return string
     */
    private function getUsername(): string
    {
        return $this->helper->getGeneralConfigData('username');
    }

    /**
     * @return string
     */
    private function getPassword(): string
    {
        return $this->helper->getGeneralConfigData('password');
    }

    /**
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_POST
    ): Response {
        $client = $this->clientFactory->create();
        $this->_logger->debug($uriEndpoint);
        $this->_logger->debug(print_r($params, true));
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (Exception $exception) {
            $this->_logger->debug($exception->getMessage());
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        } catch (GuzzleException $exception) {
            $this->_logger->debug($exception->getMessage());
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * @param $body
     * @return array
     */
    public function capture($body): array
    {
        $uri = $this->getApiRequestUri() . $this->getApiKey() . static::API_CAPTURE_ENDPOINT;
        $header = $this->prepareHeader($body, static::API_CAPTURE_ENDPOINT);
        $requestBody = array_merge_recursive($header, ['json' => $body]);
        $response = $this->doRequest(
            $uri,
            $requestBody
        );

        $responseBody = (string)$response->getBody()->getContents();
        $this->_logger->debug(print_r(json_decode($responseBody), true));
        return (array)json_decode($responseBody);
    }

    /**
     * @param $body
     * @return array
     */
    public function debit($body): array
    {
        $uri = $this->getApiRequestUri() . $this->getApiKey() . static::API_DEBIT_ENDPOINT;
        $header = $this->prepareHeader($body, static::API_DEBIT_ENDPOINT);
        $requestBody = array_merge_recursive($header, ['json' => $body]);
        $response = $this->doRequest(
            $uri,
            $requestBody
        );

        $responseBody = (string)$response->getBody()->getContents();
        $this->_logger->debug(print_r(json_decode($responseBody), true));
        return (array)json_decode($responseBody);
    }

    /**
     * @param $body
     * @return array
     */
    public function void($body): array
    {
        $uri = $this->getApiRequestUri() . $this->getApiKey() . static::API_VOID_ENDPOINT;
        $header = $this->prepareHeader($body, static::API_VOID_ENDPOINT);
        $requestBody = array_merge_recursive($header, ['json' => $body]);
        $response = $this->doRequest(
            $uri,
            $requestBody
        );

        $responseBody = (string)$response->getBody()->getContents();
        $this->_logger->debug(print_r(json_decode($responseBody), true));
        return (array)json_decode($responseBody);
    }

    /**
     * @param $body
     * @return array
     */
    public function refund($body): array
    {
        $uri = $this->getApiRequestUri() . $this->getApiKey() . static::API_REFUND_ENDPOINT;
        $header = $this->prepareHeader($body, static::API_REFUND_ENDPOINT);
        $requestBody = array_merge_recursive($header, ['json' => $body]);
        $response = $this->doRequest(
            $uri,
            $requestBody
        );

        $responseBody = (string)$response->getBody()->getContents();
        $this->_logger->debug(print_r(json_decode($responseBody), true));
        return (array)json_decode($responseBody);
    }
}
