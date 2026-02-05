import { User } from '../types/event';

/**
 * Formats a user label for display purposes
 */
export const getUserLabel = (user: User): string => {
  if (user.fullName) return user.fullName;
  if (user.firstName && user.lastName) return `${user.firstName} ${user.lastName}`;
  if (user.firstName) return user.firstName;
  if (user.lastName) return user.lastName;
  return `User #${user.id}`;
};

/**
 * Determines if an event type represents a game/match event
 */
export const isGameEventType = (
  eventType: string | undefined,
  eventTypes: { value: string; label: string }[]
): boolean => {
  const selectedEventType = eventTypes.find(et => et.value === eventType);
  return (
    selectedEventType?.label?.toLowerCase().includes('spiel') ||
    selectedEventType?.label?.toLowerCase() === 'spiel' ||
    false
  );
};

/**
 * Determines if a game type represents a tournament game
 */
export const isTournamentGameType = (
  gameType: string | undefined,
  gameTypes: { value: string; label: string }[]
): boolean => {
  const selectedGameType = gameTypes.find(gt => gt.value === gameType);
  return (
    selectedGameType?.label?.toLowerCase().includes('turnier') ||
    selectedGameType?.label?.toLowerCase() === 'turnierspiel' ||
    false
  );
};

/**
 * Determines if a game type represents a tournament game
 */
export const isTournamentEventType = (
  eventType: string | undefined,
  eventTypes: { value: string; label: string }[],
): boolean => {
  const selectedEventType = eventTypes.find(et => et.value === eventType);
  return (
    selectedEventType?.label?.toLowerCase().includes('turnier') ||
    selectedEventType?.label?.toLowerCase() === 'turnierspiel' ||
    false
  );
};

/**
 * Determines if an event type represents a task event
 */
export const isTaskEventType = (
  eventType: string | undefined,
  eventTypes: { value: string; label: string }[]
): boolean => {
  const selectedEventType = eventTypes.find(et => et.value === eventType);
  return (
    selectedEventType?.label?.toLowerCase().includes('aufgabe') ||
    selectedEventType?.label?.toLowerCase() === 'aufgabe' ||
    false
  );
};

/**
 * Calculates wizard step indices based on event configuration
 */
export const calculateWizardSteps = (
  isGameEvent: boolean,
  isTaskEvent: boolean,
  isTournamentGame: boolean
): { descriptionStep: number; summaryStep: number } => {
  const descriptionStep = !isGameEvent && !isTaskEvent ? 3 : isGameEvent && isTournamentGame ? 3 : 2;
  const summaryStep = descriptionStep + 1;
  return { descriptionStep, summaryStep };
};
