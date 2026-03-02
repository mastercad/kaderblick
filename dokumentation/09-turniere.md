# Turniere

Kaderblick bietet eine vollständige **Turnierverwaltung** — von der Planung über die Gruppeneinteilung bis zum Endspiel. Turniere können verschiedene Formate haben und werden visuell übersichtlich dargestellt.

---

## Turnier-Übersicht

Auf der **Turniere-Seite** seht ihr alle angelegten Turniere. Pro Turnier wird angezeigt:

- **Name** des Turniers
- **Typ** (z. B. Hallenturnier, Vereinsturnier)
- **Zeitraum** (Startdatum und Enddatum)
- **Anzahl der Teams**

Klickt auf ein Turnier, um die **Detail-Seite** zu öffnen.

---

## Turnier-Details

Die Detail-Seite eines Turniers ist in mehrere Bereiche aufgeteilt:

### Kopfbereich

- **Turniername** als Überschrift
- **Typ**, **Zeitraum** und **Austragungsort**
- **Turnier-Einstellungen** (falls vorhanden):
  - **Rundendauer** — Dauer pro Spiel (z. B. 15 Minuten)
  - **Spielmodus** — z. B. „Round Robin" (Jeder gegen Jeden), „K.O.-System"
  - Weitere turnierspezifische Einstellungen

---

### Teilnehmende Teams

Alle am Turnier teilnehmenden Teams werden aufgelistet. Jedes Team hat:

