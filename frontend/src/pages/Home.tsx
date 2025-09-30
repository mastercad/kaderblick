import { useRef, useEffect, useState } from 'react';
import { Box, Typography, useTheme } from '@mui/material';
import calendarImage from '../../public/images/landing_page/calendar.png?url'
import surceyImage from '../../public/images/landing_page/surveys.png?url'
import coachImage from '../../public/images/landing_page/coach.png?url'
import createEventImage from '../../public/images/landing_page/create_event.png?url'
import eventZusageImage from '../../public/images/landing_page/event_zusage.png?url'
import gamesImage from '../../public/images/landing_page/game_overview.png?url'
import reportsImage from '../../public/images/landing_page/reports.png?url'
import locationNavigationLinkImage from '../../public/images/landing_page/location_navigationlink.png?url'
import newsImage from '../../public/images/landing_page/news.png?url'

import '../styles/home-parallax.css';

const sections = [
  {
    image: calendarImage,
    text: `Mit Kaderblick kannst du sämtliche Vereins-Events wie Spiele, Trainings, Treffen oder Feiern zentral planen und verwalten. Jeder Termin wird automatisch in einen übersichtlichen, modernen Kalender eingetragen, der für alle Mitglieder zugänglich ist. Trainer und Organisatoren können Events mit wenigen Klicks anlegen, bearbeiten oder absagen. Die Teilnehmer sehen auf einen Blick, was ansteht, und können sich direkt über die Plattform anmelden oder abmelden. Push-Benachrichtigungen und Erinnerungen sorgen dafür, dass niemand einen wichtigen Termin verpasst. Die Kalenderansicht ist intuitiv, filterbar nach Kategorien oder Zeiträumen und auch auf Mobilgeräten optimal nutzbar.`
  },
  {
    image: eventZusageImage,
    text: `Für jedes Event können Mitglieder mit nur einem Klick Zu- oder Absagen abgeben. Die Rückmeldungen sind für Trainer und Teammitglieder sofort sichtbar, sodass die Planung von Aufstellungen, Fahrgemeinschaften oder Verpflegung einfach und transparent wird. Automatische Erinnerungen helfen, die Beteiligung zu erhöhen. Die Plattform zeigt übersichtlich, wer zugesagt, abgesagt oder sich noch nicht entschieden hat – auch für wiederkehrende Termine oder Serien.`
  },
  {
    image: surceyImage,
    text: `Mit dem flexiblen Umfragesystem kannst du beliebige Feedbacks, Abstimmungen oder Meinungsbilder einholen. Erstelle eigene Fragen, Mehrfach- oder Einfachauswahl, Freitextfelder oder Bewertungsskalen. Die Umfragen lassen sich gezielt an Teams, Gruppen oder den gesamten Verein richten. Die Ergebnisse werden grafisch ausgewertet und können für spätere Analysen exportiert werden. So erhältst du wertvolle Einblicke und kannst die Vereinsarbeit gezielt verbessern.`
  },
  {
    image: gamesImage,
    text: `Alle Spiele werden in einer zentralen Übersicht dargestellt. Für jedes Spiel können detaillierte Ereignisse wie Tore, Karten, Auswechslungen oder besondere Momente mit Zeitstempel erfasst werden. Zusätzlich lassen sich Videos zu den Spielen hochladen und mit den jeweiligen Spielereignissen verknüpfen. So kann man später gezielt zu jedem Ereignis im Video springen und die Szene analysieren – ein wertvolles Tool für Trainer, Spieler und Fans.`
  },
  {
    image: coachImage,
    text: `Trainer erhalten einen eigenen Bereich, in dem sie Spielaufstellungen planen, Taktiken hinterlegen und Notizen zu Spielern oder Gegnern erfassen können. Die Aufstellungen können per Drag & Drop erstellt und für jedes Spiel individuell angepasst werden. Taktische Varianten, Wechseloptionen und Formationen sind übersichtlich darstellbar. So wird die Spielvorbereitung digital, effizient und nachvollziehbar.`
  },
  {
    image: createEventImage,
    text: `Die Plattform ermöglicht das Anlegen und Verwalten beliebig vieler Vereine, Teams, Spieler und Trainer. Jeder Eintrag kann mit umfangreichen Informationen, Bildern und Kontaktdaten versehen werden. Die Zuordnung zu Teams, Altersklassen oder Funktionen ist flexibel. Historien, Statistiken und individuelle Profile sorgen für Transparenz und eine optimale Organisation des Vereinslebens.`
  },
  {
    image: locationNavigationLinkImage,
    text: `Jeder Spiel- oder Trainingsort ist mit Adresse, Karte und Link zur Navigation hinterlegt. Über einen Klick kann die Route direkt auf dem Mobilgerät in der bevorzugten Navigations-App geöffnet werden. So finden alle Teilnehmer und Eltern schnell und unkompliziert zum Austragungsort. Auch Fahrgemeinschaften lassen sich so besser organisieren.`
  },
  {
    image: reportsImage,
    text: `Umfangreiche Reports geben einen Überblick über Spielereignisse, Trainingsbeteiligung, Umfrageergebnisse und viele weitere Kennzahlen. Die Auswertungen sind individuell konfigurierbar, können exportiert und für Besprechungen oder die Vereinsentwicklung genutzt werden. So behältst du stets den Überblick über die Entwicklung deines Vereins und kannst gezielt Maßnahmen ableiten.`
  },
  {
    image: newsImage,
    text: `Das interne Messaging-System ermöglicht schnelle, sichere Kommunikation zwischen allen Mitgliedern, Teams oder Funktionären. News können für die gesamte Plattform, einzelne Vereine oder Teams veröffentlicht werden. Push-Benachrichtigungen und Lesebestätigungen sorgen dafür, dass wichtige Informationen alle erreichen. So bleibt dein Verein immer bestens informiert und vernetzt.`
  },
];

