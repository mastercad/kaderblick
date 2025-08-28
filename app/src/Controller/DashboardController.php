<?php

namespace App\Controller;

use App\Entity\DashboardWidget;
use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Repository\CalendarEventRepository;
use App\Repository\DashboardWidgetRepository;
use App\Repository\MessageRepository;
use App\Repository\NewsRepository;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'app_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private CalendarEventRepository $calendarRepo, private MessageRepository $messagesRepo, private NewsRepository $newsRepo)
    {
    }

    #[Route('/', name: 'index')]
    public function index(DashboardWidgetRepository $widgetRepo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('login_required', 'Bitte melden Sie sich an.');
        }

        $widgets = $widgetRepo->findBy(
            ['user' => $user, 'enabled' => true],
            ['position' => 'ASC']
        );

        return $this->render('dashboard/index.html.twig', [
            'widgets' => $widgets
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/widget/{id}', name: 'widget', methods: ['GET'])]
    public function widget(
        DashboardWidget $widget,
        CalendarEventRepository $calendarRepo
    ): Response {
        if ($widget->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('widgets/base.html.twig', [
            'widget' => $widget,
            'widgetContent' => $this->retrieveWidgetContent($widget),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/widget/{id}/content', name: 'widget_content')]
    public function widgetContent(DashboardWidget $widget): Response
    {
        if ($widget->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return new Response($this->retrieveWidgetContent($widget));
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/app/dashboard/widgets/update', name: 'widgets_update', methods: ['PUT'])]
    public function updateWidgets(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['widgets']) || !is_array($data['widgets'])) {
            return $this->json(['error' => 'Invalid data format'], 400);
        }

        foreach ($data['widgets'] as $widgetData) {
            $widget = $em->getRepository(DashboardWidget::class)->find($widgetData['id']);

            if ($widget && $widget->getUser() === $this->getUser()) {
                $widget->setPosition($widgetData['position']);
                $widget->setWidth($widgetData['width']);
                $widget->setEnabled($widgetData['enabled'] ?? true);
                if (isset($widgetData['config'])) {
                    $widget->setConfig($widgetData['config']);
                }
            }
        }

        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/app/dashboard/widget/update', name: 'widget_update', methods: ['PUT'])]
    public function updateWidget(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $widget = $em->getRepository(DashboardWidget::class)->find($data['id']);

        if ($widget && $widget->getUser() === $this->getUser()) {
            $widget->setPosition($data['position']);
            $widget->setWidth($data['width']);
            $widget->setEnabled($data['enabled'] ?? true);
            if (isset($data['config'])) {
                $widget->setConfig($data['config']);
            }
        }

        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/widget', name: 'widget_create', methods: ['PUT'])]
    public function createWidget(Request $request, EntityManagerInterface $em, PushNotificationService $pushNotificationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null;
        $type = strtolower($type);

        if (empty($type)) {
            return $this->json(['error' => 'Widget type is required'], 400);
        }

        $reportId = $data['reportId'] ?? null;
        $position = $data['position'] ?? 0;
        $width = $data['width'] ?? 4;
        $report = null;

        if ('report' === $type) {
            if (empty($reportId)) {
                return $this->json(['error' => 'Report ID is required'], 400);
            }

            /** @var User $user */
            $user = $this->getUser();
            $report = $em->getRepository(ReportDefinition::class)->find($reportId);
            if (null === $report) {
                return $this->json(['error' => 'Report not found'], 404);
            }
        }

        /** @var User $user */
        $user = $this->getUser();

        $pushNotificationService->sendNotification(
            $user,
            'Widget created',
            'A new widget has been created on your dashboard.'
        );

        $widget = new DashboardWidget();
        $widget->setUser($user);
        $widget->setType($data['type']);
        $widget->setPosition($position);
        $widget->setWidth($width);
        $widget->setConfig($data['config'] ?? []);
        $widget->setEnabled(true);
        $widget->setReportDefinition($report);

        $em->persist($widget);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'widget' => [
                'id' => $widget->getId(),
                'type' => $widget->getType(),
                'position' => $widget->getPosition(),
                'width' => $widget->getWidth(),
                'config' => $widget->getConfig(),
                'reportId' => $widget->getReportDefinition() ? $widget->getReportDefinition()->getId() : null
            ]
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('widget/{id}', name: 'widget_delete', methods: ['DELETE'])]
    public function deleteWidget(DashboardWidget $widget, EntityManagerInterface $em): JsonResponse
    {
        if ($widget->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $em->remove($widget);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/app/dashboard/widgets/positions', name: 'widget_position', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function updateWidgetPosition(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data['positions'] as $positionData) {
            $widgetId = $positionData['id'] ?? null;
            $newPosition = $positionData['position'] ?? null;

            if (null === $widgetId || null === $newPosition) {
                return $this->json(['error' => 'Invalid data'], 400);
            }

            /** @var ?DashboardWidget $widget */
            $widget = $em->getRepository(DashboardWidget::class)->find($widgetId);
            if (null === $widget) {
                return $this->json(['error' => 'Widget not found'], 404);
            }

            $widget->setPosition($newPosition);
            $em->persist($widget);
        }
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    private function retrieveWidgetContent(DashboardWidget $widget): string
    {
        /** @var User $user */
        $user = $this->getUser();

        return match ($widget->getType()) {
            'upcoming_events' => $this->renderView('widgets/upcoming_events.html.twig', [
                'widget' => $widget,
                'events' => $this->calendarRepo->findUpcoming()
            ]),
            'calendar' => $this->renderView('widgets/calendar.html.twig', [
                'widget' => $widget
            ]),
            'messages' => $this->renderView('widgets/messages.html.twig', [
                'widget' => $widget,
                'messages' => $this->messagesRepo->findLatestForUser($user)
            ]),
            'news' => $this->renderView('widgets/news.html.twig', [
                'widget' => $widget,
                'news' => $this->newsRepo->findForUser($user)
            ]),
            'report' => $this->renderView('widgets/report.html.twig', [
                'widget' => $widget
            ]),
            default => 'Widget type not implemented yet'
        };
    }
}
