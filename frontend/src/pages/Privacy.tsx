import React from 'react';
import { Container } from '@mui/material';

const Privacy: React.FC = () => (
  <Container maxWidth="md" sx={{ py: 4 }}>
    <div className="container py-4">
      <h1>Datenschutzerklärung</h1>
      <p className="lead">Der Schutz Ihrer persönlichen Daten ist uns ein besonderes Anliegen. Wir verarbeiten Ihre Daten daher ausschließlich auf Grundlage der gesetzlichen Bestimmungen (DSGVO, TKG 2003).</p>

      <h2>1. Verantwortlicher</h2>
      <p>Verantwortlich für die Datenverarbeitung auf dieser Webseite ist:<br />
        <strong>Andreas Kempe</strong> (Seitenbetreiber, privat)<br />
        E-Mail: <a href="mailto:andreas.kempe@kaderblick.de">andreas.kempe@kaderblick.de</a></p>

      <h2>2. Erhebung und Verarbeitung personenbezogener Daten</h2>
      <ul>
        <li>Beim Besuch der Webseite (Server-Logs, IP-Adresse, Browser, Uhrzeit)</li>
        <li>Bei der Registrierung und Nutzung (Name, E-Mail, Profilangaben, Teamzugehörigkeit)</li>
        <li>Bei der Anmeldung über Google SSO (Google-ID, Name, E-Mail, Profilbild)</li>
        <li>Feedback-Formular und Kontaktaufnahme</li>
      </ul>

      <h2>3. Google Single Sign-On (SSO)</h2>
      <p>Wir bieten die Anmeldung über Google SSO an. Dabei werden Sie zu Google weitergeleitet, um sich mit Ihrem Google-Konto anzumelden. Wir erhalten von Google folgende Daten:</p>
      <ul>
        <li>Ihren Namen</li>
        <li>E-Mail-Adresse</li>
        <li>Google-User-ID (sub)</li>
        <li>Profilbild (optional)</li>
      </ul>
      <p>Weitere Informationen zur Datenverarbeitung durch Google finden Sie in der <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google-Datenschutzerklärung</a>.</p>

      <h2>4. Zweck der Datenverarbeitung</h2>
      <ul>
        <li>Organisation und Kommunikation innerhalb von Sportteams (z.B. Fußballmannschaften, A-Jugend)</li>
        <li>Bereitstellung und Verbesserung der Webseite</li>
        <li>Authentifizierung und Zugangskontrolle</li>
        <li>Feedback-Auswertung</li>
        <li>Statistische Analysen</li>
      </ul>

      <h2>5. Weitergabe von Daten</h2>
      <p>Ihre Daten werden nicht an Dritte weitergegeben, außer es besteht eine gesetzliche Verpflichtung oder es ist für die Nutzung der Plattform technisch erforderlich.</p>

      <h2>6. Cookies & lokale Speicherung</h2>
      <p>Wir verwenden Cookies und Local Storage, um die Funktionalität der Seite zu gewährleisten (z.B. Login-Status, Theme-Auswahl). Sie können dies in Ihrem Browser einschränken oder deaktivieren.</p>

      <h2>7. Ihre Rechte</h2>
      <ul>
        <li>Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung</li>
        <li>Datenübertragbarkeit</li>
        <li>Widerruf einer Einwilligung</li>
        <li>Beschwerde bei der Datenschutzbehörde</li>
      </ul>
      <p>Bitte wenden Sie sich bei Fragen an: <a href="mailto:andreas.kempe@kaderblick.de">andreas.kempe@kaderblick.de</a></p>

      <h2>8. Änderungen</h2>
      <p>Wir behalten uns vor, diese Datenschutzerklärung zu aktualisieren. Die jeweils aktuelle Version finden Sie auf dieser Seite.</p>
    </div>
  </Container>
);

export default Privacy;
