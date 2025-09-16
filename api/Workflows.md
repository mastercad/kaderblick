Spieler anlegen:

- Spieler anlegen	Basisdaten: Name, Geburtsdatum, Nationalität etc.
- Spieler einem Club zuordnen	PlayerClubAssignment mit Start/Ende-Datum
- Spieler einem Team zuordnen	PlayerTeamAssignment mit Rolle, Trikotnummer, Zeitraum, ggf. Altersgruppe als Team-Eigenschaft
- Spieler im Spiel verwenden	GameEvents mit Referenz auf Spieler und Team (für diesen Zeitpunkt gültiges Assignment)
- Altersgruppe ableiten	Dynamisch über Team-Zugehörigkeit oder über Geburtstags-Datum (nicht direkt im Player gespeichert)

