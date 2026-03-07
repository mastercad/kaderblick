// ─── Zone color based on y-position on the half-pitch ─────────────────────────
// Portrait half-pitch: y=0 TOP = centre line (attackers), y=100 BOTTOM = own goal
export function getZoneColor(y: number): string {
  if (y > 78) return '#f59e0b'; // TW zone  – amber
  if (y > 55) return '#3b82f6'; // Defence  – blue
  if (y > 30) return '#22c55e'; // Midfield – green
  return       '#ef4444';       // Attack   – red
}

/** Truncate a player name to fit in a token label */
export function truncateName(name: string, maxLen = 7): string {
  if (name.length <= maxLen) return name;
  return name.slice(0, maxLen - 1) + '…';
}

/** Find a free grid position on the portrait pitch that doesn't overlap existing players */
export function findFreePosition(
  occupied: Array<{ x: number; y: number }>,
): { x: number; y: number } {
  // Portrait: x = lateral (10–88), y = depth (12–82)
  for (let row = 0; row < 5; row++) {
    for (let col = 0; col < 4; col++) {
      const x = 14 + col * 24; // spread across field width
      const y = 14 + row * 16; // spread down field depth
      if (!occupied.some(p => Math.abs(p.x - x) < 8 && Math.abs(p.y - y) < 8)) {
        return { x, y };
      }
    }
  }
  return { x: 15 + Math.random() * 70, y: 10 + Math.random() * 75 };
}

/** Extract x/y from a client pointer event relative to an element */
export function getRelativePosition(
  clientX: number,
  clientY: number,
  el: HTMLElement,
): { x: number; y: number } {
  const rect = el.getBoundingClientRect();
  return {
    x: Math.max(2, Math.min(98, ((clientX - rect.left) / rect.width) * 100)),
    y: Math.max(2, Math.min(98, ((clientY - rect.top) / rect.height) * 100)),
  };
}
