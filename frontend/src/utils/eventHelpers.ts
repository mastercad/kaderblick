import { User } from '../types/event';

/**
 * Formats a user label for display purposes.
 */
export const getUserLabel = (user: User): string => {
  if (user.fullName) return user.fullName;
  if (user.firstName && user.lastName) return `${user.firstName} ${user.lastName}`;
  if (user.firstName) return user.firstName;
  if (user.lastName) return user.lastName;
  return `User #${user.id}`;
};

// Event type classification has moved to hooks/useEventTypeFlags.ts
// Use getEventTypeFlags() or useEventTypeFlags() hook instead.
