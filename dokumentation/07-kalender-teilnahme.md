# Kalender & Termine

Der Kalender ist euer zentraler Terminplaner für alles rund um den Verein – Spiele, Trainings, Besprechungen und alle anderen Events auf einen Blick. Und das Beste: Ihr könnt direkt mitteilen, ob ihr dabei seid oder nicht.

---

## Der Vereinskalender

Unter **„Kalender"** im Menü findet ihr alle Termine eures Teams übersichtlich dargestellt. Je nach Ansicht (Monat, Woche oder Tag) seht ihr:

- Spieltermine mit Heim- oder Auswärtsangabe
- Trainingseinheiten
- Teambesprechungen und sonstige Events
- Turniere

Ein Klick auf einen Termin öffnet die Detailansicht mit allen Infos.

---

## Einen neuen Termin anlegen

Trainer und Administratoren können Termine anlegen:

1. Im Kalender auf das **„+"-Symbol** (FAB-Button) klicken oder in der gewünschten Tag auf „Neuer Termin"
2. Folgende Angaben machen:

| Feld | Was muss ich eintragen? |
|---|---|
| **Titel** | Name des Termins, z. B. „Abschlusstraining" oder „Auswärtsspiel FC Beispiel" |
| **Beschreibung** | Optional – z. B. Treffpunkt, Sonderinfos |
| **Startdatum & -uhrzeit** | Wann beginnt der Termin? |
| **Enddatum & -uhrzeit** | Optional – wann endet er? |
| **Termintyp** | Spiel, Training, Turnier, Besprechung, … |
| **Spielort** | Optional – wo findet der Termin statt? |

3. Speichern – der Termin erscheint sofort im Kalender!

---

## Termintypen

Termine werden nach Typ kategorisiert. Typische Termintypen sind:

- 🟢 **Training** – regelmäßige Trainingseinheiten
- 🔵 **Spiel** – offizielle Pflichtspiele oder Freundschaftsspiele
- 🟡 **Turnier** – Turniertage
- 🟠 **Besprechung** – Mannschaftssitzungen, Elternabende
- ⚪ **Sonstiges** – alles Weitere

Der Termintyp bestimmt auch, welche zusätzlichen Infos angezeigt werden – bei einem Spiel etwa das Ergebnis, bei einem Turnier den Spielplan.

---

## Zu- und Absagen – Teilnahme bestätigen

Das ist eine der meistgenutzten Funktionen für Spieler und Eltern! Für jeden Termin kannst du mitteilen, ob du dabei bist:

### So gebt ihr eine Zu- oder Absage

1. Den Termin im Kalender öffnen
2. Auf den Teilnahme-Bereich klicken
3. Euren Status wählen:

| Status | Bedeutung |
|---|---|
| ✅ **Zugesagt** | Ich bin dabei! |
| ❌ **Abgesagt** | Ich kann leider nicht |
| ❓ **Noch offen** | Ich weiß es noch nicht |

4. Optional: Eine kurze Notiz hinterlassen (z. B. „Komme etwas später" oder „Muss zum Arzt")
5. Bestätigen – fertig!

### Wann sollte ich absagen?

Je früher, desto besser – der Trainer sieht in Echtzeit, mit wie vielen Spielern er planen kann. Eine frühe Absage hilft enorm bei der Spielvorbereitung!

### Benachrichtigungen bei Statusänderungen

Wenn jemand seinen Status ändert (z. B. von „Zugesagt" auf „Abgesagt"), erhalten alle anderen Teilnehmer des Termins eine kurze **Benachrichtigung** – so ist jeder immer auf dem aktuellen Stand.

---

## Die Teilnahmeübersicht

Trainer sehen in der Detailansicht eines Termins eine vollständige **Teilnahmeliste**:

- 🟢 Zugesagt
- 🔴 Abgesagt
- 🟡 Noch offen / Keine Rückmeldung

Außerdem ist ersichtlich, ob es sich um einen tatsächlichen **Kaderspieler** handelt oder um jemanden, der z. B. nur als Elternteil am Event teilnimmt.

---

## Wetterdaten

Für Termine, die draußen stattfinden, zeigt Kaderblick automatisch die **Wetterdaten** zum Veranstaltungszeitpunkt an. So weiß jeder schon vorher, was ihn beim Spiel oder Training erwartet (Temperatur, Regen, Wind).

---

## Fahrgemeinschaften zum Termin

Direkt aus der Termindetailansicht heraus kann das **Fahrgemeinschafts-System** genutzt werden – ideal für Auswärtsspiele. Mehr dazu im Kapitel [Fahrgemeinschaften](18-fahrgemeinschaften.md).

---

## Häufige Fragen

**Ich sehe einen Termin nicht im Kalender – warum?**
→ Termine sind immer einem Team zugeordnet. Ihr seht nur Termine eures Teams. Prüft eure Teamzuordnung oder sprecht den Trainer an.

**Kann ich einen Termin nachträglich ändern?**
→ Ja, Trainer und Administratoren können Termine jederzeit bearbeiten.

**Was passiert, wenn ich gar keine Rückmeldung gebe?**
→ Der Status bleibt auf „Noch offen". Trainer können in der Übersicht sehen, wer noch keine Rückmeldung gegeben hat. Bitte sagt möglichst frühzeitig zu oder ab – das hilft dem Trainer bei der Planung!

**Kann ich meine Absage zurückziehen?**
→ Ja! Öffnet den Termin einfach erneut und ändert den Status auf „Zugesagt".

---

## Kalender-Ereignisse (CalendarEvents)

Jeder Termin ist ein `CalendarEvent`. Spiele, Turniere und sonstige Termine sind immer über ein Kalender-Ereignis mit Datum und Uhrzeit versehen.

### Felder eines Kalender-Ereignisses

| Feld | Beschreibung |
|---|---|
| Titel | Anzeigename des Termins |
| Beschreibung | Optionaler Freitext |
| Startdatum/-uhrzeit | Pflichtfeld |
| Enddatum/-uhrzeit | Optional |
| Typ | Kategorie des Termins (z. B. Spiel, Training, Meeting) |
| Ort | Optionale Verknüpfung mit einem `Location`-Objekt |
| Erstellt von | Benutzer, der den Termin angelegt hat |

### Beziehungen

Ein `CalendarEvent` kann einem von drei Typen zugeordnet sein:

| Verknüpfung | Bedeutung |
|---|---|
| `Game` | Es handelt sich um ein Spieltermin |
| `Tournament` | Es handelt sich um einen Turniertermin |
| *(kein)* | Allgemeiner Termin (z. B. Training, Besprechung) |

Diese Verknüpfung ist 1:1 und exklusiv.

---

## Kalender-Ereignistypen

Typen gruppieren Termine nach ihrer Art. Typische Werte:
- Spiel
- Training
- Turnier
- Teambesprechung
- Sonstige

Typen sind frei konfigurierbar.

---

## Wetterdaten

Zu Kalender-Ereignissen werden automatisch Wetterdaten hinterlegt und auf der Termindetailseite angezeigt – so wisst ihr schon vorher, ob ihr Regenjacken einpacken sollt.

