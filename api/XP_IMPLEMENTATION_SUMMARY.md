# XP-System Implementierung - Übersicht

## Erstellte Dateien

### Events
1. **`src/Event/GoalScoredEvent.php`** - Event für geschossene Tore
2. **`src/Event/GoalAssistedEvent.php`** - Event für Tor-Assists
3. **`src/Event/ProfileCompletenessReachedEvent.php`** - Event für erreichte Profilvollständigkeits-Meilensteine

### Services
4. **`src/Service/ProfileCompletenessService.php`** - Service zur Berechnung der Profilvollständigkeit und Meilenstein-Tracking

### Commands
5. **`src/Command/ProcessHistoricalXpCommand.php`** - Command zum nachträglichen Verarbeiten historischer Events (Tore, Game Events, etc.)
6. **`src/Command/ProcessPendingXpCommand.php`** - Command zum Verarbeiten ausstehender XP-Events (für Cronjobs)

### Dokumentation
7. **`XP_SYSTEM.md`** - Vollständige Dokumentation des XP-Systems
8. **`XP_INTEGRATION_GUIDE.md`** - Detaillierter Integrations-Guide mit Beispielen
9. **`XP_IMPLEMENTATION_SUMMARY.md`** - Diese Datei

## Geänderte Dateien

### Services
1. **`src/Service/XPService.php`**
   - Vervollständigte Implementierung der Placeholder-Methoden
   - Hinzugefügt: EntityManagerInterface-Dependency
   - Hinzugefügt: Vollständige XP-Berechnung und Level-Up-Logik
   - Erweitert: XP-Werte für alle neuen Aktionen (Tore, Assists, Profilvollständigkeit)

2. **`src/Service/XPRegistrationService.php`**
   - Erweitert: XP-Werte für neue Aktionen (Tore, Assists, Profilvollständigkeit)

### Event Subscriber
3. **`src/EventSubscriber/XpEventSubscriber.php`**
   - Hinzugefügt: Import für neue Events
   - Erweitert: `getSubscribedEvents()` mit neuen Event-Handlern
   - Hinzugefügt: `onGoalScored()` - Handler für Tor-Events
   - Hinzugefügt: `onGoalAssisted()` - Handler für Assist-Events
   - Hinzugefügt: `onProfileCompletenessReached()` - Handler für Profilvollständigkeits-Events

## Neue Features

### 1. Tor-basierte XP-Vergabe
- **50 XP** für geschossene Tore
- **30 XP** für Tor-Assists
- Automatische Duplikatsprüfung
- Unterstützung für mehrere User pro Spieler

### 2. Profilvollständigkeits-System
- Berechnung der Profilvollständigkeit (0-100%)
- Meilensteine bei 25%, 50%, 75%, 100%
- Entsprechende XP-Belohnungen (25, 50, 75, 100 XP)
- Automatische Meilenstein-Erkennung bei Profilaktualisierungen

### 3. Nachträgliche XP-Anrechnung
- Command zur Verarbeitung historischer Daten
- Optionen für verschiedene Event-Typen
- Dry-Run-Modus zum Testen
- User-spezifische Verarbeitung möglich

### 4. Vervollständigter XP-Service
- Vollständige Level-Berechnung basierend auf exponentieller Formel
- Automatisches Level-Up beim Hinzufügen von XP
- Korrekte Persistence in der Datenbank

## XP-Werte Übersicht

| Aktion | XP | Status |
|--------|-----|--------|
| Kalenderereignis teilnehmen | 10 | ✅ Bestand |
| Profil aktualisieren | 5 | ✅ Bestand |
| Spielereignis erstellen | 15 | ✅ Bestand |
| **Tor schießen** | **50** | ✨ **NEU** |
| **Tor vorbereiten (Assist)** | **30** | ✨ **NEU** |
| **Profilvollständigkeit 25%** | **25** | ✨ **NEU** |
| **Profilvollständigkeit 50%** | **50** | ✨ **NEU** |
| **Profilvollständigkeit 75%** | **75** | ✨ **NEU** |
| **Profilvollständigkeit 100%** | **100** | ✨ **NEU** |
| Post erstellen | 10 | ⏳ Vorbereitet |
| Kommentar schreiben | 5 | ⏳ Vorbereitet |
| Like geben | 2 | ⏳ Vorbereitet |
| Teilen | 8 | ⏳ Vorbereitet |

## Verwendung

### Commands ausführen

```bash
# Ausstehende XP-Events verarbeiten (sollte als Cronjob laufen)
php bin/console app:xp:process-pending

# Historische Events nachträglich verarbeiten (einmalig nach Implementierung)
php bin/console app:xp:process-historical

# Nur Tore verarbeiten
php bin/console app:xp:process-historical --type=goals

# Dry-Run zum Testen
php bin/console app:xp:process-historical --dry-run
```

### Events in Code auslösen

```php
// Goal Event
use App\Event\GoalScoredEvent;
$event = new GoalScoredEvent($user, $goal);
$eventDispatcher->dispatch($event);

// Assist Event
use App\Event\GoalAssistedEvent;
$event = new GoalAssistedEvent($user, $goal);
$eventDispatcher->dispatch($event);

// Profilvollständigkeits-Meilenstein Event
use App\Event\ProfileCompletenessReachedEvent;
$event = new ProfileCompletenessReachedEvent($user, 75); // für 75% Meilenstein
$eventDispatcher->dispatch($event);
```

## Nächste Schritte

### Erforderlich
1. ✅ Events für Tore implementieren
2. ✅ Profilvollständigkeits-System implementieren
3. ✅ Command für historische Daten implementieren
4. ⏳ Events in bestehenden Controllern auslösen (Goal-Controller, Profile-Controller)
5. ⏳ Cronjob für `app:xp:process-pending` einrichten

### Optional
6. ⏳ Frontend-Integration (Level-Anzeige, XP-Historie)
7. ⏳ API-Endpoints für User-Level-Daten
8. ⏳ Tests schreiben
9. ⏳ Monitoring/Logging einrichten
10. ⏳ Post/Comment/Like-System mit XP integrieren (wenn vorhanden)

## Wichtige Hinweise

- **Duplikatsprüfung**: Das System verhindert automatisch doppelte XP-Vergabe für die gleiche Aktion
- **Asynchrone Verarbeitung**: XP-Events werden registriert und separat verarbeitet (Performance)
- **Sicherheit**: Command kann mehrfach ausgeführt werden (durch Duplikatsprüfung)
- **Flexibilität**: Neue XP-Aktionen können einfach hinzugefügt werden
- **Level-System**: Automatisches Level-Up beim Erreichen der erforderlichen XP

## Integration in bestehenden Code

Die Events müssen noch in den entsprechenden Controllern/Services ausgelöst werden:

1. **Goal-Controller**: `GoalScoredEvent` und `GoalAssistedEvent` beim Erstellen von Toren auslösen
2. **Profile-Controller**: `ProfileCompletenessReachedEvent` beim Aktualisieren von Profildaten auslösen
3. **Cronjob**: `app:xp:process-pending` alle 5-10 Minuten ausführen

Detaillierte Beispiele finden sich in `XP_INTEGRATION_GUIDE.md`.
