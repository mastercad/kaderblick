# XP-System Integration Guide

## 1. Events in Controllern/Services auslösen

### Beispiel: GoalController - Tor erstellen

```php
<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Event\GoalAssistedEvent;
use App\Event\GoalScoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/api/goals', name: 'api_goal_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // Tor erstellen
        $goal = new Goal();
        // ... Goal mit Daten aus Request füllen ...
        
        $this->entityManager->persist($goal);
        $this->entityManager->flush();
        
        // XP-Events für Torschütze auslösen
        $scorer = $goal->getScorer();
        $scorerUsers = $this->getUsersForPlayer($scorer);
        
        foreach ($scorerUsers as $user) {
            $event = new GoalScoredEvent($user, $goal);
            $this->eventDispatcher->dispatch($event);
        }
        
        // XP-Events für Assist-Geber auslösen (falls vorhanden)
        if ($goal->getAssistBy()) {
            $assistUsers = $this->getUsersForPlayer($goal->getAssistBy());
            
            foreach ($assistUsers as $user) {
                $event = new GoalAssistedEvent($user, $goal);
                $this->eventDispatcher->dispatch($event);
            }
        }
        
        return new Response('Goal created', Response::HTTP_CREATED);
    }
    
    private function getUsersForPlayer($player): array
    {
        $users = [];
        foreach ($player->getUserRelations() as $userRelation) {
            if ($user = $userRelation->getUser()) {
                $users[] = $user;
            }
        }
        return $users;
    }
}
```

### Beispiel: UserProfileController - Profil aktualisieren

```php
<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\ProfileCompletenessReachedEvent;
use App\Event\ProfileUpdatedEvent;
use App\Service\ProfileCompletenessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserProfileController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private ProfileCompletenessService $profileCompletenessService
    ) {
    }

    #[Route('/api/profile', name: 'api_profile_update', methods: ['PUT'])]
    public function update(
        Request $request,
        #[CurrentUser] User $user
    ): Response {
        // Alte Vollständigkeit berechnen
        $oldCompleteness = $this->profileCompletenessService->calculateCompleteness($user);
        
        // Profil aktualisieren
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['height'])) {
            $user->setHeight($data['height']);
        }
        if (isset($data['weight'])) {
            $user->setWeight($data['weight']);
        }
        if (isset($data['shoeSize'])) {
            $user->setShoeSize($data['shoeSize']);
        }
        // ... weitere Felder ...
        
        $this->entityManager->flush();
        
        // Neue Vollständigkeit berechnen
        $newCompleteness = $this->profileCompletenessService->calculateCompleteness($user);
        
        // Profil-Update-Event auslösen
        $profileUpdatedEvent = new ProfileUpdatedEvent($user);
        $this->eventDispatcher->dispatch($profileUpdatedEvent);
        
        // Meilenstein-Events auslösen (falls Meilensteine erreicht wurden)
        $reachedMilestones = $this->profileCompletenessService->getReachedMilestones(
            $oldCompleteness,
            $newCompleteness
        );
        
        foreach ($reachedMilestones as $milestone) {
            $milestoneEvent = new ProfileCompletenessReachedEvent($user, $milestone);
            $this->eventDispatcher->dispatch($milestoneEvent);
        }
        
        return new Response('Profile updated', Response::HTTP_OK);
    }
}
```

## 2. Cronjob für Verarbeitung ausstehender XP-Events einrichten

### Crontab-Eintrag

```bash
# Alle 5 Minuten ausstehende XP-Events verarbeiten
*/5 * * * * cd /pfad/zum/projekt/api && php bin/console app:xp:process-pending >> /var/log/xp-processing.log 2>&1
```

### Symfony Messenger (Alternative)

Falls Symfony Messenger verwendet wird, kann die Verarbeitung auch asynchron über Message Queues erfolgen:

```php
// In XPRegistrationService nach registerXpEvent:
$this->messageBus->dispatch(new ProcessXpEventMessage($xpEvent->getId()));
```

## 3. Historische Daten nachträglich verarbeiten

Nach der Implementierung sollte das Command einmalig ausgeführt werden, um bestehende Tore und Events nachzuverarbeiten:

```bash
# Dry-Run zum Testen
php bin/console app:xp:process-historical --dry-run

# Alle historischen Events verarbeiten
php bin/console app:xp:process-historical

# Optional: Nur bestimmte Event-Typen
php bin/console app:xp:process-historical --type=goals
php bin/console app:xp:process-historical --type=game_events
```

