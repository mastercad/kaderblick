import { TournamentGameMode, TournamentType, GeneratedMatch } from "./types";

export type GenerateTournamentMatchesOptions = {
  teams: { value: string; label: string }[];
  gameMode: TournamentGameMode;
  tournamentType: TournamentType;
  roundDuration: number;
  breakTime: number;
  startTime: string;
  numberOfGroups?: number;
  currentMatches?: GeneratedMatch[];
};

/**
 * Shuffle array using Fisher-Yates algorithm
 */
function shuffleArray<T>(array: T[]): T[] {
  const result = [...array];
  for (let i = result.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [result[i], result[j]] = [result[j], result[i]];
  }
  return result;
}

/**
 * Generate round-robin matches optimized for indoor hall tournaments (one field, sequential play)
 * Maximizes rest time between a team's matches
 */
function generateRoundRobinMatchesIndoorHall(
  teams: { value: string; label: string }[],
  startTime: string,
  roundDuration: number,
  breakTime: number
): GeneratedMatch[] {
  const n = teams.length;
  
  if (n < 2) {
    return [];
  }
  
  // Klassischer Round-Robin: Berger-Tabellen-Algorithmus für 1 Feld (alle Spiele nacheinander)
  // ggf. Dummy-Team für ungerade Anzahl
  const teamList = [...teams];
  const hasDummy = n % 2 !== 0;
  if (hasDummy) {
    teamList.push({ value: 'dummy', label: 'DUMMY' });
  }
  const totalTeams = teamList.length;
  const numRounds = totalTeams - 1;
  const matches: GeneratedMatch[] = [];
  let slotCounter = 1;
  let currentTime = new Date(startTime);
  for (let round = 0; round < numRounds; round++) {
    for (let match = 0; match < totalTeams / 2; match++) {
      let home: number;
      let away: number;
      if (match === 0) {
        home = 0;
        away = round + 1;
      } else {
        home = ((round + totalTeams - match) % (totalTeams - 1)) + 1;
        away = ((round + match) % (totalTeams - 1)) + 1;
      }
      if (teamList[home].value === 'dummy' || teamList[away].value === 'dummy') {
        continue;
      }
      matches.push({
        round: round + 1,
        slot: slotCounter++,
        homeTeamId: teamList[home].value,
        awayTeamId: teamList[away].value,
        homeTeamName: teamList[home].label,
        awayTeamName: teamList[away].label,
        scheduledAt: currentTime.toISOString(),
        stage: `Runde ${round + 1}`,
      });
      currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    }
  }
  return matches;
}

/**
 * Generate round-robin matches for normal tournaments (multiple fields, parallel play)
 * Uses Berger tables algorithm for optimal round-robin scheduling
 */
function generateRoundRobinMatchesNormal(
  teams: { value: string; label: string }[],
  startTime: string,
  roundDuration: number,
  breakTime: number
): GeneratedMatch[] {
  const n = teams.length;
  
  if (n < 2) {
    return [];
  }
  
  // Kopiere und füge ggf. Dummy-Team hinzu für ungerade Anzahl
  const teamList = [...teams];
  const hasDummy = n % 2 !== 0;
  if (hasDummy) {
    teamList.push({ value: 'dummy', label: 'DUMMY' });
  }
  
  const totalTeams = teamList.length;
  const numRounds = totalTeams - 1;
  const matches: GeneratedMatch[] = [];
  
  let slotCounter = 1;
  let currentTime = new Date(startTime);
  
  // Berger-Tabellen-Algorithmus
  // Team 0 bleibt fix, andere rotieren im Uhrzeigersinn
  for (let round = 0; round < numRounds; round++) {
    // In jeder Runde spielen totalTeams/2 Spiele parallel
    for (let match = 0; match < totalTeams / 2; match++) {
      let home: number;
      let away: number;
      
      if (match === 0) {
        home = 0;
        away = round + 1;
      } else {
        home = ((round + totalTeams - match) % (totalTeams - 1)) + 1;
        away = ((round + match) % (totalTeams - 1)) + 1;
      }
      
      // Überspringe Spiele mit Dummy-Team
      if (teamList[home].value === 'dummy' || teamList[away].value === 'dummy') {
        continue;
      }
      
      matches.push({
        round: round + 1,
        slot: slotCounter++,
        homeTeamId: teamList[home].value,
        awayTeamId: teamList[away].value,
        homeTeamName: teamList[home].label,
        awayTeamName: teamList[away].label,
        scheduledAt: currentTime.toISOString(),
        stage: `Runde ${round + 1}`,
      });
      
      // Nächstes Spiel startet nach Rundenzeit + Pause
      currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    }
  }
  
  return matches;
}