| Feld | Beschreibung |
|------|-------------|
| **Teamname** | Name des Teams |
| **Setzplatz** (Seed) | Falls gesetzt — als Badge neben dem Namen (z. B. „#1") |
| **Gruppenkennung** | Falls in Gruppen eingeteilt — Badge mit Gruppenbuchstabe (z. B. „Gruppe A") |

> 💡 **Eigene Teams hervorgehoben**: Teams, die zu eurem Verein gehören, werden visuell hervorgehoben — so findet ihr eure Mannschaft sofort.

---

### Spiel-Übersicht (Matches)

Die Turnierspiele werden **nach Runde/Phase gruppiert** angezeigt. Typische Phasen:

- **Gruppenphase** — Alle Gruppenspiele
- **Viertelfinale**
- **Halbfinale**
- **Finale** / **Spiel um Platz 3**

Jedes Spiel wird als Karte dargestellt:

| Element | Beschreibung |
|---------|-------------|
| **Heim-Team** | Links — mit Teamname |
| **Gast-Team** | Rechts — mit Teamname |
| **Ergebnis** | In der Mitte, sobald das Spiel abgeschlossen ist |
| **Status** | Badge: „Geplant", „Laufend" oder „Beendet" |
| **Geplante Zeit** | Wann das Spiel stattfinden soll |
| **Spielort** | Falls abweichend vom Turnierort |

#### Match-Filter

Oben gibt es einen **Filter-Toggle**:

- **Alle Spiele** — zeigt sämtliche Turnierspiele
- **Meine Teams** — zeigt nur Spiele, an denen ein Team eures Vereins beteiligt ist

Das ist besonders bei großen Turnieren mit vielen Gruppen praktisch — ihr seht sofort, welche Spiele euch betreffen.

---

### Turnierbaum / Bracket

Bei K.O.-Turnieren ergibt sich ein **Turnierbaum**: 

- Spiele der nächsten Runde sind mit denen der aktuellen Runde verknüpft
- Der **Sieger** rückt automatisch in die nächste Runde vor (sobald das Spiel als beendet markiert wird)
- So könnt ihr den kompletten Turnierverlauf nachverfolgen — vom ersten Spiel bis zum Finale

---

## Turnier anlegen

> ℹ️ Das Anlegen und Bearbeiten von Turnieren ist in der Regel Trainern und Administratoren vorbehalten.

### Grunddaten

1. **Name** — z. B. „Nikolaus-Hallenturnier 2025"
2. **Typ** — Turnierart auswählen
3. **Startdatum** und **Enddatum**
4. **Einstellungen** (optional):
   - Rundendauer (Minuten pro Spiel)
   - Spielmodus (z. B. round_robin)
   - Weitere turnierspezifische Optionen

### Teams hinzufügen

1. Klickt auf **„Team hinzufügen"**
2. Wählt ein Team aus der Liste (alle bereits in Kaderblick angelegten Teams)
3. Optional: **Setzplatz** vergeben (1, 2, 3, ...)
4. Optional: **Gruppe** zuweisen (A, B, C, ...)
5. Wiederholt für alle teilnehmenden Teams

> 💡 Ihr könnt auch Teams von **anderen Vereinen** eintragen (wenn diese in Kaderblick angelegt sind) — perfekt für Turniere mit Gastmannschaften.

### Spiele generieren

Turnierspiele können **manuell** angelegt werden:

1. Klickt auf **„Spiel hinzufügen"**
2. Wählt **Heim-Team** und **Gast-Team**
3. Legt die **Runde** und **Phase** (Stage) fest (z. B. Gruppenphase, Halbfinale)
4. Optional: **Geplante Zeit** und **Spielort**
5. Optional: **Nächstes Spiel** verknüpfen (für den Turnierbaum)

Die Spiele werden dann in der Detail-Ansicht nach Phase sortiert angezeigt.

---

## Turnier mit Kalender verknüpfen

Ein Turnier kann mit einem **Kalender-Termin** verknüpft werden:

- Im Turnier wird ein Kalender-Termin erstellt, der den gesamten Turnierzeitraum abdeckt
- Dieser Termin erscheint im **Kalender** aller berechtigten Nutzer
- Die **Teilnahme** am Turnier kann über die Kalender-Teilnahme bestätigt werden (Zu-/Absage)

---

## Spiel-Details innerhalb des Turniers

Klickt auf ein Turnierspiel, um die **Spiel-Detail-Seite** zu öffnen. Dort findet ihr:

- Vollständige **Spiel-Informationen** (wie bei einem regulären Spiel)
- **Spielereignisse** (Tore, Karten, Auswechslungen)
- **Ergebnis** eintragen
- Verknüpfte **Videos**

> Das Turnierspiel verhält sich wie ein normales Spiel in Kaderblick — mit allen Funktionen (Ereignisse, Videos, Wetter usw.). Mehr dazu in [06-spielverwaltung.md](06-spielverwaltung.md).

---

## Tipps für Turniere

### Vorbereitung
- **Alle Teams vorher anlegen**: Legt Gastmannschaften rechtzeitig an — so könnt ihr sie beim Turnier einfach auswählen
- **Gruppen planen**: Überlegt euch die Gruppeneinteilung vorher und tragt Setzplätze und Gruppenkennung direkt beim Hinzufügen ein
- **Kalender-Termin erstellen**: Verknüpft das Turnier mit einem Kalender-Termin, damit alle den Termin sehen und zu-/absagen können

### Während des Turniers
- **Ergebnisse sofort eintragen**: Tragt Ergebnisse direkt nach jedem Spiel ein — so ist die Übersicht immer aktuell
- **Filter nutzen**: Bei großen Turnieren den „Meine Teams"-Filter verwenden, um den Überblick zu behalten
- **Spielereignisse dokumentieren**: Tragt Tore, Karten und Auswechslungen ein — die Statistik wird automatisch aktualisiert

### Nach dem Turnier
- **Komplette Übersicht**: Alle Ergebnisse bleiben erhalten und können jederzeit nachgeschaut werden
- **Videos verknüpfen**: Ladet Turniervideos hoch und verknüpft sie mit den einzelnen Spielen

---

## Häufige Fragen

### Was ist ein Setzplatz?
Der Setzplatz bestimmt die **Startposition** eines Teams im Turnierbaum. Team #1 (höchster Setzplatz) spielt in der Regel gegen das am niedrigsten gesetzte Team. Das verhindert, dass starke Teams in der ersten Runde aufeinandertreffen.

### Können Turnierspiele wie normale Spiele bearbeitet werden?
Ja! Ein Turnierspiel hat alle Funktionen eines normalen Spiels — Spielereignisse, Videos, Wetter. Es ist zusätzlich mit dem Turnier verknüpft.

### Was passiert, wenn ich ein Turnier lösche?
Das Turnier wird mit allen Turnier-Teams und Turnier-Spielzuordnungen gelöscht. Die zugehörigen Spiele selbst bleiben je nach Einstellung bestehen.

### Kann ich ein Turnier nachträglich bearbeiten?
Ja — ihr könnt jederzeit Teams hinzufügen/entfernen, Spiele anlegen und Einstellungen anpassen, solange das Turnier nicht abgeschlossen ist.

### Wie erkenne ich auf einen Blick, ob mein Team spielt?
Nutzt den **„Meine Teams"**-Filter oben in der Spielübersicht. Außerdem werden eure Vereinsteams in der Teamliste visuell hervorgehoben.
