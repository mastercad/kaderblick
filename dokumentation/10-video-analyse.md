# Videos & Spielanalyse

Mit der Video-Funktion wird die Spielnachbereitung bei Kaderblick richtig stark: Verknüpft Spielvideos direkt mit dem Spielgeschehen – und springt dann mit einem einzigen Klick genau zur Szene, die ihr analysieren wollt.

---

## Was ist die Videoanalyse?

Stellt euch vor: Ihr habt das Spiel auf YouTube oder als Datei vorliegen. Ihr tragt das Video in Kaderblick ein – und das System weiß dann zu jedem eingetragenen Spielereignis (Tor in Minute 23, Rote Karte in Minute 67, Wechsel in Minute 74), an welcher Stelle im Video diese Szene zu finden ist. Per Klick springt der Player direkt dorthin. Keine lästige Suche im Video mehr!

---

## Videos zu einem Spiel hinzufügen

1. Das Spiel öffnen (Spielliste → Spiel anklicken)
2. Im Bereich „Videos" auf **„Video hinzufügen"** klicken
3. Folgende Daten eintragen:

| Feld | Was trage ich ein? |
|---|---|
| **Name** | Bezeichnung, z. B. „Komplettaufnahme Kamera 1" oder „Highlights" |
| **YouTube-Link oder Dateipfad** | Die URL des YouTube-Videos oder der Pfad zur hochgeladenen Datei |
| **Videotyp** | z. B. Gesamtaufnahme, Highlights, Trainingsvideo |
| **Kamera** | Falls mehrere Kameras vorhanden: welche Kamera ist das? |
| **Spielstart** | ⭐ Die wichtigste Angabe! – Die Sekunde im Video, bei der der Schiedsrichter angepfiffen hat |

4. Speichern

---

## Was ist der Spielstart?

Das ist der **Schlüssel zur automatischen Spielszenen-Navigation**. 

Beispiel: Ihr habt ein 90-Minuten-Video hochgeladen, aber das Video fängt 3 Minuten und 45 Sekunden vor dem Anpfiff an (Aufwärmen, Platzbeschreibung, etc.). Dann tragt ihr bei „Spielstart" die Zahl **225** ein (= 3 Minuten × 60 + 45 Sekunden).

Ab sofort kann das System jedes Spielereignis exakt umrechnen: Tor in Minute 23 → Startzeit 225 + 23×60 = Sekunde 1605 im Video. Klick – und ihr seid direkt beim Tor!

> 💡 **Tipp:** Spielt das Video kurz an bis zum Moment des Anpfiffs und schaut auf die Zeitanzeige des Players. Diese Sekundenzahl tragt ihr als „Spielstart" ein.

---

## Spielszenen anspringen – die Zeitleiste

Sobald Videos und Spielereignisse vorhanden sind, erscheint unter dem Video-Player die **Zeitleiste**: eine visuelle Darstellung aller Ereignisse im Spielverlauf.

- Klickt auf ein Tor in der Liste → Video springt direkt zur Szene
- Klickt auf eine Karte → Ihr seht die Situation sofort
- Klickt auf einen Wechsel → Hintergrundinfos und Szene gleichzeitig

Das macht Videoanalysen im Training extrem effizient – kein manuelles Suchen mehr.

---

## Mehrere Videos pro Spiel

Ein Spiel kann beliebig viele Videos haben – z. B. verschiedene Kameraperspektiven:

- **Kamera 1**: Totale vom Dach (Gesamtüberblick)
- **Kamera 2**: Nahaufnahme auf den Strafraum
- **Highlights**: Zusammenschnitt der wichtigsten Szenen

Alle Videos sind gleichzeitig aufrufbar und mit derselben Ereignisliste synchronisiert.

---

## Szenen markieren (Videosegmente)

Innerhalb eines Videos können beliebige Abschnitte als **Szenen** (Segmente) markiert werden:

- Großchancen
- Defensivfehler
- Standardsituationen
- Taktische Muster

Segmente haben einen Start- und Endpunkt und eine Beschreibung. Sie helfen dabei, im Training gezielt auf bestimmte Situationen einzugehen.

---

## Videos hochladen

Neben YouTube-Links können Videos auch direkt in die Plattform hochgeladen werden. Genutzte Videodateien liegen dann im Vereins-Speicher und sind für alle berechtigten Mitglieder zugänglich.

---

## Berechtigungen

- **Videos ansehen**: Alle Mitglieder des Teams, zu dem das Spiel gehört
- **Videos hochladen und verwalten**: Nur Administratoren

---

## Häufige Fragen

**Das Video springt an die falsche Stelle – was ist passiert?**
→ Der Spielstart ist falsch eingetragen. Spielt das Video bis zum Anpfiff vor, notiert die Sekunde und korrigiert den Spielstart-Wert beim Video.

**Kann ich auch Videos ohne YouTube-Link nutzen?**
→ Ja, Videos können auch direkt hochgeladen werden. Achtet dabei auf ausreichend Speicherplatz auf dem Server.

**Wie viele Kameras / Videos darf ein Spiel haben?**
→ Technisch unbegrenzt – aber für die Übersichtlichkeit empfehlen wir, nicht mehr als 3–4 Videos pro Spiel einzutragen.

---

## Grundprinzip

Jedes Spiel kann beliebig viele Videos haben. Ein Video enthält grundlegende Metadaten (Name, URL/Dateipfad, Länge) sowie einen **Spielstart-Offset** (`gameStart`): die Sekunde im Video, zu der die Partie angepfiffen wurde. Durch diesen Offset kann das System die Spielminute jedes Spielereignisses direkt in einen Zeitstempel im Video umrechnen.

---

## Video-Felder

| Feld | Beschreibung |
|---|---|
| Name | Bezeichnung des Videos (eindeutig pro Spiel) |
| URL | Link zum Video (z. B. YouTube-URL) |
| YouTube-ID | Wird automatisch aus der URL extrahiert |
| Dateipfad | Alternativer Pfad für lokal hochgeladene Dateien |
| Spielstart (`gameStart`) | Sekunde im Video, bei der das Spiel beginnt |
| Sortierung (`sort`) | Reihenfolge der Videos pro Spiel (eindeutig) |
| Länge | Gesamtlänge des Videos in Sekunden |
| Videotyp | Kategorie des Videos (z. B. Gesamtaufnahme, Highlight, Ausschnitt) |
| Kamera | Welche Kamera das Video aufgenommen hat |
| Erstellt von | Benutzer, der das Video angelegt hat |

---

## Videotypen

Videotypen kategorisieren Videos (z. B. "Vollaufnahme", "Torausschnitt", "Taktikvideos"). Sie werden zentral verwaltet (Route `/videoTypes`).

---

## Kameras

Kameras repräsentieren physische oder virtuelle Aufnahmepositionen. Sie ermöglichen die Zuordnung mehrerer Videos pro Spiel zu verschiedenen Kameraperspektiven. Verwaltung über Route `/cameras`.

---

## Video-Zeitleiste (Timeline)

Unterhalb des Videos werden alle erfassten Spielereignisse als klickbare Markierungen auf einer Zeitleiste angezeigt. Ein Klick auf z. B. „67. Minute: Tor – Müller" springt das Video sofort zur exakten Szene – kein manuelles Suchen mehr.

