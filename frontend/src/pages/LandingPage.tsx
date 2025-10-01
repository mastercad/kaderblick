import React from 'react';
import LandingGallery from '../components/LandingGallery';
import calendarImage from '../../public/images/landing_page/calendar.png?url'
import surceyImage from '../../public/images/landing_page/surveys.png?url'
import coachImage from '../../public/images/landing_page/coach.png?url'
import createEventImage from '../../public/images/landing_page/create_event.png?url'
import eventZusageImage from '../../public/images/landing_page/event_zusage.png?url'
import gamesImage from '../../public/images/landing_page/game_overview.png?url'
import reportsImage from '../../public/images/landing_page/reports.png?url'
import locationNavigationLinkImage from '../../public/images/landing_page/location_navigationlink.png?url'
import newsImage from '../../public/images/landing_page/news.png?url'

const sections = [
  {
    image: calendarImage,
    additionalImages: [
      calendarImage,
      eventZusageImage,
      surceyImage,
      gamesImage,
      coachImage
    ],
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

export default function LandingPage() {
  return (
    <div style={{ padding: 32, maxWidth: 1200, margin: '0 auto' }}>
      <LandingGallery sections={sections} />
    </div>
  );
}
