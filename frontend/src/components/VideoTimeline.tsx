import React, { useRef, useState, useEffect } from 'react';
import { Box, Tooltip, IconButton } from '@mui/material';
import { SkipPrevious as SkipPreviousIcon } from '@mui/icons-material';
import { getGameEventIconByCode } from '../constants/gameEventIcons';

interface TimelineEvent {
  id: number;
  offsetSeconds?: number;
  timestamp: number;
  label: string;
  icon: string;
  color: string;
  description?: string;
}

interface VideoTimelineProps {
  duration: number;
  events: TimelineEvent[];
  onSeek: (seconds: number) => void;
  onEventMove?: (eventId: number, newSeconds: number) => void;
  onSeekToGameStart?: () => void; // Spezieller Handler für "Go to game start" Button
  gameStart?: number; // Offset in Sekunden - Zeit im Video, wo das Spiel beginnt
}

/**
 * Formatiert Sekunden zu MM:SS Format (mit optionalem Vorzeichen)
 */
function formatSeconds(seconds: number): string {
  const isNegative = seconds < 0;
  const absSecs = Math.abs(seconds);
  const mins = Math.floor(absSecs / 60);
  const secs = Math.round(absSecs % 60);
  const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  return isNegative ? `-${formatted}` : formatted;
}

