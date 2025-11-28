# XP-System Dokumentation

## Übersicht

Das XP-System belohnt Benutzer für verschiedene Aktivitäten auf der Plattform mit Erfahrungspunkten (XP). Durch das Sammeln von XP können Benutzer Level aufsteigen und ihre Fortschritte verfolgen.

## Architektur

### Entities

- **UserLevel**: Speichert XP-Total und aktuelles Level eines Benutzers
- **UserXpEvent**: Protokolliert einzelne XP-Ereignisse (noch nicht verarbeitet oder bereits verarbeitet)

### Services

#### XPService
Hauptservice für XP-Berechnungen:
- `retrieveXPForAction(string $action)`: Gibt XP-Wert für eine Aktion zurück
- `addXPToUser(User $user, int $xp)`: Fügt XP zu Benutzer hinzu und prüft Level-Up
- `calculateUserXP(User $user)`: Gibt aktuelle XP zurück
- `calculateUserLevel(User $user)`: Gibt aktuelles Level zurück
- `retrieveXpForLevel(int $level)`: Berechnet benötigte XP für Level
- `retrieveLevelForXP(int $xp)`: Berechnet Level basierend auf XP

#### XPRegistrationService
Registriert XP-Ereignisse:
- `registerXpEvent(User $user, string $actionType, int $actionId)`: Erstellt XP-Event (mit Duplikatsprüfung)

#### XPEventProcessor
Verarbeitet ausstehende XP-Events:
- `processPendingXpEvents()`: Verarbeitet alle unverarbeiteten XP-Events

#### ProfileCompletenessService
Berechnet Profilvollständigkeit:
- `calculateCompleteness(User $user)`: Gibt Prozentsatz der Profilvollständigkeit zurück
- `getReachedMilestones(int $old, int $new)`: Ermittelt erreichte Meilensteine

## XP-Werte

| Aktion | XP-Wert |
|--------|---------|
| Kalenderereignis teilnehmen | 10 |
| Profil aktualisieren | 5 |
| Spielereignis erstellen | 15 |
| **Tor schießen** | **50** |
| **Tor vorbereiten (Assist)** | **30** |
| **Profilvollständigkeit 25%** | **25** |
| **Profilvollständigkeit 50%** | **50** |
| **Profilvollständigkeit 75%** | **75** |
| **Profilvollständigkeit 100%** | **100** |
| Post erstellen | 10 |
| Kommentar schreiben | 5 |
| Like geben | 2 |
| Teilen | 8 |

## Events

### Bestehende Events
- `ProfileUpdatedEvent`: Wird ausgelöst, wenn ein Profil aktualisiert wird
- `CalendarEventParticipatedEvent`: Wird ausgelöst, wenn ein Benutzer an einem Kalenderereignis teilnimmt
- `GameEventCreatedEvent`: Wird ausgelöst, wenn ein Spielereignis erstellt wird

### Neue Events
- **`GoalScoredEvent`**: Wird ausgelöst, wenn ein Tor geschossen wird
- **`GoalAssistedEvent`**: Wird ausgelöst, wenn ein Assist gegeben wird
- **`ProfileCompletenessReachedEvent`**: Wird ausgelöst, wenn ein Profilvollständigkeits-Meilenstein erreicht wird

## Verwendung

### Events auslösen

```php
use App\Event\GoalScoredEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// In einem Controller oder Service
$event = new GoalScoredEvent($user, $goal);
$eventDispatcher->dispatch($event);
```

### Profilvollständigkeit prüfen

```php
use App\Service\ProfileCompletenessService;
use App\Event\ProfileCompletenessReachedEvent;

$oldCompleteness = $profileCompletenessService->calculateCompleteness($user);

// ... Profil aktualisieren ...

$newCompleteness = $profileCompletenessService->calculateCompleteness($user);
$reachedMilestones = $profileCompletenessService->getReachedMilestones($oldCompleteness, $newCompleteness);

foreach ($reachedMilestones as $milestone) {
    $event = new ProfileCompletenessReachedEvent($user, $milestone);
    $eventDispatcher->dispatch($event);
}
```

### Historische Events verarbeiten

Das System bietet ein Symfony-Command zum nachträglichen Anrechnen von XP für bereits bestehende Events:

