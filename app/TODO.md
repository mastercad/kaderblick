# Build

## generate public keys for lexik:jwt
```shell
php bin/console lexik:jwt:generate-keypair
```

# Troubleshooting
for problems with apache and auth header, add in vhost file:

```
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

# Deployment
- datenbank musste zentralisiert und ausgelagert werden, da auf einem volume keine 2 datenbanken laufen können, das gab konflikte was dafür sorgte dass eine der beiden db dauernd neu startete
- will ich api und vue neu bauen oder starten und in der docker-compose steht auch die db, wird die automatisch mit gestartet, weil sie als abhängigkeit da drin stand, getrennt ist es sauberer


# Aufgaben
- Schema inhalt der entities über ein command generieren und in einem ordner ablegen um performance zu sparen, die daten müssen nicht jedes mal neu ausgelesen werden. man könnte das ganze eventuell in einem post install / post update composer command aufrufen. hierbei ist aber zu bedenken, dass das durchaus zu fehlern führen kann, wenn keine datenbank vorhanden ist, aber composer install ausgeführt werden soll. man kann es aber auch ein script schreiben, was sich um das setup der app kümmert. dort drin könnte man das command ebenfalls aufrufen. es ist nicht so tragisch, aber bei jeder neuen migration der db sollten auch diese schema dateien dann neu generiert werden. aktuell passiert das on-the-fly
✅ - in der view für die liste von locations kann man die location editieren und löschen. hierbei muss ein modal angezeigt werden um den user zu bestätigen, dass er wirklich löschen will. 
✅ - aktuell kann ich entities nicht speichern, die OneToMany relations haben, zum beispiel wird im Frontend bei Location auch die OneToMany relation Games angezeigt, was keinen sinn macht. da muss eine lösung gefunden werden, vielleich erstmal komplett entfernen. vielleicht nehme ich relations auch ganz raus, zumindest aus dem "befülle das schema mit daten" und lade sie  spezifisch nach dem jeweiligen entity beim auswählen des dropdown nach.
- ApiController::getSchema, ApiController::index, ApiController::List und ApiController:show benötigen spezielle Repositories um für den aktuellen User, den aktuellen Zeitraum und das aktuelle Team Spieler zu filtern. Auch Tore soll es nur für sinnvolle Zusammenhänge anzeigen. Gerade Tore werden sonst hundertausende mit geladen, immer wieder.

# Besonderheiten
- für das auflösen der relationen müssen diese entities eine __toString() zur verfügung stellen, so zeigt es in den edit dialogen im dropdown den entsprechenden string an

# Datenbank zurücksetzen
- php bin/console doctrine:schema:drop --full-database --force
- rm -r migrations/Version*
- php bin/console make:migrations
- php bin/console doctrine:schema:create
- php bin/console doctrine:migrate:migrations

# Altersklassen
Altersklasse	Bezeichnung	Alter am 1. Januar	Geburtsjahr (für Saison 2024/25)
U6	Bambini	bis 5 Jahre	2019 oder später
U7	G-Junioren	6 Jahre	2018
U8	F2-Junioren	7 Jahre	2017
U9	F1-Junioren	8 Jahre	2016
U10	E2-Junioren	9 Jahre	2015
U11	E1-Junioren	10 Jahre	2014
U12	D2-Junioren	11 Jahre	2013
U13	D1-Junioren	12 Jahre	2012
U14	C2-Junioren	13 Jahre	2011
U15	C1-Junioren	14 Jahre	2010
U16	B2-Junioren	15 Jahre	2009
U17	B1-Junioren	16 Jahre	2008
U18/U19	A-Junioren	17–18 Jahre	2007 / 2006

Altersklassen für Datenbank-Modellierung im Fußball
ID	Code (Kurzname)	Deutsch	Englisch	Alter (Jahre)	Beschreibung	Stichtag
1	F-Junioren	F-Junioren	U7 / Under 7	6-7	Kleinste Jugendklasse	01.01.
2	E-Junioren	E-Junioren	U9 / Under 9	8-9		01.01.
3	D-Junioren	D-Junioren	U11 / Under 11	10-11		01.01.
4	C-Junioren	C-Junioren	U13 / Under 13	12-13		01.01.
5	B-Junioren	B-Junioren	U15 / Under 15	14-15		01.01.
6	A-Junioren	A-Junioren	U17 / Under 17	16-17	Älteste klassische Jugendklasse	01.01.
7	U19	U19	U19 / Under 19	18-19	Jugendlicher Übergang zu Erwachsenen	01.01.
8	U21	U21	U21 / Under 21	20-21	Nachwuchsteam, oft Reservemannschaft	01.01.
9	U23	U23	U23 / Under 23	22-23	Erweiterter Nachwuchsbereich	01.01.
10	Senioren	Senioren / Erwachsene	Seniors / Adults	18+	Erwachsenenligen ohne Altersbegrenzung	n/a
11	Ü32	Alte Herren Ü32	Veterans Over 32	32+	Freizeit- und Hobbyliga	n/a
12	Ü40	Alte Herren Ü40	Veterans Over 40	40+	Freizeit- und Hobbyliga	n/a
13	Ü50	Alte Herren Ü50	Veterans Over 50	50+	Freizeit- und Hobbyliga	n/a

- auf ORM zu setzen war keine so geile idee, ich muss hier noch umbauen auf repositories und arrays für das laden der listen
- es muss ein paginator implementiert werden, dann kann ORM eventuell bestehen bleiben
- es muss noch eine einschränkung implementiert werden, bzw. gecheckt, warum doctrine alle einträge ständig zurück gibt, nicht nur die zusammenhängenden, wie z.b. alle trainerlizensen statt nur die des aktuellen trainers
- es muss bei den repos wahrscheinlich eine logik implementiert werden, um default immer nur die aktuellen und relavanten daten zu ziehen, nicht immer alles
- auch api/schema darf nur eine wirklich aktuelle und relevante liste laden, wo nur für den (z.b.) aktuellen spieler alle relevanten daten als "auswahl" geladen werden. 

# Troubleshooting
Problem:
{"error":"The data is either not an string, an empty string, or null; you should pass a string that can be parsed with the passed format or a valid DateTime string."}

Lösung:
ich habe startDate auf nullable gesetzt und der read gruppe des aufrufers zugewiesen, damit ging es