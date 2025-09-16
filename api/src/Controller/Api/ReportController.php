<?php

namespace App\Controller\Api;

use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Service\ReportDataService;
use App\Service\ReportFieldAliasService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/report')]
#[IsGranted('ROLE_USER')]
class ReportController extends AbstractController
{
    #[Route('/builder-data', name: 'api_report_builder_data', methods: ['GET'])]
    public function builderData(EntityManagerInterface $em): JsonResponse
    {
        $fieldAliases = ReportFieldAliasService::fieldAliases();

        // Teams, Spieler, Ereignistypen für Filter
        $teamRepo = $em->getRepository(\App\Entity\Team::class);
        $playerRepo = $em->getRepository(\App\Entity\Player::class);
        $eventTypeRepo = $em->getRepository(\App\Entity\GameEventType::class);
        $gameEventRepo = $em->getRepository(\App\Entity\GameEvent::class);

        $teams = $teamRepo->findAll();
        $players = $playerRepo->findAll();
        $eventTypes = $eventTypeRepo->findAll();

        // Convert to simple arrays to avoid circular references
        $teamsData = array_map(fn ($team) => [
            'id' => $team->getId(),
            'name' => $team->getName()
        ], $teams);

        $playersData = array_map(fn ($player) => [
            'id' => $player->getId(),
            'fullName' => $player->getFullName(),
            'firstName' => $player->getFirstName(),
            'lastName' => $player->getLastName()
        ], $players);

        $eventTypesData = array_map(fn ($eventType) => [
            'id' => $eventType->getId(),
            'name' => $eventType->getName()
        ], $eventTypes);

        $dateRows = $gameEventRepo->createQueryBuilder('e')
            ->select('e.timestamp')
            ->orderBy('e.timestamp', 'ASC')
            ->getQuery()->getArrayResult();
        $availableDates = array_unique(array_map(
            fn ($row) => $row['timestamp']->format('Y-m-d'),
            $dateRows
        ));
        $availableDates = array_values($availableDates);
        $minDate = $availableDates[0] ?? null;
        $maxDate = $availableDates[count($availableDates) - 1] ?? null;

        $fields = [];
        foreach ($fieldAliases as $key => $data) {
            $fields[] = [
                'key' => $key,
                'label' => $data['label']
            ];
        }

        return $this->json([
            'fields' => $fields,
            'teams' => $teamsData,
            'players' => $playersData,
            'eventTypes' => $eventTypesData,
            'availableDates' => $availableDates,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }

    #[Route('/preview', name: 'api_report_preview', methods: ['POST'])]
    public function preview(Request $request, ReportDataService $reportDataService): JsonResponse
    {
        $content = $request->getContent();
        $config = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($config)) {
            return $this->json(['error' => 'Invalid JSON configuration: ' . json_last_error_msg()], 400);
        }

        // Clean filters - remove empty values
        $configData = $config['config'] ?? $config;
        $rawFilters = $configData['filters'] ?? [];

        // Convert stdClass to array if needed
        if (is_object($rawFilters)) {
            $rawFilters = (array) $rawFilters;
        }

        $filters = [];
        foreach ($rawFilters as $k => $v) {
            if (null !== $v && '' !== $v) {
                $filters[$k] = $v;
            }
        }
        $configData['filters'] = $filters;

        $reportData = $reportDataService->generateReportData($configData);

        return $this->json([
            'labels' => $reportData['labels'],
            'datasets' => $reportData['datasets'],
            'diagramType' => $configData['diagramType'] ?? 'bar'
        ]);
    }

    #[Route('/available', name: 'api_report_available', methods: ['GET'])]
    public function available(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);
        $result = [];
        foreach ($templates as $report) {
            $result[] = [
                'id' => $report->getId(),
                'name' => $report->getName(),
                'isTemplate' => true
            ];
        }
        foreach ($userReports as $report) {
            $result[] = [
                'id' => $report->getId(),
                'name' => $report->getName(),
                'isTemplate' => false
            ];
        }

        return $this->json($result);
    }