## 4. Frontend-Integration (Optional)

### API-Endpoint für User-Level-Daten

```php
<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/user', name: 'api_user_')]
class UserLevelController extends AbstractController
{
    #[Route('/level', name: 'level', methods: ['GET'])]
    public function getLevel(#[CurrentUser] User $user): JsonResponse
    {
        $userLevel = $user->getUserLevel();
        
        return $this->json([
            'level' => $userLevel->getLevel(),
            'xpTotal' => $userLevel->getXpTotal(),
            'xpForNextLevel' => $this->calculateXpForNextLevel($userLevel),
            'progress' => $this->calculateLevelProgress($userLevel),
        ]);
    }
    
    #[Route('/xp-events', name: 'xp_events', methods: ['GET'])]
    public function getXpEvents(#[CurrentUser] User $user): JsonResponse
    {
        $xpEvents = $user->getUserXpEvents()->toArray();
        
        // Nur verarbeitete Events der letzten 30 Tage
        $recentEvents = array_filter($xpEvents, function($event) {
            return $event->isProcessed() && 
                   $event->getCreatedAt() > new \DateTimeImmutable('-30 days');
        });
        
        return $this->json($recentEvents, 200, [], [
            'groups' => ['xp_event:read']
        ]);
    }
    
    private function calculateXpForNextLevel($userLevel): int
    {
        $currentLevel = $userLevel->getLevel();
        $nextLevel = $currentLevel + 1;
        
        // Formel: 50 * Level^1.5
        return (int) round(50 * pow($nextLevel, 1.5));
    }
    
    private function calculateLevelProgress($userLevel): float
    {
        $currentXp = $userLevel->getXpTotal();
        $currentLevel = $userLevel->getLevel();
        
        $xpForCurrentLevel = (int) round(50 * pow($currentLevel, 1.5));
        $xpForNextLevel = $this->calculateXpForNextLevel($userLevel);
        
        $xpInCurrentLevel = $currentXp - $xpForCurrentLevel;
        $xpNeededForLevel = $xpForNextLevel - $xpForCurrentLevel;
        
        return ($xpInCurrentLevel / $xpNeededForLevel) * 100;
    }
}
```

## 5. Tests schreiben

### Unit-Test für XPService

```php
<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserLevel;
use App\Service\XPService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class XPServiceTest extends TestCase
{
    private XPService $xpService;
    private EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->xpService = new XPService($this->entityManager);
    }
    
    public function testRetrieveXPForAction(): void
    {
        $this->assertEquals(50, $this->xpService->retrieveXPForAction('goal_scored'));
        $this->assertEquals(30, $this->xpService->retrieveXPForAction('goal_assisted'));
        $this->assertEquals(0, $this->xpService->retrieveXPForAction('unknown_action'));
    }
    
    public function testRetrieveLevelForXP(): void
    {
        $this->assertEquals(1, $this->xpService->retrieveLevelForXP(50));
        $this->assertEquals(2, $this->xpService->retrieveLevelForXP(141));
        $this->assertEquals(5, $this->xpService->retrieveLevelForXP(559));
    }
    
    public function testRetrieveXpForLevel(): void
    {
        $this->assertEquals(50, $this->xpService->retrieveXpForLevel(1));
        $this->assertEquals(141, $this->xpService->retrieveXpForLevel(2));
        $this->assertEquals(260, $this->xpService->retrieveXpForLevel(3));
    }
}
```

## 6. Monitoring und Logging

### Event-Listener für Logging (Optional)

```php
<?php

namespace App\EventSubscriber;

use App\Event\GoalScoredEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class XpLoggingSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            GoalScoredEvent::class => 'onGoalScored',
            // weitere Events...
        ];
    }
    
    public function onGoalScored(GoalScoredEvent $event): void
    {
        $this->logger->info('XP Event: Goal Scored', [
            'user_id' => $event->getUser()->getId(),
            'goal_id' => $event->getGoal()->getId(),
            'xp_awarded' => 50,
        ]);
    }
}
```

## Checkliste für die Integration

- [ ] Events in allen relevanten Controllern/Services auslösen
- [ ] Cronjob für `app:xp:process-pending` einrichten
- [ ] Command `app:xp:process-historical` einmalig ausführen
- [ ] Frontend-Integration (Level-Anzeige, XP-Historie)
- [ ] Tests schreiben
- [ ] Monitoring/Logging einrichten
- [ ] Dokumentation aktualisieren
