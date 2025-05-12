<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    #[Route('/weather', name: 'weather_form', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig');
    }

    #[Route('/weather/show', name: 'weather_show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        $city = $request->query->get('city', 'London');
        
        try {
            $weatherData = $this->weatherService->getWeatherData($city);
            return $this->render('weather/show.html.twig', [
                'weather' => $weatherData
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to fetch weather data: ' . $e->getMessage());
            return $this->redirectToRoute('weather_form');
        }
    }
} 