/**
 * Wrapper function for round-robin match generation
 */
function generateRoundRobinMatches(
  teams: { value: string; label: string }[],
  startTime: string,
  roundDuration: number,
  breakTime: number,
  tournamentType: TournamentType
): GeneratedMatch[] {
  if (tournamentType === 'indoor_hall') {
    return generateRoundRobinMatchesIndoorHall(teams, startTime, roundDuration, breakTime);
  } else {
    return generateRoundRobinMatchesNormal(teams, startTime, roundDuration, breakTime);
  }
}

/**
 * Generate groups with finals matches
 * Groups play internally, then winners play finals
 */
function generateGroupsWithFinalsMatches(
  teams: { value: string; label: string }[],
  startTime: string,
  roundDuration: number,
  breakTime: number,
  tournamentType: TournamentType,
  numberOfGroups: number = 2
): GeneratedMatch[] {
  const matches: GeneratedMatch[] = [];
  const totalTeams = teams.length;

  if (totalTeams < 4) {
    // Fallback to round-robin if not enough teams
    return generateRoundRobinMatches(teams, startTime, roundDuration, breakTime, tournamentType);
  }

  // Gruppen initialisieren
  const groups: Record<string, { value: string; label: string }[]> = {};
  for (let i = 0; i < numberOfGroups; i++) {
    groups[String.fromCharCode(65 + i)] = [];
  }
  // Teams mischen und verteilen
  const shuffledTeams = shuffleArray(teams);
  shuffledTeams.forEach((team, idx) => {
    const groupKey = String.fromCharCode(65 + (idx % numberOfGroups));
    groups[groupKey].push(team);
  });

  // Platzhalter für spätere KO-Runden
  const groupWinners: string[] = [];
  const groupSecondPlace: string[] = [];

  // Globale Rundenzählung und Slot/Time-Initialisierung
  let globalRound = 1;
  let slotCounter = 1;
  let currentTime = new Date(startTime);
  if (tournamentType === 'indoor_hall') {
    // Hallenturnier: Generiere Spiele pro Gruppe, dann interleave für optimale Pausen
    // 1. Berger-Tabellen-Algorithmus für jede Gruppe, dann Matches mischen
    const groupMatchLists: Array<Array<any>> = [];
    for (const [groupKey, groupTeams] of Object.entries(groups)) {
      if (groupTeams.length < 2) continue;
      const teamList = [...groupTeams];
      const hasDummy = teamList.length % 2 !== 0;
      if (hasDummy) {
        teamList.push({ value: 'dummy', label: 'DUMMY' });
      }
      const totalTeams = teamList.length;
      const numRounds = totalTeams - 1;
      const groupMatches: any[] = [];
      for (let round = 0; round < numRounds; round++) {
        for (let match = 0; match < totalTeams / 2; match++) {
          let home: number;
          let away: number;
          if (match === 0) {
            home = 0;
            away = round + 1;
          } else {
            home = ((round + totalTeams - match) % (totalTeams - 1)) + 1;
            away = ((round + match) % (totalTeams - 1)) + 1;
          }
          if (teamList[home].value === 'dummy' || teamList[away].value === 'dummy') {
            continue;
          }
          groupMatches.push({
            round: globalRound, // wird später gesetzt
            slot: 0, // wird später gesetzt
            homeTeamId: teamList[home].value,
            awayTeamId: teamList[away].value,
            homeTeamName: teamList[home].label,
            awayTeamName: teamList[away].label,
            group: groupKey,
            scheduledAt: '', // wird später gesetzt
            stage: `Gruppenspiel (Gr. ${groupKey})`,
          });
        }
      }
      groupMatchLists.push(groupMatches);
      groupWinners.push(`${groupKey}1`);
      groupSecondPlace.push(`${groupKey}2`);
    }
    // Interleave: abwechselnd aus jeder Gruppe ein Spiel nehmen
    let groupIndexes = new Array(groupMatchLists.length).fill(0);
    let groupMatchesLeft = groupMatchLists.reduce((sum, arr) => sum + arr.length, 0);
    while (groupMatchesLeft > 0) {
      for (let g = 0; g < groupMatchLists.length; g++) {
        const idx = groupIndexes[g];
        if (idx < groupMatchLists[g].length) {
          const m = groupMatchLists[g][idx];
          m.round = globalRound++;
          m.slot = slotCounter++;
          m.scheduledAt = currentTime.toISOString();
          // eslint-disable-next-line no-console
          console.log('[DEBUG] Gruppenspiel:', m);
          matches.push(m);
          currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
          groupIndexes[g]++;
          groupMatchesLeft--;
        }
      }
    }
// entfernt, da jetzt alles über das Interleaving oben läuft
  } else {
    // Normales Turnier: Berger-Algorithmus pro Gruppe
    for (const [groupKey, groupTeams] of Object.entries(groups)) {
      if (groupTeams.length < 2) continue;
      const teamList = [...groupTeams];
      const hasDummy = groupTeams.length % 2 !== 0;
      if (hasDummy) {
        teamList.push({ value: 'dummy', label: 'DUMMY' });
      }
      const totalTeamsInGroup = teamList.length;
      const numRounds = totalTeamsInGroup - 1;
      for (let round = 0; round < numRounds; round++) {
        for (let match = 0; match < totalTeamsInGroup / 2; match++) {
          let home: number;
          let away: number;
          if (match === 0) {
            home = 0;
            away = round + 1;
          } else {
            home = ((round + totalTeamsInGroup - match) % (totalTeamsInGroup - 1)) + 1;
            away = ((round + match) % (totalTeamsInGroup - 1)) + 1;
          }
          if (teamList[home].value === 'dummy' || teamList[away].value === 'dummy') {
            continue;
          }
          const debugMatch = {
            round: globalRound++,
            slot: slotCounter++,
            homeTeamId: teamList[home].value,
            awayTeamId: teamList[away].value,
            homeTeamName: teamList[home].label,
            awayTeamName: teamList[away].label,
            group: groupKey,
            scheduledAt: currentTime.toISOString(),
            stage: `Gruppenspiel (Gr. ${groupKey})`,
          };

          // eslint-disable-next-line no-console
          console.log('[DEBUG] Gruppenspiel:', debugMatch);
          
          matches.push(debugMatch);
          currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
        }
      }
      // Store group winners/second place
      groupWinners.push(`${groupKey}1`);
      groupSecondPlace.push(`${groupKey}2`);
    }
  }
  
  // Phase 2: K.O.-Runden automatisch generieren (Viertelfinale, Halbfinale, Finale, Platz 3)
  // Annahme: groupWinners und groupSecondPlace enthalten die Platzierungen als Strings (z.B. 'A1', 'B2', ...)
  // 1. Alle Gruppenersten und ggf. -zweiten kommen ins Viertel-/Halbfinale
  const koTeams: string[] = [];
  if (numberOfGroups === 2) {
    // Klassisch: 2 Gruppen, Halbfinale, Finale, Platz 3
    // Halbfinale: A1 vs B2, B1 vs A2
    // Halbfinale
    const debugMatchHF1 = {
      round: globalRound++,
      slot: slotCounter++,
      homeTeamId: 'tbd',
      awayTeamId: 'tbd',
      homeTeamName: groupWinners[0],
      awayTeamName: groupSecondPlace[1],
      scheduledAt: currentTime.toISOString(),
      stage: 'Halbfinale',
    };
    // eslint-disable-next-line no-console
    console.log('[DEBUG] KO-Spiel:', debugMatchHF1);
    matches.push(debugMatchHF1);
    currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    const debugMatchHF2 = {
      round: globalRound++,
      slot: slotCounter++,
      homeTeamId: 'tbd',
      awayTeamId: 'tbd',
      homeTeamName: groupWinners[1],
      awayTeamName: groupSecondPlace[0],
      scheduledAt: currentTime.toISOString(),
      stage: 'Halbfinale',

    };
    // eslint-disable-next-line no-console
    console.log('[DEBUG] KO-Spiel:', debugMatchHF2);

    matches.push(debugMatchHF2);
    currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    // Spiel um Platz 3
    const debugMatchP3 = {
      round: globalRound++,
      slot: slotCounter++,
      homeTeamId: 'tbd',
      awayTeamId: 'tbd',
      homeTeamName: 'Verlierer HF1',
      awayTeamName: 'Verlierer HF2',
      scheduledAt: currentTime.toISOString(),
      stage: 'Spiel um Platz 3',
    };

    // eslint-disable-next-line no-console
    console.log('[DEBUG] KO-Spiel:', debugMatchP3);

    matches.push(debugMatchP3);
    currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    // Finale
    const debugMatchF = {
      round: globalRound++,
      slot: slotCounter++,
      homeTeamId: 'tbd',
      awayTeamId: 'tbd',
      homeTeamName: 'Sieger HF1',
      awayTeamName: 'Sieger HF2',
      scheduledAt: currentTime.toISOString(),
      stage: 'Finale',
    };

    // eslint-disable-next-line no-console
    console.log('[DEBUG] KO-Spiel:', debugMatchF);

    matches.push(debugMatchF);
  } else if (numberOfGroups === 4) {
    // Beispiel: 4 Gruppen, Viertelfinale, Halbfinale, Finale, Platz 3
    // Viertelfinale: A1 vs B2, B1 vs A2, C1 vs D2, D1 vs C2
    // TODO: Implement 4-group KO logic here. Placeholder for Viertelfinale etc.
    // Remove invalid code below (was causing syntax error)
  }
  
  return matches;
}