export default function Home() {
  const theme = useTheme();
  const [activeSection, setActiveSection] = useState(0);
  const [modalOpen, setModalOpen] = useState(false);

  // Navigation: z.B. mit Buttons oder Pfeilen
  const handlePrev = () => setActiveSection(s => Math.max(0, s - 1));
  const handleNext = () => setActiveSection(s => Math.min(sections.length - 1, s + 1));

  return (
    <div style={{ width: '100vw', minHeight: '100vh', background: '#fff', overflowX: 'hidden' }}>
      <Box sx={{ py: { xs: 4, md: 8 }, px: { xs: 2, md: 4 }, width: '100%' }}>
        <Typography variant="h2" align="center" gutterBottom fontWeight={800} color={theme.palette.primary.main}>
          Kaderblick – Die Plattform für deinen Verein
        </Typography>
        <Typography variant="h5" align="center" color="text.secondary" paragraph sx={{ mb: 6 }}>
          Organisiere, plane und erlebe deinen Verein auf eine völlig neue, moderne Art. Entdecke alle Funktionen im Überblick!
        </Typography>
      </Box>
      <Box
        sx={{
          display: 'flex',
          flexDirection: { xs: 'column', md: 'row' },
          alignItems: 'stretch',
          justifyContent: 'center',
          gap: { xs: 4, md: 8 },
          px: { xs: 2, md: 8 },
          py: { xs: 2, md: 6 },
          width: '100%',
          maxWidth: 1400,
          margin: '0 auto',
        }}
      >
        <Box
          sx={{
            flex: '0 0 420px',
            maxWidth: { xs: '100%', md: 480 },
            minWidth: 220,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: '#f7f7f7',
            borderRadius: '1.2rem',
            boxShadow: '0 4px 24px rgba(0,0,0,0.10)',
            p: { xs: 2, md: 4 },
            height: { xs: 220, md: 340 },
            cursor: 'pointer',
          }}
          onClick={() => setModalOpen(true)}
          title="Bild vergrößern"
        >
          <img
            src={sections[activeSection].image}
            alt="Vorschau"
            style={{
              width: '100%',
              height: '100%',
              objectFit: 'contain',
              borderRadius: '1rem',
              background: '#fff',
              pointerEvents: 'none',
            }}
          />
        </Box>
        <Box
          sx={{
            flex: 1,
            display: 'flex',
            alignItems: 'center',
            background: '#f7f7f7',
            borderRadius: '1.2rem',
            boxShadow: '0 4px 24px rgba(0,0,0,0.10)',
            p: { xs: 2, md: 4 },
            minHeight: { xs: 220, md: 340 },
          }}
        >
          <Typography
            variant="h5"
            align="left"
            sx={{
              color: '#222',
              fontWeight: 400,
              fontSize: { xs: '1rem', md: '1.15rem' },
              lineHeight: 1.5,
              px: { xs: 1, md: 2 },
            }}
          >
            {sections[activeSection].text}
          </Typography>
        </Box>
      </Box>
      <Box sx={{ display: 'flex', justifyContent: 'center', gap: 2, mt: 2 }}>
        <button onClick={handlePrev} disabled={activeSection === 0} style={{ padding: '0.7em 1.2em', fontSize: '1.1em', borderRadius: '0.7em', border: 'none', background: '#eee', cursor: 'pointer' }}>Zurück</button>
        <button onClick={handleNext} disabled={activeSection === sections.length - 1} style={{ padding: '0.7em 1.2em', fontSize: '1.1em', borderRadius: '0.7em', border: 'none', background: '#eee', cursor: 'pointer' }}>Weiter</button>
      </Box>
      {/* Modal für Großansicht */}
      {modalOpen && (
        <div
          style={{
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100vw',
            height: '100vh',
            background: 'rgba(0,0,0,0.7)',
            zIndex: 9999,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
          }}
        >
          <div style={{ position: 'relative', display: 'inline-block' }}>
            <img
              src={sections[activeSection].image}
              alt="Großansicht"
              style={{
                maxWidth: '90vw',
                maxHeight: '90vh',
                borderRadius: '1.5rem',
                boxShadow: '0 8px 32px rgba(0,0,0,0.25)',
                background: '#fff',
                display: 'block',
              }}
              onClick={() => setModalOpen(false)}
            />
            <button
              onClick={() => setModalOpen(false)}
              style={{
                position: 'absolute',
                top: '-18px',
                right: '-18px',
                zIndex: 10000,
                background: 'rgba(255,255,255,0.96)',
                border: 'none',
                borderRadius: '50%',
                width: 36,
                height: 36,
                cursor: 'pointer',
                boxShadow: '0 1px 6px rgba(0,0,0,0.12)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                transition: 'background 0.2s',
                padding: 0,
              }}
              aria-label="Schließen"
              onMouseOver={e => (e.currentTarget.style.background = 'rgba(230,230,230,1)')}
              onMouseOut={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.96)')}
            >
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 5L13 13M13 5L5 13" stroke="#222" strokeWidth="2.2" strokeLinecap="round" />
              </svg>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
