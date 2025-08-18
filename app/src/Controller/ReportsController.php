<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{
    #[Route('/api/reports', name: 'api_report_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('report/index.html.twig');
    }

    #[Route('/api/reports', name: 'api_reports_show', methods: ['GET'])]
    public function reports(Request $request): JsonResponse
    {
        $diagramType = $request->query->get('diagramType', 'bar');
        $xField = $request->query->get('xField', 'spieler');
        $yField = $request->query->get('yField', 'tore');

        $data = [
            ['spieler' => 'Max', 'tore' => 5, 'minuten' => 45],
            ['spieler' => 'Tom', 'tore' => 2, 'minuten' => 90],
            ['spieler' => 'Jan', 'tore' => 1, 'minuten' => 75],
        ];

        $labels = array_column($data, $xField);
        $values = array_column($data, $yField);

        return $this->json([
            'labels' => $labels,
            'values' => $values,
            'diagramType' => $diagramType,
        ]);
    }
}
