import { useEffect, useRef, useState } from 'react';


interface UsePullToRefreshOptions {
  onRefresh: () => Promise<void>;
  threshold?: number;
  maxPullDistance?: number;
  isEnabled?: boolean;
}

export const usePullToRefresh = ({
  onRefresh,
  threshold = 80,
  maxPullDistance = 150,
  isEnabled = true,
}: UsePullToRefreshOptions) => {
  const [isPulling, setIsPulling] = useState(false);
  const [pullDistance, setPullDistance] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const startY = useRef(0);
  const currentY = useRef(0);
  const scrollable = useRef(true);

  useEffect(() => {
    if (!isEnabled) return;

    const isOverlayOrMenu = (el: EventTarget | null): boolean => {
      if (!(el instanceof Element)) return false;
      // Prüfe auf typische Overlay/Menu/Dialog/Dropdown-Klassen und Rollen
      const overlaySelectors = [
        '.MuiDrawer-root',
        '.MuiModal-root',
        '.MuiPopover-root',
        '.MuiMenu-root',
        '.dropdown-menu',
        '[role="dialog"]',
        '[role="menu"]',
        '[role="listbox"]',
        '[role="presentation"]',
      ];
      for (const selector of overlaySelectors) {
        if (el.closest(selector)) return true;
      }
      return false;
    };

    const handleTouchStart = (e: TouchEvent) => {
      // PullToRefresh nur erlauben, wenn das Touch nicht auf einem Overlay/Menu/Dialog/Dropdown startet
      if (isOverlayOrMenu(e.target)) {
        scrollable.current = false;
        return;
      }
      // Nur wenn am Seitenanfang (window.scrollY === 0)
      if (window.scrollY === 0) {
        startY.current = e.touches[0].clientY;
        scrollable.current = true;
      } else {
        scrollable.current = false;
      }
    };

    const handleTouchMove = (e: TouchEvent) => {
      if (!scrollable.current || isRefreshing) return;

      currentY.current = e.touches[0].clientY;
      const diff = currentY.current - startY.current;

      // Nur nach unten ziehen erlauben
      if (diff > 0 && window.scrollY === 0) {
        setIsPulling(true);
        // Easing-Effekt: Je weiter man zieht, desto langsamer wird die Bewegung
        const distance = Math.min(
          diff * 0.5, // Reduziere die Bewegung um 50%
          maxPullDistance
        );
        setPullDistance(distance);
        // Nur preventDefault, wenn wirklich gepullt wird
        if (distance > 0) {
          e.preventDefault();
        }
      }
    };

    const handleTouchEnd = async () => {
      if (!scrollable.current || isRefreshing) return;

      if (isPulling && pullDistance >= threshold) {
        setIsRefreshing(true);
        try {
          await onRefresh();
        } catch (error) {
          console.error('Refresh failed:', error);
        } finally {
          setIsRefreshing(false);
        }
      }

      setIsPulling(false);
      setPullDistance(0);
      scrollable.current = false;
    };

    document.addEventListener('touchstart', handleTouchStart, { passive: false });
    document.addEventListener('touchmove', handleTouchMove, { passive: false });
    document.addEventListener('touchend', handleTouchEnd);

    return () => {
      document.removeEventListener('touchstart', handleTouchStart);
      document.removeEventListener('touchmove', handleTouchMove);
      document.removeEventListener('touchend', handleTouchEnd);
    };
  }, [isPulling, pullDistance, threshold, maxPullDistance, isRefreshing, isEnabled, onRefresh]);

  return {
    isPulling: isPulling || isRefreshing,
    pullDistance,
    isRefreshing,
  };
};