# Cronjob-Setup für XP-System

## Notwendiger Cronjob

Das XP-System verwendet eine robuste Event-Queue (`UserXpEvent`-Tabelle), die sicherstellt, dass keine XP verloren gehen, selbst wenn beim Anlegen eines Events ein Fehler auftritt.

### Ausstehende XP-Events verarbeiten

Der `XPEventProcessor` muss regelmäßig ausgeführt werden, um ausstehende XP-Events zu verarbeiten:

```bash
# Alle 5 Minuten ausstehende XP-Events verarbeiten
*/5 * * * * cd /pfad/zum/projekt/api && php bin/console app:xp:process-pending >> /var/log/xp-processing.log 2>&1
```

**Wichtig:** Dies ist der **einzige** Cronjob, der für den laufenden Betrieb des XP-Systems notwendig ist!

## Optionale Commands

### Historische Daten nachträglich verarbeiten

**Einmalig** nach Implementierung des XP-Systems ausführen, um XP für bereits existierende Daten zu vergeben:

```bash
# Alle historischen Events verarbeiten (Tore, Game Events, Profilvollständigkeit)
php bin/console app:xp:process-historical

# Nur bestimmte Event-Typen verarbeiten
php bin/console app:xp:process-historical --type=goals
php bin/console app:xp:process-historical --type=game_events
php bin/console app:xp:process-historical --type=profiles

# Dry-Run zum Testen (ohne tatsächliche XP-Vergabe)
php bin/console app:xp:process-historical --dry-run

# Für einen bestimmten Benutzer
php bin/console app:xp:process-historical --user-id=123
```

## Ablauf des XP-Systems

### 1. Event wird ausgelöst
```php
// z.B. beim Anlegen eines Tors
$event = new GoalScoredEvent($user, $goal);
$eventDispatcher->dispatch($event);
```

### 2. Event-Subscriber registriert XP-Event
```php
// XpEventSubscriber erstellt Eintrag in UserXpEvent-Tabelle
$xpRegistrationService->registerXpEvent($user, 'goal_scored', $goal->getId());
```

**Vorteile:**
- ✅ Schnelle Response (nur DB-Eintrag)
- ✅ Kein XP-Verlust bei Fehlern
- ✅ Duplikatsprüfung verhindert doppelte XP-Vergabe

### 3. Cronjob verarbeitet ausstehende Events
```bash
# Läuft alle 5 Minuten
php bin/console app:xp:process-pending
```

Der `XPEventProcessor`:
- Lädt alle unverarbeiteten `UserXpEvent`-Einträge (`isProcessed = false`)
- Vergibt XP an die Benutzer
- Markiert Events als verarbeitet (`isProcessed = true`)
- Updated Level automatisch bei Erreichen der erforderlichen XP

## Monitoring

### Ausstehende Events prüfen

```sql
-- Anzahl unverarbeiteter Events
SELECT COUNT(*) FROM user_xp_events WHERE is_processed = 0;

-- Unverarbeitete Events nach Typ
SELECT action_type, COUNT(*) as count 
FROM user_xp_events 
WHERE is_processed = 0 
GROUP BY action_type;

-- Älteste unverarbeitete Events
SELECT * FROM user_xp_events 
WHERE is_processed = 0 
ORDER BY created_at ASC 
LIMIT 10;
```

### Logs überprüfen

```bash
# Cronjob-Logs ansehen
tail -f /var/log/xp-processing.log

# Symfony-Logs ansehen
tail -f var/log/prod.log | grep XP
```

## Fehlerbehebung

### Problem: Events werden nicht verarbeitet

**Prüfen:**
1. Läuft der Cronjob? → `crontab -l` prüfen
2. Gibt es Fehler? → Logs ansehen
3. Haben Benutzer ein `UserLevel`? → Migration gelaufen?

**Manuell ausführen:**
```bash
php bin/console app:xp:process-pending -v
```

### Problem: Doppelte XP-Vergabe

Das System verhindert dies automatisch durch:
1. Duplikatsprüfung in `XPRegistrationService`
2. Unique-Constraint auf `UserXpEvent` (user_id, action_type, action_id)

### Problem: XP fehlen für alte Daten

```bash
# Historische Daten nachträglich verarbeiten
php bin/console app:xp:process-historical --dry-run  # Erst testen
php bin/console app:xp:process-historical             # Dann ausführen
```

## Skalierung

Bei hoher Last kann der Cronjob-Intervall angepasst werden:

```bash
# Jede Minute (bei sehr hoher Aktivität)
* * * * * cd /pfad/zum/projekt/api && php bin/console app:xp:process-pending

# Alle 10 Minuten (bei geringer Aktivität)
*/10 * * * * cd /pfad/zum/projekt/api && php bin/console app:xp:process-pending
```

**Alternative:** Symfony Messenger für echte asynchrone Verarbeitung nutzen.
