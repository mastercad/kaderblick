# Spiele

Die Spielverwaltung ist das Herzstück von Kaderblick. Hier werden alle Spiele eures Teams dokumentiert – von der Ansetzung über das Live-Protokoll bis zur Nachbereitung mit Videos.

---

## Die Spielübersicht

Unter **„Spiele"** im Menü findet ihr alle Spiele eures Teams – übersichtlich aufgeteilt in drei Kategorien:

| Bereich | Was steht dort? |
|---|---|
| **Laufende Spiele** | Spiele, die gerade stattfinden (Startzeit war, Endzeit liegt noch in der Zukunft) |
| **Bevorstehende Spiele** | Geplante Spiele, die noch nicht begonnen haben – sortiert nach Datum |
| **Vergangene Spiele** | Abgelaufene Spiele mit dem Endergebnis |

---

## Ein Spiel anlegen

Nur Administratoren und berechtigte Trainer können neue Spiele anlegen.

1. Im Menü auf **„Spiele"** klicken
2. Auf **„Neues Spiel"** klicken
3. Folgende Angaben machen:

| Feld | Beschreibung | Pflicht? |
|---|---|---|
| **Heimteam** | Welches Team spielt zuhause? | Empfohlen |
| **Auswärtsteam** | Welches Team ist zu Gast? (darf nicht dasselbe sein) | Empfohlen |
| **Spieltyp** | Ligaspiel, Freundschaftsspiel, Pokalspiel, … | ✅ Ja |
| **Spielort** | Wo findet das Spiel statt? | Empfohlen |
| **Liga** | Optionale Zuordnung zu einer Liga | Nein |

4. Speichern

> 💡 **Wichtig:** Datum und Uhrzeit werden über den **Kalender** gesteuert. Das Spiel bekommt automatisch einen Terminslot, den ihr im Kalender sehen und zu dem Spieler ihre Zu-/Absage geben können.

---

## Das Spiel live dokumentieren

Die Detailseite eines Spiels ist der zentrale Ort für die Spieldokumentation. Öffnet das Spiel, indem ihr in der Liste darauf klickt.

### Spielereignisse erfassen

Während des Spiels (oder im Nachhinein) können alle wichtigen Ereignisse eingetragen werden:

| Ereignis | Wann eintragen |
|---|---|
| ⚽ **Tor** | Ein reguläres Tor wurde erzielt |
| 🙈 **Eigentor** | Ein Tor gegen das eigene Team |
| 🟨 **Gelbe Karte** | Ein Spieler wurde verwarnt |
| 🟥 **Rote Karte** | Ein Spieler wurde des Feldes verwiesen |
| 🟨🟥 **Gelb-Rote Karte** | Zweite Gelbe Karte = Feldverweis |
| 🔄 **Wechsel** | Ein Spieler wird ausgewechselt |
| 👟 **Assist/Vorlage** | Vorlagengeber zu einem Tor |

So wird ein Ereignis eingetragen:
1. Auf **„Ereignis hinzufügen"** klicken
2. Ereignistyp wählen (z. B. Tor)
3. Spieler auswählen (wer hat das Tor geschossen?)
4. Spielminute eintragen
5. Bei Tor: Optional den Vorbereiter (Assist-Geber) angeben
6. Speichern

### Der Spielstand

Der aktuelle Spielstand wird **automatisch** aus den eingetragenen Toren und Eigentoren berechnet – ihr müsst das Ergebnis nicht separat eingeben. Das System zählt die Tore für jedes Team und zeigt das aktuelle Ergebnis an.

---

## Wechsel erfassen

Spielerwechsel werden separat dokumentiert:

1. Auf **„Wechsel hinzufügen"** klicken
2. Den **eingewechselten** Spieler wählen
3. Den **ausgewechselten** Spieler wählen
4. Spielminute eintragen
5. Optional: Wechselgrund angeben
6. Speichern

---

## Spiel abschließen

