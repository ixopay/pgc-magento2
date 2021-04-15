<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Pgc\Pgc\Gateway\Http\Client\TransactionVoid;
use Pgc\Pgc\Services\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionVoidTest extends TestCase
{
    /**
     * @var TransactionVoid
     */
    private TransactionVoid $voidModel;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var Service|MockObject
     */
    private $serviceMock;

    /**
     * Run test placeRequest method
     *
     * @return void
     */
    public function testPlaceRequestSuccess()
    {
        $response = $this->getResponseArray();

        $this->serviceMock->expects($this->once())
            ->method('void')
            ->with($this->getTransferData())
            ->willReturn($response);

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'response' => $response
                ]
            );

        $actualResult = $this->voidModel->placeRequest($this->getTransferObjectMock());

        $this->assertIsArray($actualResult);
        $this->assertEquals($response, $actualResult);
    }

    /**
     * @return int[]
     */
    private function getResponseArray(): array
    {
        return ['success' => 1];
    }

    /**
     * @return array
     */
    private function getTransferData(): array
    {
        return [
            'merchantTransactionId' => 'ORD000000144',
            'amount' => '49.00',
            'currency' => 'CHF'
        ];
    }

    /**
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->serviceMock = $this->getMockBuilder(Service::class)->disableOriginalConstructor()
            ->getMock();

        $this->voidModel = new TransactionVoid($this->loggerMock, $this->serviceMock);
    }
}
