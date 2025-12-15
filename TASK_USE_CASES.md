# Generelles
- ein task hat task_assignments
- das task selbst bleibt immer unique und dient als editierbasis
- zu tasks gibt es rotation_users in welchen die benutzer hinterlegt sind, welche zu dem task verteilt werden dürfen
- 

## tasks für spiele
- werden tasks zu einem spiel angelegt, werden sie über die spiele verteilt, dabei hält jedes task entsprechend benutzer zugewiesen
- gibt es schon calender events für spiele zu dem task und es wird ein neues spiel angelegt oder eins gelöscht, werden die tasks neu verteilt
- wenn tasks neu verteilt werden und zu einem calender event gibt es bereits eine fahrgemeinschaft oder zusagen/absagen, werden die fahrgemeinschaften deaktiviert, bleiben aber erhalten. die beteiligten werden per notification informiert und können in der notification ihre zusage/absage ändern bzw. die fahrgemeinschaft bestätigen oder löschen, wird eine fahrgemeinschaft gelöscht und es bestehen bereits interessenten, werden diese informiert, wird der status einer zusage/absage geändert, wird der ersteller des events informiert
- weden calender events neu verteilt, weil sich ein task geändert hat, werden die beteiligten benuzer per notification informiert

# Use Cases im Detail

# User ist nicht verlinkt und hat keinerlei einträge im kalender
## presets
- Es existiert kein Task
- es existieren keine Spiele
- es existieren keine Kalendereinträge
- benutzer ist in keinem Team und keinem Verein
- Benutzer hat keine Zuordnung 
- Benutzer hat die Role User

## auswirkungen
- Benutzer kann nur für sich selbst Tasks anlegen
- Spiele sind eventuell gar nicht auswählbar
- Teams sind nicht auswählbar bzw. bringen kein ergebnis bei der anfrage
- Clubs sind nicht auswählbar bzw. bringen kein ergebnis bei der anfrage 

# User ist verlinkt mit einem Spieler
## presets
- spieler ist in einem team
- benutzer ist spieler selbst
- benutzer hat die Role User
- es existieren keine Kalendereinträge
- es existieren keine Tasks

## auswirkungen
- benutzer kann für sich selbst tasks anlegen
- Teams sind auswählbar, er sieht nur die mit ihm verbundenen teams
- Clubs sind auswählbar, er sieht nur die mit ihm verbundenen clubs

# Weitere Ideen:
- offset für kalendereinträge als aufgabe um bestimmte aufgaben als "vor dem termin" anzulegen, z.b. bei spielen einen tag vorher.
- 