const VideoTimeline: React.FC<VideoTimelineProps> = ({ duration, events, onSeek, onEventMove, onSeekToGameStart, gameStart = 0 }) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const dragTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const [dragState, setDragState] = useState<null | {
    eventId: number;
    origSeconds: number;
    row: number;
    offsetX: number;
    mouseX: number;
  }>(null);

  // Globales Mousemove/Up-Handling für Dragging
  useEffect(() => {
    if (!dragState) return;
    const handleMove = (e: MouseEvent) => {
      setDragState(ds => {
        if (!ds) return null;
        const mouseX = e.clientX;
        // Video-Position während Dragging setzen
        const box = containerRef.current?.getBoundingClientRect();
        if (box && typeof onSeek === 'function') {
          const x = mouseX - ds.offsetX - box.left;
          let newSeconds = (x / timelineWidth) * duration;
          newSeconds = Math.max(0, Math.min(duration, newSeconds));
          onSeek(newSeconds);
        }
        return { ...ds, mouseX };
      });
    };
    const handleUp = (e: MouseEvent) => {
      if (!dragState) return;
      const box = containerRef.current?.getBoundingClientRect();
      if (!box) {
        setDragState(null);
        return;
      }
      // Offset berücksichtigen
      const x = e.clientX - dragState.offsetX - box.left;
      let videoPosition = (x / timelineWidth) * duration;
      videoPosition = Math.max(0, Math.min(duration, videoPosition));
      // Video-Position zurück in Event-Zeit konvertieren (minus gameStart)
      const eventTime = videoPosition - gameStart;
      onEventMove?.(dragState.eventId, eventTime);
      setDragState(null);
    };
    window.addEventListener('mousemove', handleMove);
    window.addEventListener('mouseup', handleUp);
    return () => {
      window.removeEventListener('mousemove', handleMove);
      window.removeEventListener('mouseup', handleUp);
    };
  }, [dragState, duration, onEventMove]);

  // Cleanup timeout on unmount
  useEffect(() => {
    return () => {
      if (dragTimeoutRef.current) {
        clearTimeout(dragTimeoutRef.current);
      }
    };
  }, []);
  const markerRows: { event: TimelineEvent; left: string; px: number; row: number }[] = [];
  const rowHeight = 28; // px, Abstand zwischen Reihen
  const markerWidth = 24; // px, angenommene Markerbreite inkl. Padding
  const timelineWidth = 800; // px, angenommene Breite der Timeline (kann ggf. dynamisch gemacht werden)
  events.forEach(event => {
    // Event-Zeit + gameStart = Video-Position
    const videoPosition = event.timestamp + gameStart;
    // Berechne Pixelposition relativ zur Timeline
    const px = Math.round((videoPosition / duration) * timelineWidth);
    let row = 0;
    while (markerRows.some(m => Math.abs(m.px - px) < markerWidth && m.row === row)) {
      row++;
    }
    const left = `${(videoPosition / duration) * 100}%`;
    markerRows.push({ event, left, px, row });
  });

  const maxRow = markerRows.reduce((max, m) => Math.max(max, m.row), 0);

  // Mouse-Event-Handler für Click oder Drag
  const handleMouseDown = (e: React.MouseEvent, eventId: number, origSeconds: number, row: number) => {
    // Clear any existing timeout
    if (dragTimeoutRef.current) {
      clearTimeout(dragTimeoutRef.current);
    }

    // Bei kurzer Zeit (Click) -> Video springen
    // Bei längerer Zeit (Hold) -> Dragging starten
    const startTime = Date.now();
    let isDragging = false;

    const startDrag = () => {
      isDragging = true;
      if (typeof window !== 'undefined') {
        // eslint-disable-next-line no-console
        console.log('[VideoTimeline] Drag started', { eventId, origSeconds, row });
      }
      if (!onEventMove) {
        if (typeof window !== 'undefined') {
          // eslint-disable-next-line no-console
          console.log('[VideoTimeline] onEventMove fehlt!');
        }
        return;
      }
      const box = containerRef.current?.getBoundingClientRect();
      if (!box) {
        if (typeof window !== 'undefined') {
          // eslint-disable-next-line no-console
          console.log('[VideoTimeline] containerRef leer!');
        }
        return;
      }
      // origSeconds ist bereits die Video-Position (timestamp + gameStart)
      const markerX = box.left + (origSeconds / duration) * timelineWidth;
      setDragState({
        eventId,
        origSeconds,
        row,
        offsetX: e.clientX - markerX,
        mouseX: e.clientX,
      });
    };

    // Set timeout: nach 300ms wird Drag aktiviert
    dragTimeoutRef.current = setTimeout(() => {
      startDrag();
    }, 300);

    // Handle mouseup to check if it was a click or drag
    const handleMouseUp = () => {
      if (dragTimeoutRef.current) {
        clearTimeout(dragTimeoutRef.current);
        dragTimeoutRef.current = null;
      }

      // Wenn noch nicht dragging, war es ein Click -> seek
      if (!isDragging) {
        if (typeof window !== 'undefined') {
          // eslint-disable-next-line no-console
          console.log('[VideoTimeline] Click detected, seeking to', origSeconds);
        }
        // origSeconds ist bereits die Video-Position (timestamp + gameStart)
        onSeek(origSeconds);
      }

      window.removeEventListener('mouseup', handleMouseUp);
    };

    window.addEventListener('mouseup', handleMouseUp, { once: true });
    e.preventDefault();
    e.stopPropagation();
  };

  return (
    <Box
      ref={containerRef}
      sx={{ position: 'relative', width: '100%', mt: 1, mb: 2 }}
    >
      {/* Time labels and info */}
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1, px: 1, fontSize: '0.875rem', color: '#666' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <strong>Start:</strong> {formatSeconds(gameStart)}
                {gameStart > 0 && (
                    <Tooltip title="Zum Spielbeginn springen">
                        <IconButton 
                            size="small" 
                            onClick={() => onSeekToGameStart ? onSeekToGameStart() : onSeek(gameStart)}
                            sx={{ p: 0.5, color: '#1976d2', '&:hover': { bgcolor: '#f5f5f5' } }}
                        >
                            <SkipPreviousIcon fontSize="small" />
                        </IconButton>
                    </Tooltip>
                )}
            </Box>
        <Box>
            <strong>Länge:</strong> {formatSeconds(duration - gameStart)} (exkl. Offset)
        </Box>
    </Box>

    {/* Timeline container with calculated height */}
    <Box
        sx={{ position: 'relative', width: '100%', height: 32 + maxRow * rowHeight }}
    >
    {/* Timeline Bar */}
    <Box sx={{ position: 'absolute', top: 16 + maxRow * rowHeight / 2, left: 0, right: 0, height: 4, bgcolor: '#e0e0e0', borderRadius: 2, transform: 'translateY(-50%)' }} />
      {/* Event Markers gestaffelt */}
      {markerRows.map(({ event, left, row }) => {
        // Wenn Marker gerade gezogen wird, live-Position berechnen
        if (dragState && dragState.eventId === event.id) return null; // Overlay-Icon wird separat gerendert
        return (
          <Tooltip key={event.id} title={event.label + (event.description ? `: ${event.description}` : '')} arrow>
            <Box
              sx={{
                position: 'absolute',
                top: 16 + row * rowHeight,
                left,
                transform: 'translate(-50%, -50%)',
                cursor: onEventMove ? 'grab' : 'pointer',
                zIndex: 2,
                color: event.color,
                bgcolor: 'white',
                borderRadius: '50%',
                boxShadow: 1,
                p: 0.2,
                fontSize: 18,
                transition: 'box-shadow 0.2s',
                '&:hover': { boxShadow: 4, bgcolor: '#f5f5f5' },
                userSelect: 'none',
              }}
              onMouseDown={(e) => handleMouseDown(e, event.id, event.timestamp + gameStart, row)}
            >
              {getGameEventIconByCode(event.icon) || '•'}
            </Box>
          </Tooltip>
        );
      })}
      {/* Drag-Overlay-Icon */}
      {dragState && containerRef.current && (
        <Box
          sx={{
            position: 'absolute',
            top: 16 + dragState.row * rowHeight,
            left: `${((dragState.mouseX - dragState.offsetX - containerRef.current.getBoundingClientRect().left) / timelineWidth) * 100}%`,
            transform: 'translate(-50%, -50%)',
            pointerEvents: 'none',
            zIndex: 10,
            color: events.find(ev => ev.id === dragState.eventId)?.color || 'primary.main',
            bgcolor: 'white',
            border: '2px solid #1976d2',
            borderRadius: '50%',
            boxShadow: 4,
            p: 0.2,
            fontSize: 18,
            userSelect: 'none',
            opacity: 1,
          }}
        >
          {getGameEventIconByCode(
            events.find(ev => ev.id === dragState.eventId)?.icon || ''
          ) || '•'}
        </Box>
      )}
      </Box>
    </Box>
  );
};

export default VideoTimeline;
