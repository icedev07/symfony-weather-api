<?php

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherServiceTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $params;
    private $weatherService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->params = $this->createMock(ParameterBagInterface::class);

        $this->params->method('get')
            ->willReturnMap([
                ['app.weather_api_key', 'test_api_key'],
                ['app.weather_api_url', 'https://api.weatherapi.com/v1'],
            ]);

        $this->weatherService = new WeatherService(
            $this->httpClient,
            $this->logger,
            $this->params
        );
    }

    public function testGetWeatherData()
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')
            ->willReturn([
                'location' => [
                    'name' => 'London',
                    'country' => 'UK',
                ],
                'current' => [
                    'temp_c' => 20,
                    'condition' => ['text' => 'Sunny'],
                    'humidity' => 65,
                    'wind_kph' => 15,
                    'last_updated' => '2024-03-20 12:00',
                ],
            ]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->weatherService->getWeatherData('London');

        $this->assertEquals('London', $result['city']);
        $this->assertEquals('UK', $result['country']);
        $this->assertEquals(20, $result['temperature']);
        $this->assertEquals('Sunny', $result['condition']);
    }
} 