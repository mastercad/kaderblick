/**
 * Tournament Match Generation Utilities
 */

import type {
  GeneratedMatch,
  GenerateTournamentMatchesOptions,
  TournamentType,
} from '../types/tournament';

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
  
  // Shuffle teams and distribute to groups evenly
  const shuffledTeams = shuffleArray(teams);
  const groups: { [key: string]: typeof teams } = {};
  
  // Verteile Teams gleichmäßig auf Gruppen (z.B. 7 Teams, 2 Gruppen = 4+3 oder 3+4)
  const baseTeamsPerGroup = Math.floor(totalTeams / numberOfGroups);
  const remainingTeams = totalTeams % numberOfGroups;
  
  let currentIndex = 0;
  for (let i = 0; i < numberOfGroups; i++) {
    const groupKey = String.fromCharCode(65 + i); // A, B, C, ...
    // Erste 'remainingTeams' Gruppen bekommen ein Team mehr
    const teamsInThisGroup = baseTeamsPerGroup + (i < remainingTeams ? 1 : 0);
    groups[groupKey] = shuffledTeams.slice(currentIndex, currentIndex + teamsInThisGroup);
    currentIndex += teamsInThisGroup;
  }
  
  let slotCounter = 1;
  let currentTime = new Date(startTime);
  
  // Phase 1: Group stage - Algorithmus abhängig vom Turniertyp
  const groupWinners: string[] = [];
  const groupSecondPlace: string[] = [];
  
  if (tournamentType === 'indoor_hall') {
    // Hallenturnier: Generiere Spiele pro Gruppe, dann interleave für optimale Pausen
    
    const groupMatches: { [key: string]: any[] } = {};
    
    // Generiere Spiele für jede Gruppe separat
    for (const [groupKey, groupTeams] of Object.entries(groups)) {
      if (groupTeams.length < 2) continue;
      
      const n = groupTeams.length;
      const allPairings: { home: number; away: number }[] = [];
      
      for (let i = 0; i < n; i++) {
        for (let j = i + 1; j < n; j++) {
          allPairings.push({ home: i, away: j });
        }
      }
      
      // Greedy für diese Gruppe
      const lastPlayTime: { [teamIndex: number]: number } = {};
      for (let i = 0; i < n; i++) {
        lastPlayTime[i] = -1000;
      }
      
      const used = new Set<number>();
      const groupMatchList: any[] = [];
      let matchIndex = 0;
      
      while (used.size < allPairings.length) {
        let bestPairing: number = -1;
        let bestScore: number = -Infinity;
        
        for (let i = 0; i < allPairings.length; i++) {
          if (used.has(i)) continue;
          
          const { home, away } = allPairings[i];
          const score = (matchIndex - lastPlayTime[home]) + (matchIndex - lastPlayTime[away]);
          
          if (score > bestScore) {
            bestScore = score;
            bestPairing = i;
          }
        }
        
        if (bestPairing === -1) break;
        
        const { home, away } = allPairings[bestPairing];
        used.add(bestPairing);
        lastPlayTime[home] = matchIndex;
        lastPlayTime[away] = matchIndex;
        
        groupMatchList.push({
          homeTeam: groupTeams[home],
          awayTeam: groupTeams[away],
          group: groupKey,
        });
        
        matchIndex++;
      }
      
      groupMatches[groupKey] = groupMatchList;
      groupWinners.push(`${groupKey}1`);
      groupSecondPlace.push(`${groupKey}2`);
    }
    
    // Für jede Gruppe: Berger-Tabellen-Algorithmus, aber alle Spiele nacheinander
    for (const [groupKey, groupTeams] of Object.entries(groups)) {
      const n = groupTeams.length;
      if (n < 2) continue;
      const teamList = [...groupTeams];
      const hasDummy = n % 2 !== 0;
      if (hasDummy) {
        teamList.push({ value: 'dummy', label: 'DUMMY' });
      }
      const totalTeams = teamList.length;
      const numRounds = totalTeams - 1;
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
            group: groupKey,
            scheduledAt: currentTime.toISOString(),
            stage: `Gruppenspiel (Gr. ${groupKey})`,
          });
          currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
        }
      }
      // Store group winners/second place
      groupWinners.push(`${groupKey}1`);
      groupSecondPlace.push(`${groupKey}2`);
    }
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
          
          matches.push({
            round: round + 1,
            slot: slotCounter++,
            homeTeamId: teamList[home].value,
            awayTeamId: teamList[away].value,
            homeTeamName: teamList[home].label,
            awayTeamName: teamList[away].label,
            group: groupKey,
            scheduledAt: currentTime.toISOString(),
            stage: `Gruppenspiel (Gr. ${groupKey})`,
          });
          
          currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
        }
      }
      
      // Store group winners/second place
      groupWinners.push(`${groupKey}1`);
      groupSecondPlace.push(`${groupKey}2`);
    }
  }
  
  // --- NEU: Immer K.O.-Runden nach Gruppenphase, so viele wie möglich ---
  // Alle Gruppenersten und -zweiten (sofern vorhanden) kommen weiter
  const koTeilnehmer = [...groupWinners, ...groupSecondPlace].filter(Boolean);
  // K.O.-Raster: Viertelfinale ab 8 Teams, Halbfinale ab 4 Teams, Finale ab 2 Teams
  let koTeams = koTeilnehmer;
  let koStage = '';
  let roundNr = 2;
  while (koTeams.length >= 2) {
    if (koTeams.length >= 8) {
      koStage = 'Viertelfinale';
    } else if (koTeams.length >= 4) {
      koStage = 'Halbfinale';
    } else if (koTeams.length === 2) {
      koStage = 'Finale';
    }
    const nextRoundTeams: string[] = [];
    for (let i = 0; i < koTeams.length; i += 2) {
      const home = koTeams[i];
      const away = koTeams[i + 1] || 'tbd';
      matches.push({
        round: roundNr,
        slot: slotCounter++,
        homeTeamId: 'tbd',
        awayTeamId: 'tbd',
        homeTeamName: home,
        awayTeamName: away,
        scheduledAt: currentTime.toISOString(),
        stage: koStage,
      });
      currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
      nextRoundTeams.push(`Sieger ${koStage} ${Math.floor(i/2)+1}`);
    }
    // Spiel um Platz 3, wenn Halbfinale gespielt wurde
    if (koStage === 'Halbfinale' && koTeams.length === 4) {
      matches.push({
        round: roundNr + 1,
        slot: slotCounter++,
        homeTeamId: 'tbd',
        awayTeamId: 'tbd',
        homeTeamName: 'Verlierer HF1',
        awayTeamName: 'Verlierer HF2',
        scheduledAt: currentTime.toISOString(),
        stage: 'Spiel um Platz 3',
      });
      currentTime = new Date(currentTime.getTime() + (roundDuration + breakTime) * 60000);
    }
    koTeams = nextRoundTeams;
    roundNr++;
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