```bash
# Alle historischen Events verarbeiten
php bin/console app:xp:process-historical

# Nur Tore verarbeiten
php bin/console app:xp:process-historical --type=goals

# Nur Spielereignisse verarbeiten
php bin/console app:xp:process-historical --type=game_events

# Nur für einen bestimmten Benutzer
php bin/console app:xp:process-historical --user-id=123

# Dry-Run (ohne tatsächliche Änderungen)
php bin/console app:xp:process-historical --dry-run
```

### Ausstehende XP-Events verarbeiten

XP-Events werden zunächst in der Datenbank registriert und dann asynchron verarbeitet:

```php
use App\Service\XPEventProcessor;

$xpEventProcessor->processPendingXpEvents();
```

Dies könnte als Cronjob oder über einen Message Queue Worker laufen.

## Level-System

Die Level werden basierend auf einer exponentiellen Formel berechnet:

```
XP für Level = Basis * Level^Exponent
```

Standardwerte:
- Basis: 50
- Exponent: 1.5

Beispiele:
- Level 1: 50 XP
- Level 2: 141 XP
- Level 3: 260 XP
- Level 5: 559 XP
- Level 10: 1581 XP

## Integration in bestehenden Code

### Beispiel: Tor erstellen

```php
use App\Event\GoalScoredEvent;
use App\Event\GoalAssistedEvent;

// Nach dem Erstellen eines Tors
$goal = new Goal();
// ... Goal konfigurieren ...
$entityManager->persist($goal);
$entityManager->flush();

// XP-Events auslösen
$scorer = $goal->getScorer();
$scorerUsers = $this->getUsersForPlayer($scorer);
foreach ($scorerUsers as $user) {
    $event = new GoalScoredEvent($user, $goal);
    $eventDispatcher->dispatch($event);
}

// Assist-XP
if ($goal->getAssistBy()) {
    $assistUsers = $this->getUsersForPlayer($goal->getAssistBy());
    foreach ($assistUsers as $user) {
        $event = new GoalAssistedEvent($user, $goal);
        $eventDispatcher->dispatch($event);
    }
}
```

### Beispiel: Profil aktualisieren

```php
use App\Service\ProfileCompletenessService;
use App\Event\ProfileCompletenessReachedEvent;

// Vor der Aktualisierung
$oldCompleteness = $profileCompletenessService->calculateCompleteness($user);

// Profil aktualisieren
$user->setHeight($height);
$user->setWeight($weight);
$entityManager->flush();

// Nach der Aktualisierung
$newCompleteness = $profileCompletenessService->calculateCompleteness($user);

// Profil-Update-Event
$event = new ProfileUpdatedEvent($user);
$eventDispatcher->dispatch($event);

// Meilenstein-Events
$reachedMilestones = $profileCompletenessService->getReachedMilestones($oldCompleteness, $newCompleteness);
foreach ($reachedMilestones as $milestone) {
    $event = new ProfileCompletenessReachedEvent($user, $milestone);
    $eventDispatcher->dispatch($event);
}
```

## Erweiterung

### Neue XP-Aktionen hinzufügen

1. **XP-Wert definieren** in `XPService::retrieveXPForAction()` und `XPRegistrationService::registerXpEvent()`

2. **Event erstellen** (optional, aber empfohlen)
   ```php
   namespace App\Event;
   
   use App\Entity\User;
   
   final class NewActionEvent
   {
       public function __construct(private User $user, private $entity)
       {
       }
       
       public function getUser(): User
       {
           return $this->user;
       }
   }
   ```

3. **Event-Handler registrieren** in `XpEventSubscriber`
   ```php
   public static function getSubscribedEvents(): array
   {
       return [
           // ...
           NewActionEvent::class => 'onNewAction',
       ];
   }
   
   public function onNewAction(NewActionEvent $event): void
   {
       $user = $event->getUser();
       $this->xpRegistrationService->registerXpEvent($user, 'new_action', $entity->getId());
   }
   ```

4. **Event auslösen** an der entsprechenden Stelle im Code

## Wichtige Hinweise

- **Duplikatsprüfung**: Das System verhindert automatisch, dass für die gleiche Aktion mehrfach XP vergeben wird
- **Asynchrone Verarbeitung**: XP-Events werden zunächst registriert und dann separat verarbeitet, um Performance zu gewährleisten
- **Level-Berechnung**: Erfolgt automatisch beim Hinzufügen von XP
- **Historische Daten**: Das Command `app:xp:process-historical` kann sicher mehrfach ausgeführt werden (Duplikatsprüfung)
