// Diese Komponente ist nicht mehr nötig, da die Authentifizierung 
// jetzt direkt über das Backend-Template (google_auth_popup.html.twig) abgewickelt wird.
// 
// Das neue System funktioniert folgendermaßen:
// 1. GoogleLoginButton öffnet Popup mit /connect/google
// 2. Google OAuth Flow läuft ab
// 3. Backend rendert google_auth_popup.html.twig mit Auth-Daten
// 4. Template sendet postMessage an Parent Window
// 5. AuthModal empfängt die Nachricht und verarbeitet die Authentifizierung
//
// Diese Datei kann gelöscht werden.

export default function GoogleAuthPopup() {
    return (
        <div>
            <h3>Diese Komponente ist veraltet</h3>
            <p>Die Google-Authentifizierung wird jetzt über das Backend-Template abgewickelt.</p>
        </div>
    );
}