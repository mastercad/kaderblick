# Aufstellungen & Formationen

Der Aufstellungsplaner ist eines der Herzstücke für Trainer: Hier plant ihr eure Startelf visuell auf einem Spielfeld — per Drag & Drop, mit echten Spielern aus eurem Kader.

---

## Übersicht der Aufstellungen

Unter **Trainer → Aufstellungen** (oder **Admin → Verwaltung → Aufstellungen**) seht ihr alle gespeicherten Aufstellungen als **Karten-Raster**:

Jede Karte zeigt:
- Den **Namen** der Aufstellung (z. B. „Ligaspiel vs. FC Muster — Startelf")
- Den **Formations-Typ** als Badge (z. B. „4-4-2", „4-3-3")
- Eine **Miniatur-Vorschau** des Spielfelds mit den platzierten Spielern als kleine farbige Kreise
- **Bearbeiten** (✏️) und **Löschen** (🗑️) Buttons

Wenn noch keine Aufstellungen existieren, seht ihr den Hinweis: **„Erstellen Sie jetzt Ihre erste Aufstellung"** als Direktlink zum Anlegen.

---

## Eine neue Aufstellung erstellen

### Den Editor öffnen

1. Klickt auf **„Neue Aufstellung"** oder den Direktlink in der leeren Übersicht
2. Es öffnet sich der **Aufstellungs-Editor** — ein zweigeteiltes Fenster

### Der Editor im Detail

Der Editor besteht aus **zwei Spalten**:

#### Linke Seite — Das Spielfeld

Ein **Fußball-Halbfeld** als Hintergrundbild (standardmäßig ein grünes Rasenfeld mit weißen Linien). Hier platziert ihr die Spieler:

- Jeder Spieler wird als **farbiger Kreis** dargestellt (ca. 32px)
  - **Echte Spieler** → in der Vereinsfarbe (primäre Farbe der App)
  - **Generische Spieler** (Platzhalter ohne konkreten Spieler) → in Grau
- Im Kreis steht der **Name** oder eine Abkürzung
- Kreise können **frei auf dem Feld verschoben werden** (Drag & Drop) — die Position wird in Prozent berechnet, sodass die Aufstellung auf jeder Bildschirmgröße korrekt dargestellt wird

> 💡 Das System platziert neue Spieler automatisch auf eine **freie Position** im 15%-Raster, sodass sich keine Spieler überlappen.

#### Rechte Seite — Spielerliste & Steuerung

Hier verwaltet ihr den Kader für die Aufstellung:

| Element | Beschreibung |
|---------|-------------|
| **Name der Aufstellung** | Textfeld für den Namen (z. B. „Startelf Samstag") |
| **Formations-Typ** | Dropdown-Auswahl: 4-4-2, 4-3-3, 3-5-2, etc. (bestimmt auch das Hintergrundbild) |
| **Team-Auswahl** | Dropdown mit euren Teams — filtert die Spielerliste nach Team |
| **„Generischen Spieler hinzufügen"** | Button, um einen Platzhalter-Kreis hinzuzufügen (z. B. für noch unbesetzte Positionen) |
| **Verfügbare Spieler** | Liste aller Spieler des gewählten Teams — mit „Hinzufügen"-Button |
| **Aktive Spieler** | Liste der bereits platzierten Spieler — mit „Entfernen"-Button |

### Spieler platzieren — Schritt für Schritt

1. **Team wählen** — im Dropdown das richtige Team auswählen
2. **Spieler hinzufügen** — in der Spielerliste auf **„Hinzufügen"** klicken. Der Spieler erscheint als farbiger Kreis auf dem Feld.
3. **Position bearbeiten** — den Kreis mit der Maus (oder dem Finger auf dem Handy) an die gewünschte Stelle auf dem Spielfeld **ziehen**
4. **Wiederholen** — für alle Spieler der Aufstellung
5. Optional: **Generische Spieler** hinzufügen, wenn Positionen noch nicht besetzt sind
6. **Speichern** — Aufstellung wird gespeichert und erscheint in der Übersicht

### Spieler entfernen

In der Liste der aktiven Spieler (rechte Seite) klickt auf **„Entfernen"** — der Spieler verschwindet vom Feld.

---

## Formations-Typen

Formations-Typen legen das taktische Grundgerüst fest und bestimmen das **Hintergrundbild** des Spielfelds:

| Formation | Beschreibung | Typische Verwendung |
|-----------|-------------|---------------------|
| **4-4-2** | 4 Abwehr, 4 Mittelfeld, 2 Sturm | Klassisches Grundsystem |
| **4-3-3** | 4 Abwehr, 3 Mittelfeld, 3 Sturm | Offensives System |
| **3-5-2** | 3 Abwehr, 5 Mittelfeld, 2 Sturm | Mittelfeld-dominantes Spiel |
| **4-2-3-1** | 4 Abwehr, 2 DM, 3 OM, 1 Sturm | Flexibles System |
| **5-3-2** | 5 Abwehr, 3 Mittelfeld, 2 Sturm | Defensives System |
| **3-4-3** | 3 Abwehr, 4 Mittelfeld, 3 Sturm | Sehr offensiv |

Jeder Formations-Typ kann ein eigenes **Hintergrundbild** und **Standard-Positionen** haben. So könnt ihr z. B. bei der 4-4-2 direkt die typischen Positionen als Ausgangspunkt verwenden und die Spieler dann feinjustieren.

> 💡 Neue Formations-Typen können von Administratoren unter **Admin → Stammdaten → Aufstellungstypen** angelegt werden (siehe [20-admin.md](20-admin.md)).

---

## Aufstellungen bearbeiten

1. In der Übersicht auf das **Bearbeiten-Symbol** (✏️) der gewünschten Aufstellung klicken
2. Der Editor öffnet sich mit der gespeicherten Aufstellung
3. Spieler verschieben, hinzufügen oder entfernen
4. **Speichern** — die Änderungen werden übernommen

---

## Aufstellungen löschen

1. In der Übersicht auf das **Löschen-Symbol** (🗑️) klicken
2. Ein **Bestätigungsdialog** erscheint
3. Bestätigen → die Aufstellung wird endgültig entfernt

---

## Tipps für die Aufstellungsplanung

- **Erst das Team wählen**, damit nur die richtigen Spieler angezeigt werden
- **Generische Spieler** nutzen, um die Formation erst taktisch zu planen und dann konkrete Spieler zuzuweisen
- **Namen der Aufstellungen** beschreibend vergeben: „U13 Liga Heimspiel 4-3-3" statt nur „Aufstellung 1"
- **Verschiedene Varianten anlegen**: z. B. eine offensive und eine defensive Aufstellung für das gleiche Spiel
- **Am Laptop/Tablet arbeiten**: Das Verschieben der Spieler auf dem Feld funktioniert auf größeren Bildschirmen bequemer als auf dem Handy

---

## Wer kann Aufstellungen nutzen?

| Rolle | Kann Aufstellungen… |
|-------|---------------------|
| **Spieler** | Ansehen (eigene Aufstellungen) |
| **Trainer** | Erstellen, bearbeiten, löschen — über das Trainer-Menü |
| **Administrator** | Erstellen, bearbeiten, löschen — über den Admin-Bereich. Zusätzlich: Formations-Typen verwalten |

---

## Häufige Fragen

### Kann ich die gleichen Spieler in mehreren Aufstellungen verwenden?
Ja, ein Spieler kann in beliebig vielen Aufstellungen gleichzeitig vorkommen.

### Was sind „generische Spieler"?
Das sind Platzhalter-Kreise ohne echten Spieler dahinter — z. B. für Positionen, die noch nicht besetzt sind. Sie werden grau statt farbig dargestellt.

### Kann ich die Positionen nachträglich verschieben?
Ja — öffnet die Aufstellung zum Bearbeiten und zieht die Kreise einfach an die neue Position.

### Warum sehe ich keine Spieler in der Liste?
Ihr müsst zuerst ein **Team** im Dropdown auswählen. Dann werden nur die Spieler dieses Teams angezeigt. Falls das Team keine Spieler hat, muss erst ein Spieler dem Team zugewiesen werden (siehe [04-spieler.md](04-spieler.md)).

### Wie werden die Hintergrundbilder festgelegt?
Jeder Formations-Typ hat ein eigenes Hintergrundbild (z. B. ein grünes Halbfeld). Administratoren können unter **Admin → Stammdaten** neue Formations-Typen mit eigenen Hintergründen anlegen.
