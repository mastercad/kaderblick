<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Location;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/locations', name: 'api_locations_')]
class LocationsController extends ApiController
{
    protected string $entityName = 'Location';
    protected string $entityNamePlural = 'Locations';
    protected string $entityClass = Location::class;
    protected string $urlPart = 'locations';
    protected array $relations = [
        'surfaceType' => ['type' => 2, 'entityName' => 'SurfaceType']
    ];

    #[Route('/osm-coordinates', name: 'api_locations_osm_coordinates', methods: ['GET'])]
    public function osmCoordinates(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $query = $request->query->get('query');
        if (!$query) {
            return $this->json([], 400);
        }
        $url = 'https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=' . urlencode($query);
        $response = $httpClient->request('GET', $url, [
            'headers' => [
                'User-Agent' => 'Kaderblick/1.0 (kontakt@deinverein.de)'
            ]
        ]);
        $data = $response->toArray();

        return $this->json($data);
    }
}
