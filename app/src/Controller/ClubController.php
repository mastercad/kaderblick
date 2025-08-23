<?php

namespace App\Controller;

use App\Entity\Club;
use App\Repository\ClubRepository;
use App\Service\FussballDeCrawlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClubController extends AbstractController
{
    #[Route('/clubs', name: 'club_list', methods: ['GET'])]
    public function list(ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findAll();

        return $this->render('club/list.html.twig', [
            'clubs' => $clubs,
        ]);
    }

    #[Route('/clubs/{id}/detail', name: 'club_detail_modal', methods: ['GET'])]
    public function detailModal(Club $club): Response
    {
        return $this->render('club/detail.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/clubs/{id}/edit', name: 'club_edit_modal', methods: ['GET'])]
    public function editModal(Club $club): Response
    {
        return $this->render('club/edit_modal.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/clubs/{id}/update', name: 'club_update', methods: ['POST'])]
    public function update(Request $request, Club $club, EntityManagerInterface $em): JsonResponse
    {
        // Validierung & Filterung
        $logoUrl = trim((string) $request->request->get('logoUrl', $club->getLogoUrl()));
        $fussballDeId = trim((string) $request->request->get('fussballDeId', $club->getFussballDeId()));
        $fussballDeUrl = trim((string) $request->request->get('fussballDeUrl', $club->getFussballDeUrl()));
        $name = trim((string) $request->request->get('name', $club->getName()));
        $shortName = trim((string) $request->request->get('shortName', $club->getShortName()));
        $stadiumName = trim((string) $request->request->get('stadiumName', $club->getStadiumName()));
        $website = trim((string) $request->request->get('website', $club->getWebsite()));
        $email = trim((string) $request->request->get('email', $club->getEmail()));
        $phone = trim((string) $request->request->get('phone', $club->getPhone()));
        $clubColors = trim((string) $request->request->get('clubColors', $club->getClubColors()));
        $contactPerson = trim((string) $request->request->get('contactPerson', $club->getContactPerson()));
        $foundingYearRaw = $request->request->get('foundingYear', $club->getFoundingYear());
        $foundingYear = ('' !== $foundingYearRaw && is_numeric($foundingYearRaw)) ? (int) $foundingYearRaw : null;
        $active = $request->request->get('active') ? true : false;

        // Setzen ins Entity
        $club->setLogoUrl('' !== $logoUrl ? $logoUrl : null);
        $club->setFussballDeId('' !== $fussballDeId ? $fussballDeId : null);
        $club->setFussballDeUrl('' !== $fussballDeUrl ? $fussballDeUrl : null);
        $club->setName($name);
        $club->setShortName('' !== $shortName ? $shortName : null);
        $club->setStadiumName('' !== $stadiumName ? $stadiumName : null);
        $club->setWebsite('' !== $website ? $website : null);
        $club->setEmail('' !== $email ? $email : null);
        $club->setPhone('' !== $phone ? $phone : null);
        $club->setActive($active);
        $club->setClubColors('' !== $clubColors ? $clubColors : null);
        $club->setContactPerson('' !== $contactPerson ? $contactPerson : null);
        $club->setFoundingYear($foundingYear);

        $em->persist($club);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/clubs/{id}/delete', name: 'club_delete', methods: ['POST'])]
    public function delete(Club $club, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($club);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/clubs/{id}/detail-partial', name: 'club_detail_partial', methods: ['GET'])]
    public function detailPartial(Club $club): Response
    {
        return $this->render('club/_detail_content.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/clubs/fussballde-lookup', name: 'club_fussballde_lookup', methods: ['GET'])]
    public function fussballdeLookup(Request $request, FussballDeCrawlerService $crawler): JsonResponse
    {
        $name = $request->query->get('name');
        if (!$name) {
            return new JsonResponse(['error' => 'Kein Name angegeben'], 400);
        }

        $teams = $crawler->searchTeams($name);
        if (!$teams || !isset($teams[0]['vereinId'])) {
            // Debug-Ausgabe: empfangener Name
            $debug = [
                'received_name' => $name,
            ];
            $debug['searchTeams_result'] = $teams;

            // Debug-Ausgabe bei Fehler
            return new JsonResponse([
                'error' => 'Kein Verein gefunden',
                'debug' => $debug
            ], 404);
        }

        $details = $crawler->retrieveVereinDetails($teams[0]['vereinId']);

        return new JsonResponse([
            'name' => $details['name'] ?? null,
            'website' => $details['website'] ?? null,
            'vereinId' => $details['vereinId'] ?? null,
            'url' => $details['url'] ?? null,
            'farben' => $details['farben'] ?? null,
            'gruendung' => $details['gruendung'] ?? null,
            'adresse' => $details['adresse'] ?? null,
            'ansprechpartner' => $details['ansprechpartner'] ?? null,
            'debug' => $details
        ]);
    }
}
