# Spieler

Der Kader ist das Herzstück jedes Teams. In Kaderblick kann der Kader vollständig digital verwaltet werden – von den Stammdaten über Positionen bis hin zur kompletten Vereinshistorie jedes Spielers.

---

## Einen neuen Spieler anlegen

Nur Administratoren und berechtigte Trainer können neue Spieler anlegen.

1. Im Menü auf **„Spieler"** klicken
2. Oben auf **„Neuer Spieler"** klicken
3. Die Daten eingeben (siehe unten)
4. Auf **„Speichern"** klicken

### Welche Daten werden eingetragen?

| Feld | Beschreibung | Pflicht? |
|---|---|---|
| **Vorname** | Vorname des Spielers | ✅ Ja |
| **Nachname** | Nachname des Spielers | ✅ Ja |
| **Geburtsdatum** | Für Altersklassenberechnung wichtig | Empfohlen |
| **Hauptposition** | Die bevorzugte Spielposition | ✅ Ja |
| **Alternativpositionen** | Weitere Positionen, die der Spieler spielen kann | Nein |
| **Starker Fuß** | Rechts, Links oder Beidfüßig | Empfohlen |
| **Körpergröße** | In Zentimetern | Nein |
| **Körpergewicht** | In Kilogramm | Nein |
| **Nationalität(en)** | Kann auch mehrere sein | Nein |

---

## Positionen in Kaderblick

Jeder Spieler hat eine **Hauptposition** und optional weitere **Alternativpositionen**. So weiß der Trainer auf einen Blick, wer wo einsetzbar ist.

Verfügbare Positionen werden vom Vereinsadministrator angelegt und gepflegt (z. B. Torwart, Innenverteidiger, Mittelfeld, Stürmer, linkes Mittelfeld, etc.).

---

## Spieler einem Team zuordnen

Ein Spieler muss einem Team zugeordnet werden – erst dann erscheint er in der Mannschaftsliste und kann in Spielen eingesetzt werden.

Die Zuordnung enthält immer einen **Zeitraum** (Von – Bis), damit das System jederzeit weiß, welcher Spieler **aktuell** zu welchem Team gehört. Ehemalige Zugehörigkeiten bleiben als Geschichte gespeichert.

So ordnet ihr einen Spieler einem Team zu:

1. Spieler in der Liste öffnen
2. Auf **„Team zuordnen"** klicken
3. Team auswählen
4. Trikotnummer und Rolle angeben (z. B. Stammspieler)
5. Startdatum eintragen (das Enddatum kann leer bleiben, wenn die Zuordnung aktuell noch gilt)
6. Speichern

### Was bedeutet das Enddatum?

- **Leer gelassen**: Der Spieler ist aktuell in diesem Team aktiv
- **Datum eingetragen**: Der Spieler war bis zu diesem Datum im Team (z. B. nach einem Vereinswechsel)

Das System zeigt immer nur die **aktuell gültigen** Zuordnungen an, wenn es um laufende Spiele oder Termine geht.

---

## Spieler einem Verein zuordnen

Ähnlich wie beim Team funktioniert die **Vereinszuordnung**: Ein Spieler wird mit einem Start- und optionalem Enddatum einem Verein zugewiesen. So ist die vollständige Vereinskarriere eines Spielers dokumentiert.

---

## Nationalitäten

Spieler können mehreren Nationalitäten zugeordnet sein – praktisch z. B. bei Spielern mit Doppelstaatsbürgerschaft oder für internationale Turniere.

---

## Spielerstatistiken

Kaderblick sammelt automatisch Statistiken für jeden Spieler, sobald Spielereignisse erfasst werden:

- **Tore** – alle geschossenen Tore mit Spielbezug und Minute
- **Assists (Vorlagen)** – alle Torvorlagen
- **Spiele** – Anzahl der Spielteilnahmen

Die Statistiken werden über die Detailansicht des Spielers einsehbar und fließen auch in Berichte und Auswertungen ein.

---

## Spieler mit einem Benutzerkonto verknüpfen

Damit ein Spieler sich selbst bei Kaderblick einloggen kann, muss sein **Spieler-Datensatz** mit einem **Benutzerkonto** verknüpft werden. Das macht der Vereinsadministrator im Bereich [Administration → Nutzerzuordnungen](20-admin.md).

Sobald die Verknüpfung besteht, sieht der Spieler nach dem Login:
- Seine eigenen Spiele und Statistiken
- Termine seines Teams
- Aufgaben, die ihm zugewiesen wurden

---

## Spieler suchen und filtern

In der Spielerliste könnt ihr nach Name, Position und Team filtern. So findet ihr auch in einem großen Verein schnell den gesuchten Spieler.

---

## Spieler bearbeiten oder löschen

- **Bearbeiten**: Klickt auf den Spielernamen oder das Stift-Symbol – alle Felder können geändert werden
- **Löschen**: Klickt auf das Papierkorb-Symbol – eine Sicherheitsabfrage erscheint, bevor der Spieler wirklich gelöscht wird

> ⚠️ **Achtung beim Löschen:** Wenn ein Spieler gelöscht wird, gehen auch seine Statistiken, Zuordnungen und Spielereignisse verloren. Überlegt gut, ob ihr wirklich löschen oder nur die Zuordnung zum aktuellen Team beenden möchtet.

---

## Häufige Fragen

**Warum taucht der Spieler nicht in der Aufstellung auf?**
→ Der Spieler muss dem Team mit einem gültigen Zeitraum zugeordnet sein. Prüft, ob das Enddatum korrekt gesetzt ist – oder ob gar keine Teamzuordnung existiert.

**Kann ein Spieler gleichzeitig in zwei Teams sein?**
→ Ja! Einfach für das zweite Team ebenfalls eine Zuordnung anlegen. Das ist z. B. für Spieler sinnvoll, die sowohl in der Jugend als auch in der Reserve spielen.

**Den Spieler gibt es schon – muss er neu angelegt werden?**
→ Nein! Wenn der Spieler bereits im System ist (z. B. von einem vorigen Trainer angelegt), einfach die Zuordnung aktualisieren oder eine neue hinzufügen.
