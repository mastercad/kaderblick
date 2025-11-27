# Title System Documentation

## Overview
Das Titel-System vergibt Auszeichnungen an Benutzer basierend auf ihren Leistungen (Tore, Vorlagen, etc.). Titel haben unterschiedliche Prioritäten und werden hierarchisch angezeigt.

## Titel-Hierarchie

### Scope (Geltungsbereich)
1. **Platform** (höchste Priorität) - Platformweit über alle Teams
2. **Team** - Innerhalb eines Teams

### Rank (Platzierung)
1. **Gold** - 1. Platz (höchste Priorität)
2. **Silver** - 2. Platz
3. **Bronze** - 3. Platz

### Category (Kategorie)
- `top_scorer` - Torschützenkönig
- `top_assist` - Vorlagenkönig (erweiterbar)

## Priorisierung

Die Anzeige-Priorität wird wie folgt berechnet:
```
Priority = ScopePriority + RankPriority

ScopePriority:
- platform: 0
- team: 100

RankPriority:
- gold: 0
- silver: 10
- bronze: 20
```

**Beispiele:**
- Platform Gold: 0 + 0 = **0** (höchste Priorität)
- Platform Silver: 0 + 10 = **10**
- Platform Bronze: 0 + 20 = **20**
- Team Gold: 100 + 0 = **100**
- Team Silver: 100 + 10 = **110**
- Team Bronze: 100 + 20 = **120**

**Anzeige-Regel:** Der Titel mit der **niedrigsten** Priority-Zahl wird angezeigt.

## Entity: UserTitle

```php
class UserTitle
{
    private User $user;
    private string $titleCategory;  // 'top_scorer', 'top_assist'
    private string $titleScope;     // 'platform', 'team'
    private string $titleRank;      // 'gold', 'silver', 'bronze'
    private ?Team $team;            // null für platform-Titel
    private int $value;             // Anzahl Tore/Vorlagen
    private bool $isActive;         // true = aktuell gültig
    private DateTimeImmutable $awardedAt;
    private ?DateTimeImmutable $revokedAt;
    private ?string $season;        // z.B. '2024/2025'
}
```

## Services

### TitleCalculationService
Berechnet und vergibt Titel basierend auf aktuellen Statistiken.

**Methoden:**
- `calculatePlatformTopScorers(?string $season): array` - Top 3 Torschützen platformweit
- `calculateTeamTopScorers(Team $team, ?string $season): array` - Top 3 pro Team
- `calculateAllTeamTopScorers(?string $season): array` - Top 3 für alle Teams
- `retrieveCurrentSeason(): string` - Aktuelle Saison (z.B. '2024/2025')

### UserTitleService
Frontend-Integration für Titel-Anzeige.

**Methoden:**
- `loadDisplayTitle(User $user): ?UserTitle` - Höchster Titel zur Anzeige
- `loadAllTitles(User $user): array` - Alle aktiven Titel
- `retrieveTitleDataForUser(User $user): array` - Formatierte Daten fürs Frontend
- `hasTitle(User $user, string $category, string $scope, string $rank): bool` - Check spezifischer Titel

## Command

### app:titles:calculate
Berechnet und vergibt Titel.

```bash
# Alle Titel berechnen (Platform + Teams)
php bin/console app:titles:calculate

# Nur Platform-Titel
php bin/console app:titles:calculate --scope=platform

# Nur Team-Titel
php bin/console app:titles:calculate --scope=team

# Für spezifische Saison
php bin/console app:titles:calculate --season=2024/2025
```

**Empfohlener Cronjob:** Täglich oder wöchentlich

```cron
# Täglich um 2 Uhr
0 2 * * * cd /var/www/html && /var/www/html/bin/cron_wrapper.sh php bin/console app:titles:calculate 2>&1
```

## Frontend Integration

### API Response Format

```json
{
  "hasTitle": true,
  "displayTitle": {
    "id": 123,
    "category": "top_scorer",
    "scope": "platform",
    "rank": "gold",
    "value": 28,
    "teamId": null,
    "teamName": null,
    "season": "2024/2025",
    "awardedAt": "2024-11-24 18:30:00",
    "displayName": "Platform Torschützenkönig - Gold",
    "priority": 0
  },
  "avatarFrame": "platform_gold",
  "allTitles": [
    {
      "id": 123,
      "category": "top_scorer",
      "scope": "platform",
      "rank": "gold",
      "value": 28,
      "displayName": "Platform Torschützenkönig - Gold",
      "priority": 0
    },
    {
      "id": 124,
      "category": "top_scorer",
      "scope": "team",
      "rank": "gold",
      "value": 28,
      "teamId": 5,
      "teamName": "FC Example",
      "displayName": "Team Torschützenkönig - Gold",
      "priority": 100
    }
  ]
}
```