/**
 * Main function to generate tournament matches based on configuration
 */
export function generateTournamentMatches(
  options: GenerateTournamentMatchesOptions
): GeneratedMatch[] {
  const { teams, gameMode, tournamentType, roundDuration, breakTime, startTime, numberOfGroups } = options;
  
  if (!teams || teams.length < 2) {
    return [];
  }
  
  switch (gameMode) {
    case 'round_robin':
      return generateRoundRobinMatches(teams, startTime, roundDuration, breakTime, tournamentType);
    
    case 'groups_with_finals':
      return generateGroupsWithFinalsMatches(
        teams,
        startTime,
        roundDuration,
        breakTime,
        tournamentType,
        numberOfGroups || 2
      );
    
    default:
      return [];
  }
}

/**
 * Calculate total tournament duration
 */
export function calculateTournamentDuration(
  numberOfMatches: number,
  roundDuration: number,
  breakTime: number
): number {
  if (numberOfMatches === 0) return 0;
  return numberOfMatches * roundDuration + (numberOfMatches - 1) * breakTime;
}

/**
 * Calculate estimated end time
 */
export function calculateTournamentEndTime(
  startTime: string,
  numberOfMatches: number,
  roundDuration: number,
  breakTime: number
): string {
  const duration = calculateTournamentDuration(numberOfMatches, roundDuration, breakTime);
  const start = new Date(startTime);
  const end = new Date(start.getTime() + duration * 60000);
  return end.toISOString();
}
