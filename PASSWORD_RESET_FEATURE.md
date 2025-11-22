# Passwort-Reset-Funktionalität

Diese Implementierung bietet eine vollständige "Passwort vergessen"-Funktionalität für die Fußballverein-Webapp.

## Features

### Backend (Symfony)

1. **PasswordResetToken Entity** (`api/src/Entity/PasswordResetToken.php`)
   - Speichert Tokens mit Ablaufzeit (24h Standard)
   - Relation zu User-Entity
   - `used`-Flag zur Einmalverwendung
   - `isValid()` und `isExpired()` Hilfsmethoden

2. **PasswordResetTokenRepository** (`api/src/Repository/PasswordResetTokenRepository.php`)
   - `findValidTokenByToken()` - Token validieren
   - `findValidTokenByUser()` - Aktives Token für User finden
   - `invalidateAllTokensForUser()` - Alte Tokens invalidieren
   - `deleteExpiredTokens()` - Cleanup-Methode

3. **PasswordResetService** (`api/src/Service/PasswordResetService.php`)
   - `requestPasswordReset($email)` - Reset-Link per E-Mail senden
   - `validateToken($token)` - Token prüfen
   - `resetPassword($token, $newPassword)` - Neues Passwort setzen
   - `cleanupExpiredTokens()` - Alte Tokens löschen
   - Sicherheitsmechanismus: Gibt immer Erfolg zurück, auch bei unbekannter E-Mail

4. **PasswordResetController** (`api/src/Controller/PasswordResetController.php`)
   - `POST /api/forgot-password` - Reset anfordern
   - `POST /api/reset-password` - Passwort zurücksetzen
   - `GET /api/validate-reset-token/{token}` - Token validieren

5. **E-Mail-Template** (`api/templates/emails/password_reset.html.twig`)
   - Professionelles HTML-E-Mail-Design
   - Responsive Layout
   - Sicherheitshinweise

6. **Migration** (`api/migrations/Version20251122000000.php`)
   - Erstellt `password_reset_tokens` Tabelle
   - Indizes für Performance
   - Foreign Key zu `user` Tabelle

### Frontend (React + TypeScript)

1. **ForgotPassword Seite** (`frontend/src/pages/ForgotPassword.tsx`)
   - Eingabefeld für E-Mail
   - Erfolgs- und Fehlermeldungen
   - Link zurück zum Login

2. **ResetPassword Seite** (`frontend/src/pages/ResetPassword.tsx`)
   - Token-Validierung beim Laden
   - Passwort + Bestätigung mit Sichtbarkeits-Toggle
   - Passwort-Stärke-Anforderungen
   - Automatische Weiterleitung zum Login nach Erfolg

3. **LoginForm Update** (`frontend/src/components/LoginForm.tsx`)
   - "Passwort vergessen?" Link immer sichtbar
   - Zusätzlicher Hinweis bei falschen Zugangsdaten

4. **Routing** (`frontend/src/App.tsx`)
   - `/forgot-password` - Passwort-Reset anfordern
   - `/reset-password/:token` - Neues Passwort setzen

## Konfiguration

### Environment-Variablen

In `api/.env` oder `api/.env.local` hinzufügen:

```env
# Frontend-URL für Reset-Links in E-Mails
FRONTEND_URL=http://localhost:5173
```

### Services-Konfiguration

Die `FRONTEND_URL` wurde bereits in `api/config/services.yaml` konfiguriert und als `$frontendUrl` Parameter gebunden.

## Installation

1. **Migration ausführen:**
   ```bash
   cd api
   bin/console doctrine:migrations:migrate
   ```

2. **Environment-Variable setzen:**
   ```bash
   echo "FRONTEND_URL=http://localhost:5173" >> .env.local
   ```

3. **Cache leeren (optional):**
   ```bash
   bin/console cache:clear
   ```

## Verwendung

### Für Endbenutzer

1. Im Login auf "Passwort vergessen?" klicken
2. E-Mail-Adresse eingeben
3. E-Mail mit Reset-Link erhalten (gültig für 24 Stunden)
4. Link öffnen und neues Passwort eingeben
5. Mit neuem Passwort einloggen

### Für Entwickler

#### Token-Bereinigung

Abgelaufene Tokens sollten regelmäßig gelöscht werden. Dafür kann ein Cronjob oder Command erstellt werden:

```php
// In einem Symfony Command
$this->passwordResetService->cleanupExpiredTokens();
```

#### Sicherheitsfeatures

- **Rate Limiting**: Sollte auf Controller-Ebene implementiert werden
- **CSRF-Schutz**: Bereits durch JWT-Tokens abgedeckt
- **E-Mail-Leak-Prevention**: Service gibt immer Erfolg zurück, auch bei unbekannter E-Mail
- **Token-Einmalverwendung**: Token wird nach Verwendung als `used` markiert
- **Zeitliche Begrenzung**: Tokens sind nur 24h gültig

## API-Endpunkte

### POST /api/forgot-password

Reset-Link anfordern.

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response (immer):**
```json
{
  "message": "Falls ein Konto mit dieser E-Mail-Adresse existiert, wurde ein Link zum Zurücksetzen des Passworts gesendet."
}
```

### GET /api/validate-reset-token/{token}

Token validieren (für Frontend-Validierung).

**Response (gültig):**
```json
{
  "valid": true
}
```

**Response (ungültig):**
```json
{
  "valid": false,
  "error": "Ungültiger oder abgelaufener Token"
}
```

### POST /api/reset-password

Passwort zurücksetzen.

**Request:**
```json
{
  "token": "abc123...",
  "password": "neuesPasswort123"
}
```

**Response (Erfolg):**
```json
{
  "message": "Passwort erfolgreich zurückgesetzt"
}
```

**Response (Fehler):**
```json
{
  "error": "Ungültiger oder abgelaufener Token"
}
```

## Testing

### Manuelles Testing

1. Registriere einen Test-User
2. Gehe zu `/forgot-password`
3. Gib die E-Mail des Test-Users ein
4. Prüfe die E-Mail (z.B. in MailHog wenn konfiguriert)
5. Öffne den Reset-Link
6. Setze ein neues Passwort
7. Logge dich mit dem neuen Passwort ein

### Unit Tests

TODO: Unit-Tests für PasswordResetService und Controller erstellen

## Troubleshooting

### E-Mails werden nicht gesendet

- Prüfe MAILER_DSN in `.env`
- Teste E-Mail-Konfiguration mit `bin/console debug:config framework mailer`
- Verwende MailHog für lokale Entwicklung

### Token ungültig

- Prüfe ob Migration ausgeführt wurde
- Prüfe Systemzeit (Token-Ablauf basiert auf DateTime)
- Prüfe ob Token bereits verwendet wurde

### Frontend kann Backend nicht erreichen

- Prüfe BACKEND_URL in `frontend/config.ts`
- Prüfe CORS-Konfiguration im Backend
- Prüfe Browser-Console für Netzwerkfehler

## Weitere Verbesserungen

Mögliche zukünftige Erweiterungen:

- [ ] Rate Limiting für Passwort-Reset-Anfragen
- [ ] Cronjob für automatische Token-Bereinigung
- [ ] E-Mail-Benachrichtigung bei erfolgreicher Passwort-Änderung
- [ ] Passwort-Stärke-Meter im Frontend
- [ ] Zwei-Faktor-Authentifizierung
- [ ] Passwort-History (keine Wiederverwendung alter Passwörter)
