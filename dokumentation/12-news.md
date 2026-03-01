# News & Vereinsnachrichten

Im News-Bereich veröffentlicht euer Verein aktuelle Nachrichten, Ankündigungen und Berichte – von Spielberichten über Vereinsfeiern bis zu wichtigen Infos für alle Mitglieder.

---

## Neuigkeiten lesen

1. Im Menü auf **„News"** klicken
2. Ihr seht eine Liste aller aktuellen Beiträge – die neuesten zuerst
3. Klickt auf einen Beitrag, um ihn vollständig zu lesen

Die Vorschau in der Liste zeigt Titel und einen kurzen Einleitungstext – so könnt ihr auf einen Blick entscheiden, was euch interessiert.

---

## Was erscheint in den News?

Vereinsadministratoren und berechtigte Personen können News-Beiträge veröffentlichen. Typische Inhalte sind:

- 📋 Spielberichte nach dem Spieltag
- 📢 Wichtige Ankündigungen (z. B. Trainingsausfall, Hallenzeiten)
- 🎉 Berichte von Vereinsfeiern oder besonderen Ereignissen
- 📸 Fotogalerien vom letzten Turnier
- 🔧 Infos zu Trainingszeiten und Änderungen

---

## Benachrichtigung bei neuen Beiträgen

Wenn ein neuer Beitrag veröffentlicht wird, könnt ihr eine **Benachrichtigung** erhalten – so verpasst ihr keine wichtigen Infos. Wie ihr Benachrichtigungen einrichtet, erklärt das Kapitel [Benachrichtigungen](15-benachrichtigungen.md).

---

## News schreiben (nur für Berechtigte)

Falls ihr die Berechtigung habt, neue Beiträge zu veröffentlichen:

1. Im News-Bereich auf **„Neuer Beitrag"** klicken
2. Titel eingeben
3. Den vollständigen Beitrag schreiben
4. Einen kurzen Vorschautext formulieren (erscheint in der Liste)
5. Optional: Bilder einbinden
6. Auf **„Veröffentlichen"** klicken – der Beitrag ist sofort sichtbar

---

## Häufige Fragen

**Wie lange bleiben News sichtbar?**
→ News bleiben dauerhaft verfügbar, bis sie gelöscht werden. Ältere Beiträge rutschen in der Liste nach unten.

**Kann ich einen Beitrag kommentieren?**
→ Das ist in der aktuellen Version nicht vorgesehen. Für Rückmeldungen nutzt die Nachrichten-Funktion direkt.

**Wer darf News schreiben?**
→ Nur Administratoren oder von ihnen berechtigte Personen.

---

## Beiträge verwalten

News-Beiträge werden von berechtigten Benutzern erstellt. Jeder Beitrag enthält:

| Feld | Beschreibung |
|---|---|
| Titel | Überschrift des Beitrags |
| Teaser / Vorschautext | Kürzere Zusammenfassung für die Listenansicht |
| Inhalt | Vollständiger Beitrag (Freitext / Rich Text) |
| Veröffentlichungsdatum | Wann der Beitrag erscheint |
| Kategorie / Tags | Optionale Klassifizierung |

---

## Frontend

| Route | Inhalt |
|---|---|
| `/news` | Liste aller News-Beiträge, neueste zuerst |
| `/news/:id` | Vollansicht eines einzelnen Beitrags |

Die News-Liste gibt einen Überblick mit Vorschautext. Beim Klick auf einen Beitrag öffnet sich die Detailansicht mit dem vollen Inhalt.