### Avatar Frame CSS

Die `avatarFrame`-Property gibt einen Identifier zurück, der für CSS-Klassen oder Bild-Auswahl verwendet werden kann:

```
platform_gold    → Höchste Priorität
platform_silver
platform_bronze
team_gold
team_silver
team_bronze
```

**CSS Beispiel:**
```css
.avatar-frame.platform_gold {
  border: 3px solid gold;
  box-shadow: 0 0 15px gold;
}

.avatar-frame.team_gold {
  border: 3px solid #FFD700;
}
```

**Oder mit Bildern:**
```html
<div class="avatar-container">
  <img src="avatar.jpg" alt="User" />
  <img src="/frames/platform_gold.png" class="frame-overlay" />
</div>
```

## Workflow

### 1. Titel berechnen (Command)
```bash
php bin/console app:titles:calculate
```

Dies:
1. Ermittelt die Top 3 Torschützen platformweit
2. Ermittelt die Top 3 pro Team
3. Deaktiviert alte Titel (setzt `isActive=false`, `revokedAt`)
4. Erstellt neue `UserTitle`-Einträge mit `isActive=true`

### 2. Titel abrufen (API)
```php
// In einem Controller/API Endpoint
$titleService = $this->container->get(UserTitleService::class);
$titleData = $titleService->retrieveTitleDataForUser($user);

return $this->json($titleData);
```

### 3. Frontend-Anzeige
1. API-Request für User-Daten (inkl. Titel)
2. `avatarFrame`-Property auswerten
3. Entsprechenden Rahmen um Avatar anzeigen
4. Optional: Alle Titel in Profil-Übersicht anzeigen

## Beispiel-Szenario

**Benutzer "Max Mustermann":**
- 28 Tore platformweit (Platz 1 von allen)
- 28 Tore im Team "FC Example" (Platz 1 im Team)

**Vergebene Titel:**
1. Platform Top Scorer - Gold (Priority: 0) ← **Wird angezeigt**
2. Team Top Scorer - Gold (Priority: 100)

**Frontend:**
```json
{
  "avatarFrame": "platform_gold",
  "displayTitle": {
    "displayName": "Platform Torschützenkönig - Gold"
  }
}
```

Der Platform-Gold-Rahmen wird angezeigt, obwohl der Benutzer auch Team-Gold hätte.

## Erweiterbarkeit

### Neue Kategorien hinzufügen
1. Neue `titleCategory` definieren (z.B. `top_assist`)
2. In `TitleCalculationService` neue Berechnungsmethoden hinzufügen
3. In `UserTitleService::getTitleDisplayName()` deutschen Namen hinzufügen
4. Command erweitern oder separate Commands erstellen

### Zusätzliche Scopes
Könnte erweitert werden um:
- `league` - Liga-weit
- `region` - Regional
- `country` - Landesweit

Einfach neue Scope-Namen in Priority-Berechnung aufnehmen.

## Datenbank-Optimierung

### Indizes
```sql
-- Schnelle User-Abfrage
CREATE INDEX idx_user_titles_user_id ON user_titles(user_id);

-- Schnelle Team-Abfrage
CREATE INDEX idx_user_titles_team_id ON user_titles(team_id);

-- Schnelle aktive Titel
CREATE INDEX idx_user_titles_is_active ON user_titles(is_active);

-- Verhindert doppelte aktive Titel
CREATE UNIQUE INDEX uniq_user_title_active 
ON user_titles(user_id, title_category, title_scope, team_id, is_active);
```

## Caveats

1. **Titel sind nicht live**: Titel werden nur beim Ausführen des Commands aktualisiert
2. **Gleichstand**: Bei Gleichstand (gleiche Tor-Anzahl) entscheidet die Datenbank-Sortierung (Player-ID)
3. **Historische Titel**: Alte Titel bleiben in DB (`isActive=false`), können für Statistiken genutzt werden
4. **Team-Wechsel**: Bei Team-Wechsel bleiben alte Team-Titel erhalten (andere `team_id`)
5. **Null-Tore**: User mit 0 Toren erhalten keine Titel

## Testing

```bash
# 1. Testdaten erstellen (Goals mit Scorern)
# 2. Titel berechnen
php bin/console app:titles:calculate

# 3. Output prüfen
# 4. Datenbank prüfen
mysql> SELECT u.email, ut.title_category, ut.title_scope, ut.title_rank, ut.value 
       FROM user_titles ut 
       JOIN users u ON ut.user_id = u.id 
       WHERE ut.is_active = 1;
```
