<?php

namespace Tests\Feature;

use App\Entity\DashboardWidget;
use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Repository\DashboardWidgetRepository;
use Symfony\Component\HttpFoundation\Response;

class DashboardEndToEndTest extends ApiWebTestCase
{
    public function testUserCanUpdateOwnWidget(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $widgetRepo = $container->get(DashboardWidgetRepository::class);
        $this->authenticateUser($client, 'user1@example.com');

        // Widget für User anlegen
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $widget = new DashboardWidget();
        $widget->setUser($user)
            ->setType('test')
            ->setPosition(1)
            ->setWidth(4)
            ->setConfig(['foo' => 'bar'])
            ->setEnabled(true)
            ->setDefault(false);
        $em->persist($widget);
        $em->flush();

        $payload = [
            'id' => $widget->getId(),
            'position' => 2,
            'width' => 6,
            'enabled' => false,
            'default' => false,
            'config' => ['foo' => 'baz']
        ];

        $client->jsonRequest('PUT', '/app/dashboard/widget/update', $payload);
        $this->assertResponseIsSuccessful();
        $em->refresh($widget);
        $this->assertEquals(2, $widget->getPosition());
        $this->assertEquals(6, $widget->getWidth());
        $this->assertFalse($widget->isEnabled());
        $this->assertEquals(['foo' => 'baz'], $widget->getConfig());
    }

    public function testDefaultWidgetCopyOnUserEdit(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $widgetRepo = $container->get(DashboardWidgetRepository::class);
        $this->authenticateUser($client, 'user2@example.com');

        // Default Widget für einen existierenden User (z.B. user1@example.com)
        $defaultOwner = $em->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $defaultWidget = new DashboardWidget();
        $defaultWidget->setUser($defaultOwner)
            ->setType('test')
            ->setPosition(1)
            ->setWidth(4)
            ->setConfig(['foo' => 'bar'])
            ->setEnabled(true)
            ->setDefault(true);
        $em->persist($defaultWidget);
        $em->flush();

        $payload = [
            'id' => $defaultWidget->getId(),
            'position' => 3,
            'width' => 8,
            'enabled' => true,
            'default' => false,
            'config' => ['foo' => 'user2']
        ];

        $client->jsonRequest('PUT', '/app/dashboard/widget/update', $payload);
        $this->assertResponseIsSuccessful();

        // Default Widget bleibt unverändert
        $em->refresh($defaultWidget);
        $this->assertEquals(1, $defaultWidget->getPosition());
        $this->assertEquals(['foo' => 'bar'], $defaultWidget->getConfig());
        // Es existiert ein neues Widget für user2
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user2@example.com']);
        $userWidgets = $widgetRepo->findBy(['user' => $user, 'type' => 'test']);
        $this->assertNotEmpty($userWidgets);
        $userWidget = $userWidgets[0];
        $this->assertEquals(3, $userWidget->getPosition());
        $this->assertEquals(['foo' => 'user2'], $userWidget->getConfig());
        $this->assertFalse($userWidget->isDefault());
    }

    public function testUserCannotUpdateOthersWidget(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $widgetRepo = $container->get(DashboardWidgetRepository::class);
        $this->authenticateUser($client, 'user3@example.com');

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user4@example.com']);
        $widget = new DashboardWidget();
        $widget->setUser($user)
            ->setType('test')
            ->setPosition(1)
            ->setWidth(4)
            ->setConfig(['foo' => 'bar'])
            ->setEnabled(true)
            ->setDefault(false);
        $em->persist($widget);
        $em->flush();

        $payload = [
            'id' => $widget->getId(),
            'position' => 5,
            'width' => 12,
            'enabled' => true,
            'default' => false,
            'config' => ['foo' => 'hacker']
        ];

        $client->jsonRequest('PUT', '/app/dashboard/widget/update', $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); // Controller gibt trotzdem success zurück
        $em->refresh($widget);
        // Widget bleibt unverändert
        $this->assertEquals(1, $widget->getPosition());
        $this->assertEquals(['foo' => 'bar'], $widget->getConfig());
    }

    public function testUpdateWidgetsTriggersTypeInitializationBug(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $widgetRepo = $container->get(DashboardWidgetRepository::class);
        $this->authenticateUser($client, 'user2@example.com');

        // Default Widget für einen existierenden User
        $defaultOwner = $em->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $defaultWidget = new DashboardWidget();
        $defaultWidget->setUser($defaultOwner)
            ->setType('test')
            ->setPosition(1)
            ->setWidth(4)
            ->setConfig(['foo' => 'bar'])
            ->setEnabled(true)
            ->setDefault(true);
        $em->persist($defaultWidget);
        $em->flush();

        // Simuliere ein Massenupdate wie im Fehlerbericht, ohne type/config
        $payload = [
            'widgets' => [
                [
                    'id' => $defaultWidget->getId(),
                    'position' => 0,
                    'width' => 4,
                    'default' => 0
                    // kein type, kein config
                ]
            ]
        ];

        $client->jsonRequest('PUT', '/app/dashboard/widgets/update', $payload);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('success', $client->getResponse()->getContent());
    }

    public function testCopyDefaultReportWidgetLosesReportDefinition(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $this->authenticateUser($client, 'user2@example.com');

        // ReportDefinition anlegen
        $report = new ReportDefinition();
        $report->setName('Mein Report');
        $em->persist($report);

        // Default-Widget mit ReportDefinition für user1
        $defaultOwner = $em->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $defaultWidget = new DashboardWidget();
        $defaultWidget->setUser($defaultOwner)
            ->setType('report')
            ->setPosition(1)
            ->setWidth(4)
            ->setConfig([])
            ->setEnabled(true)
            ->setDefault(true)
            ->setReportDefinition($report);
        $em->persist($defaultWidget);
        $em->flush();

        // Massenupdate, das das Kopieren triggert
        $payload = [
            'widgets' => [
                [
                    'id' => $defaultWidget->getId(),
                    'position' => 0,
                    'width' => 4,
                    'default' => 0
                ]
            ]
        ];
        $client->jsonRequest('PUT', '/app/dashboard/widgets/update', $payload);
        $this->assertResponseIsSuccessful();

        // Das neue Widget für user2 suchen (höchste ID = zuletzt kopiert)
        $user2 = $em->getRepository(User::class)->findOneBy(['email' => 'user2@example.com']);
        $widgets = $em->getRepository(DashboardWidget::class)->findBy(['user' => $user2, 'type' => 'report']);
        $this->assertNotEmpty($widgets, 'Es wurde kein Widget für user2 angelegt');
        /** @var ?DashboardWidget $copied */
        $copied = array_reduce($widgets, function ($carry, $item) {
            return (null === $carry || $item->getId() > $carry->getId()) ? $item : $carry;
        }, null);
        $this->assertNotNull($copied, 'Kein kopiertes Widget gefunden!');
        $this->assertNotNull($copied->getReportDefinition(), 'ReportDefinition wurde nicht kopiert!');
        $this->assertNotEquals($report->getId(), $copied->getReportDefinition()->getId(), 'ReportDefinition wurde nicht als Kopie angelegt!');
        $this->assertEquals($report->getName(), $copied->getReportDefinition()->getName(), 'ReportDefinition-Name stimmt nicht überein!');
    }
}
