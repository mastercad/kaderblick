<?php

namespace App\Controller;

use App\Entity\DashboardWidget;
use App\Entity\User;
use App\Repository\CalendarEventRepository;
use App\Repository\DashboardWidgetRepository;
use App\Repository\MessageRepository;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private CalendarEventRepository $calendarRepo, private MessageRepository $messagesRepo)
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

    #[Route('/widget/{id}/content', name: 'widget_content')]
    public function widgetContent(DashboardWidget $widget): Response
    {
        if ($widget->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return new Response($this->retrieveWidgetContent($widget));
    }

    #[Route('widget', name: 'widget_update', methods: ['POST'])]
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

    #[Route('widget', name: 'widget_create', methods: ['PUT'])]
    public function createWidget(Request $request, EntityManagerInterface $em, PushNotificationService $pushNotificationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['type'])) {
            return $this->json(['error' => 'Widget type is required'], 400);
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
        $widget->setPosition($data['position'] ?? 0);
        $widget->setWidth($data['width'] ?? 4);
        $widget->setConfig($data['config'] ?? []);
        $widget->setEnabled(true);

        $em->persist($widget);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'widget' => [
                'id' => $widget->getId(),
                'type' => $widget->getType(),
                'position' => $widget->getPosition(),
                'width' => $widget->getWidth(),
                'config' => $widget->getConfig()
            ]
        ]);
    }

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
            default => 'Widget type not implemented yet'
        };
    }
}
