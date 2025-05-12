<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WeatherService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $params
    ) {
        $this->apiKey = $this->params->get('app.weather_api_key');
        $this->apiUrl = $this->params->get('app.weather_api_url');
    }

    public function getWeatherData(string $city): array
    {
        try {
            $response = $this->httpClient->request('GET', "{$this->apiUrl}/current.json", [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'lang' => 'uk'
                ]
            ]);

            $data = $response->toArray();

            $result = [
                'city' => $data['location']['name'],
                'country' => $data['location']['country'],
                'temperature' => $data['current']['temp_c'],
                'condition' => $data['current']['condition']['text'],
                'humidity' => $data['current']['humidity'],
                'wind_speed' => $data['current']['wind_kph'],
                'last_updated' => $data['current']['last_updated'],
            ];

            $this->logger->info("Погода в {$city}: {$result['temperature']}°C, {$result['condition']}");

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Помилка отримання даних про погоду: " . $e->getMessage());
            throw $e;
        }
    }
} 