Wenn das Spiel vorbei ist, könnt ihr es als **„Abgeschlossen"** markieren. Damit wird es in die Kategorie der vergangenen Spiele verschoben und das Ergebnis ist final festgehalten.

---

## Spieldetailseite – was seht ihr dort?

Die Detailseite eines Spiels zeigt alles auf einen Blick:

- **Heimteam vs. Auswärtsteam** mit aktuellem Spielstand
- **Chronologische Ereignisliste** – alle Tore, Karten und Wechsel in zeitlicher Reihenfolge
- **Verknüpfte Videos** – falls Spielvideos hochgeladen wurden, könnt ihr Spielszenen direkt anspringen
- **Wetterdaten** – das Wetter zum Spielzeitpunkt
- **Teilnahmestatus** – wer hat zugesagt, wer fehlt?

---

## Spielorte verwalten

Spielorte (Sportanlagen, Plätze) werden einmalig angelegt und können dann bei jedem Spiel ausgewählt werden. Das spart Zeit bei wiederkehrenden Heimspielen.

Neue Spielorte anlegen: Im Menü auf **„Spielorte"** klicken.

---

## Automatische Synchronisation mit fussball.de

Wenn euer Team mit dem Portal **fussball.de** verknüpft ist, werden Spielergebnisse nach dem Spieltag automatisch übernommen. Ihr müsst nichts manuell eintragen – das System erledigt das von alleine!

---

## Berechtigungen: Wer sieht was?

- **Spieler und Eltern** sehen nur die Spiele des Teams, dem sie zugeordnet sind
- **Trainer** sehen alle Spiele ihrer Teams und können Spielereignisse nachtragen
- **Administratoren** haben vollen Zugriff

---

## Häufige Fragen

**Warum sehe ich ein Spiel nicht in der Liste?**
→ Ihr seht nur Spiele eures eigenen Teams. Prüft, ob eure Teamzuordnung korrekt eingestellt ist (sprecht ggf. den Trainer an).

**Das Ergebnis stimmt nicht – wie korrigiere ich das?**
→ Öffnet das Spiel und korrigiert oder löscht das falsch eingetragene Spielereignis (Tor oder Eigentor).

**Kann ich Tore auch noch nach dem Spiel nachtragen?**
→ Ja! Die Spielereignisse können jederzeit nachträglich erfasst oder korrigiert werden, solange das Spiel nicht gesperrt ist.

**Was ist, wenn Heim- und Auswärtsteam nicht aus unserem Verein kommen?**
→ Das Heimteam oder Auswärtsteam darf auch freigelassen werden – z. B. wenn ein externes Team noch nicht in Kaderblick erfasst ist. Das Ergebnis kann trotzdem protokolliert werden.

---

## Spielübersicht

Die Hauptseite der Spielverwaltung (`/games`) gliedert Spiele in drei Bereiche:

| Bereich | Beschreibung |
|---|---|
| **Laufende Spiele** | Spiele, deren Start in der Vergangenheit und deren Ende in der Zukunft liegt |
| **Bevorstehende Spiele** | Spiele mit Startzeit in der Zukunft, aufsteigend sortiert |
| **Vergangene Spiele** | Abgeschlossene Spiele, absteigend nach Datum, mit Endergebnis |

---

## Spiel anlegen

Ein Spiel verbindet zwei Teams miteinander und enthält alle spielrelevanten Informationen.

### Felder eines Spiels

| Feld | Beschreibung | Pflicht |
|---|---|---|
| Heimteam | Eines der registrierten Teams | Nein* |
| Auswärtsteam | Darf nicht dasselbe sein wie das Heimteam | Nein* |
| Spieltyp | z. B. Ligaspiel, Freundschaftsspiel, Turnierspiel | Ja |
| Ort / Location | Sportanlage, auf der gespielt wird | Nein |
| Liga | Optionale Zuordnung zu einer Liga | Nein |
| Heim-Tore | Endergebnis Heimteam | Nein |
| Auswärts-Tore | Endergebnis Auswärtsteam | Nein |
| Abgeschlossen | Markiert ein Spiel als beendet | Nein |
| Kalender-Ereignis | Verknüpfter Termin (steuert Datum/Uhrzeit und Teilnahme) | Nein |
| fussball.de-ID | Kennung für automatische Synchronisation | Nein |