    #[Route('/widget/{reportId}/data', name: 'api_report_data', methods: ['GET'])]
    public function retrieveData(int $reportId, EntityManagerInterface $em, Request $request, ReportDataService $reportDataService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($reportId);
        if (!$report || ($report->getUser() !== $user && false == $report->isTemplate())) {
            return $this->json(['error' => 'Report not found or access denied', 'owner' => $report->getUser()->getId(), 'user' => $user->getId()], 404);
        }
        $config = $report->getConfig();
        $rawFilters = $config['filters'] ?? [];

        $filters = [];
        foreach ($rawFilters as $k => $v) {
            if (null !== $v && '' !== $v) {
                $filters[$k] = $v;
            }
        }
        $config['filters'] = $filters;

        $reportData = $reportDataService->generateReportData($config);

        return $this->json([
            'config' => $config,
            'labels' => $reportData['labels'],
            'datasets' => $reportData['datasets'],
            'diagramType' => $config['diagramType'] ?? 'bar'
        ]);
    }

    #[Route('/definitions', name: 'api_report_definitions', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);

        // Convert to simple arrays to avoid circular references
        $templatesData = array_map(fn ($report) => [
            'id' => $report->getId(),
            'name' => $report->getName(),
            'description' => $report->getDescription(),
            'config' => $report->getConfig(),
            'isTemplate' => $report->isTemplate()
        ], $templates);

        $userReportsData = array_map(fn ($report) => [
            'id' => $report->getId(),
            'name' => $report->getName(),
            'description' => $report->getDescription(),
            'config' => $report->getConfig(),
            'isTemplate' => $report->isTemplate()
        ], $userReports);

        return $this->json([
            'templates' => $templatesData,
            'userReports' => $userReportsData
        ]);
    }

    #[Route('/definition', name: 'api_report_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'], $data['config'])) {
            return $this->json(['error' => 'Missing name or config'], 400);
        }
        /** @var User $user */
        $user = $this->getUser();
        $report = new ReportDefinition();
        $report->setName($data['name']);
        $report->setDescription($data['description'] ?? null);
        $report->setConfig($data['config']);
        $report->setUser($user);
        $report->setIsTemplate(false);
        $em->persist($report);
        $em->flush();

        return $this->json(['status' => 'success', 'id' => $report->getId()]);
    }

    #[Route('/definition/{id}', name: 'api_report_update', methods: ['PUT'])]
    public function update(ReportDefinition $report, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($report->getUser()?->getId() !== $user->getId() && true !== $report->isTemplate()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) {
            $report->setName($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $report->setDescription($data['description']);
        }
        if (isset($data['config'])) {
            $report->setConfig($data['config']);
        }

        if (true === $report->isTemplate()) {
            if (
                !in_array('ROLE_ADMIN', $user->getRoles())
                && !in_array('ROLE_SUPERADMIN', $user->getRoles())
            ) {
                $newReport = new ReportDefinition();
                $newReport->setName($data['name'] ?? $report->getName());
                $newReport->setDescription($data['description'] ?? $report->getDescription());
                $newReport->setConfig($data['config'] ?? $report->getConfig());
                $newReport->setUser($user);
                $newReport->setIsTemplate(false);
                $newReport->setCreatedAt(new DateTimeImmutable());
                $newReport->setUpdatedAt(new DateTimeImmutable());

                $em->persist($newReport);
                $em->detach($report);
                $em->flush();

                return $this->json(['status' => 'success', 'id' => $newReport->getId()]);
            } else {
                $report->setUpdatedAt(new DateTimeImmutable());
                $em->flush();
            }
        } else {
            $report->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
        }

        return $this->json(['status' => 'success']);
    }

    #[Route('/definition/{id}', name: 'api_report_delete', methods: ['DELETE'])]
    public function delete(ReportDefinition $report, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($report->getUser()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        $em->remove($report);
        $em->flush();

        return $this->json(['status' => 'success']);
    }
}
