<?php

namespace Pgc\Pgc\Test\Unit\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Pgc\Pgc\Gateway\Config\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    const METHOD_CODE = 'pgc';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @param string $encodedValue
     * @param string|array $value
     * @param array $expected
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     */
    public function testGetCountrySpecificCardTypeConfig($encodedValue, $value, array $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($encodedValue);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($encodedValue)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getCountrySpecificCardTypeConfig()
        );
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }

    /**
     * @return array
     */
    public function getCountrySpecificCardTypeConfigDataProvider()
    {
        return [
            'valid data' => [
                '{"GB":["VI","AE"],"US":["DI","JCB"]}',
                ['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']],
                ['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']]
            ],
            'non-array value' => [
                '""',
                '',
                []
            ]
        ];
    }

   

    /**
     * @covers       \Pgc\Pgc\Gateway\Config\Config::getCountryAvailableCardTypes
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     * @param string $encodedData
     * @param string|array $data
     * @param array $countryData
     */
    public function testCountryAvailableCardTypes($encodedData, $data, array $countryData)
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($encodedData);

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->with($encodedData)
            ->willReturn($data);

        foreach ($countryData as $countryId => $types) {
            $result = $this->model->getCountryAvailableCardTypes($countryId);
            static::assertEquals($types, $result);
        }

        if (empty($countryData)) {
            static::assertEquals($data, "");
        }
    }

    /**
     * @covers \Pgc\Pgc\Gateway\Config\Config::isCvvEnabled
     */
    public function testUseCvv()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with(1)
            ->willReturn(1);

        static::assertTrue($this->model->isCvvEnabled());
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getCurrencyDataProvider
     */
    public function testgetCurrency($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_CURRENCY), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getCurrency()
        );
    }

    /**
     * @return array
     */
    public function getCurrencyDataProvider()
    {
        return [
            ['value' => 'USD', 'expected' => 'USD'],
            ['value' => 'AUD', 'expected' => 'AUD'],
        ];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getEnvironmentDataProvider
     */
    public function testgetEnvironment($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_ENVIRONMENT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getEnvironment()
        );
    }

    /**
     * @return array
     */
    public function getEnvironmentDataProvider()
    {
        return [
            ['value' => 'sandbox', 'expected' => 'sandbox'],
            ['value' => 'production', 'expected' => 'production'],
        ];
    }

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->serializerMock = $this->createMock(Json::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'methodCode' => self::METHOD_CODE,
                'serializer' => $this->serializerMock
            ]
        );
    }
}