> *Heimteam und Auswärtsteam können bewusst leer gelassen werden, z. B. bei externen Spielen gegen unbekannte Gegner.

Ein Spiel ist **immer** mit einem Kalender-Ereignis verknüpft – das Datum und die Uhrzeit werden dort verwaltet (siehe [Kalender & Teilnahme](07-kalender-teilnahme.md)).

---

## Spieltypen

Spieltypen kategorisieren Spiele und können verwaltet werden. Typische Werte: Ligaspiel, Pokalspiel, Freundschaftsspiel, Turnierspiel. Die Spieltypen sind über die Datenbank konfigurierbar.

---

## Spielorte (Locations)

Spielorte sind eigenständige Entitäten, die Name und optional weitere Infos speichern. Sie können mehreren Spielen zugeordnet werden. Verwaltung über Route `/locations`.

---

## Spielereignisse (GameEvents)

Spielereignisse protokollieren alles, was während eines Spiels passiert. Jedes Ereignis ist dem Spiel, einem Team und optional einem Spieler sowie einer Spielminute zugeordnet.

### Bekannte Ereignistypen (GameEventTypes)

| Code | Bedeutung |
|---|---|
| `goal` | Reguläres Tor |
| `own_goal` | Eigentor |
| `yellow_card` | Gelbe Karte |
| `yellow_red_card` | Gelb-Rote Karte |
| `red_card` | Rote Karte |
| `substitution` | Spielerwechsel |
| `assist` | Vorlage (Assist) |

Spielereignistypen sind über `/gameEventTypes` konfigurierbar und erweiterbar.

### Score-Berechnung

Der aktuelle Spielstand wird nicht als fester Wert gespeichert, sondern wird **live aus den Spielereignissen berechnet**: Alle Ereignisse vom Typ `goal` werden gezählt, Eigentore (`own_goal`) werden dem gegnerischen Team gutgeschrieben.

---

## Tore (Goals)

Tore sind eigenständige Entitäten und werden zusätzlich zu den Spielereignissen gespeichert. Sie enthalten:

| Feld | Beschreibung |
|---|---|
| Schütze | Spieler, der das Tor schoss |
| Vorbereiter (Assist) | Spieler, der das Tor vorbereitete |
| Spiel | Zugehöriges Spiel |
| Spielminute | Minute des Tors |

---

## Wechsel (Substitutions)

Spielerwechsel werden als eigenständige Entität gespeichert:

| Feld | Beschreibung |
|---|---|
| Einwechselspieler | Spieler, der eingewechselt wird |
| Auswechselspieler | Spieler, der ausgewechselt wird |
| Spiel | Zugehöriges Spiel |
| Spielminute | Minute des Wechsels |
| Grund | Optionaler Wechselgrund (aus `SubstitutionReason`) |

---

## Spieldetailansicht

Die Detailseite eines Spiels (`/games/:id`) zeigt:

- Heimteam vs. Auswärtsteam mit aktuellem Spielstand
- Chronologische Liste aller Spielereignisse (Tore, Karten, Wechsel)
- Verknüpfte Videos mit Zeitleisten-Funktion (YouTube-Timestamps)
- Kamera-Zuordnungen für Videoanalyse
- Wetterdaten des Spieltermins

Benutzer sehen ein Spiel nur, wenn sie dem zugehörigen Verein/Team zugeordnet sind (Voter-Prüfung).

---

## fussball.de-Synchronisation

Spiele und Teams können mit [fussball.de](https://www.fussball.de) verknüpft werden. Sobald die Verknüpfung eingerichtet ist, werden Spielergebnisse automatisch synchronisiert – ohne manuelle Dateneingabe.

