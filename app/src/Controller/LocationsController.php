<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\SurfaceTypeRepository;
use App\Security\Voter\LocationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationsController extends AbstractController
{
    #[Route('/locations', name: 'locations_index')]
    public function index(LocationRepository $locationRepository): Response
    {
        $locations = $locationRepository->findBy([], ['name' => 'ASC']);

        return $this->render('locations/index.html.twig', [
            'locations' => $locations,
            'permissions' => [
                'CREATE' => LocationVoter::CREATE,
                'EDIT' => LocationVoter::EDIT,
                'VIEW' => LocationVoter::VIEW,
                'DELETE' => LocationVoter::DELETE
            ]
        ]);
    }

    #[Route('/locations/edit/{id}', name: 'locations_edit', methods: ['GET'])]
    public function edit(Location $location, SurfaceTypeRepository $surfaceTypeRepository): Response
    {
        $surfaceTypes = $surfaceTypeRepository->findAll();

        return $this->render('locations/edit_modal.html.twig', [
            'location' => $location,
            'surfaceTypes' => $surfaceTypes,
        ]);
    }

    #[Route('/locations/update/{id}', name: 'locations_update', methods: ['POST'])]
    public function update(Request $request, Location $location, EntityManagerInterface $em): Response
    {
        $location->setName($request->request->get('name'));
        $location->setAddress($request->request->get('address'));
        $location->setCity($request->request->get('city'));
        $capacity = $request->request->get('capacity');
        $location->setCapacity(null === $capacity || '' === $capacity ? null : (int) $capacity);
        $location->setFacilities($request->request->get('facilities'));
        $location->setLatitude($request->request->get('latitude'));
        $location->setLongitude($request->request->get('longitude'));
        $surfaceTypeId = $request->request->get('surfaceType');
        if ($surfaceTypeId) {
            $surfaceType = $em->getRepository(\App\Entity\SurfaceType::class)->find($surfaceTypeId);
            $location->setSurfaceType($surfaceType);
        } else {
            $location->setSurfaceType(null);
        }
        $location->setHasFloodlight($request->request->has('hasFloodlight'));

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/locations/delete/{id}', name: 'locations_delete', methods: ['POST'])]
    public function delete(Location $location, EntityManagerInterface $em): Response
    {
        $em->remove($location);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
