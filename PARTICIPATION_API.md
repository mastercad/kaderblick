# Participation API Documentation

## Endpunkte

### GET /api/participation/event/{id}
Abrufen aller Teilnehmer für ein Event

**Response:**
```json
{
    "event": {
        "id": 123,
        "title": "Heimspiel gegen FC Beispiel",
        "type": "Spiel",
        "is_game": true
    },
    "participations": [
        {
            "user_id": 456,
            "user_name": "Max Mustermann",
            "status": {
                "id": 1,
                "name": "Zusage",
                "code": "confirmed",
                "color": "#22c55e",
                "icon": "check-circle"
            },
            "note": "Freue mich auf das Spiel!",
            "is_team_player": true
        }
    ],
    "available_statuses": [
        {
            "id": 1,
            "name": "Zusage",
            "code": "confirmed",
            "color": "#22c55e",
            "icon": "check-circle",
            "sort_order": 1
        }
    ]
}
```

### POST /api/participation/event/{id}/respond
Teilnahmestatus für ein Event setzen

**Request Body:**
```json
{
    "status_id": 1,
    "note": "Optional: Zusätzliche Notiz"
}
```

**Response:**
```json
{
    "message": "Teilnahmestatus erfolgreich aktualisiert",
    "participation": {
        "status": {
            "id": 1,
            "name": "Zusage",
            "code": "confirmed",
            "color": "#22c55e",
            "icon": "check-circle"
        },
        "note": "Freue mich auf das Spiel!"
    }
}
```

### GET /api/participation/statuses
Alle verfügbaren Teilnahmestatus abrufen

**Response:**
```json
{
    "statuses": [
        {
            "id": 1,
            "name": "Zusage",
            "code": "confirmed",
            "color": "#22c55e",
            "icon": "check-circle",
            "sort_order": 1
        },
        {
            "id": 2,
            "name": "Absage",
            "code": "declined",
            "color": "#ef4444",
            "icon": "x-circle",
            "sort_order": 2
        }
    ]
}
```

## Features

- **Benutzer-basiert**: Teilnahme erfolgt über User, nicht Player
- **Flexible Status**: Verschiedene Status mit Namen, Codes, Farben und Icons
- **Team-Spieler Erkennung**: Automatische Erkennung ob User Spieler in beteiligten Teams ist
- **Zuschauer Support**: Auch Nicht-Spieler können bei Spielen teilnehmen
- **Notizen**: Optional können Notizen zur Teilnahme hinzugefügt werden

## Logik

### Für Spiele:
- Spieler der beteiligten Teams können teilnehmen
- Zuschauer/Fans können ebenfalls teilnehmen
- `is_team_player` zeigt an, ob User Spieler in einem der beteiligten Teams ist

### Für andere Events:
- Alle Vereinsmitglieder können teilnehmen
- `is_team_player` ist immer false

### User-Player Beziehung:
- Über UserRelation mit RelationType (category='player', identifier='self' oder 'player')
- Ermöglicht flexible Beziehungen (Spieler selbst, Eltern, Trainer, etc.)
