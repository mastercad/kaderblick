<?php

namespace App\Controller;

use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Service\ReportFieldAliasService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReportsController extends AbstractController
{
    #[Route('/reports', name: 'app_report_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);

        return $this->render('report/list.html.twig', [
            'templates' => $templates,
            'userReports' => $userReports
        ]);
    }

    #[Route('/reports/builder/{id}', name: 'app_report_builder', requirements: ['id' => '\\d+'], defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function builder(Request $request, EntityManagerInterface $em, ?int $id = null): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $isEdit = null !== $id;
        $report = $isEdit ? $repo->find($id) : new ReportDefinition();
        $fieldAliases = ReportFieldAliasService::fieldAliases();
        if ($isEdit && (!$report || ($report->getUser() && $report->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')))) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $report->setName($data['name'] ?? '');
            $report->setDescription($data['description'] ?? null);
            $config = [
                'diagramType' => $data['config']['diagramType'] ?? 'bar',
                'xField' => $data['config']['xField'] ?? 'player',
                'yField' => $data['config']['yField'] ?? 'goals',
            ];
            // Nur erlaubte Aliase speichern
            if (!isset($fieldAliases[$config['xField']])) {
                $config['xField'] = array_key_first($fieldAliases);
            }
            if (!isset($fieldAliases[$config['yField']])) {
                $config['yField'] = array_key_first($fieldAliases);
            }
            $report->setConfig($config);
            if ($this->isGranted('ROLE_ADMIN') && isset($data['isTemplate'])) {
                $report->setIsTemplate(true);
                $report->setUser(null);
            } else {
                $report->setIsTemplate(false);
                $report->setUser($user);
            }
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('app_report_list');
        }

        return $this->render('report/builder.html.twig', [
            'report' => $report,
            'form_action' => $isEdit ? $this->generateUrl('app_report_builder', ['id' => $id]) : $this->generateUrl('app_report_builder'),
            'fieldAliases' => $fieldAliases,
        ]);
    }

    #[Route('/reports/delete/{id}', name: 'app_report_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($id);
        if (!$report || ($report->getUser() && $report->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException('Report not found or access denied');
        }
        $em->remove($report);
        $em->flush();
        $this->addFlash('success', 'Report deleted.');

        return $this->redirectToRoute('app_report_list');
    }

    #[Route('/reports/add-widget/{id}', name: 'app_report_add_widget', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function addWidget(int $id, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($id);
        if (!$report) {
            throw $this->createNotFoundException('Report not found');
        }
        $widget = new \App\Entity\DashboardWidget();
        $widget->setUser($user);
        $widget->setType('report');
        $widget->setPosition(0); // Or calculate next position
        $widget->setWidth(6);
        $widget->setEnabled(true);
        $widget->setReportDefinition($report);
        $em->persist($widget);
        $em->flush();
        $this->addFlash('success', 'Report widget added to your dashboard.');

        return $this->redirectToRoute('app_dashboard_index');
    }
}
