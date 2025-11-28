/**
 * Gibt die URL für den Avatar-Frame zurück, wenn ein Titel vorhanden ist.
 * @param titleObj Ein Objekt mit hasTitle (boolean) und avatarFrame (string)
 * @returns Die URL zum Frame (avatarFrame + '.png') oder undefined
 */
export function getAvatarFrameUrl(titleObj?: { hasTitle?: boolean; avatarFrame?: string }): string | undefined {
  if (!titleObj?.hasTitle || !titleObj.avatarFrame) return undefined;
  return "/images/avatar/"+titleObj.avatarFrame + '.svg';
}
