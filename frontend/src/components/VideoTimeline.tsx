import React, { useRef, useState, useEffect } from 'react';
import { Box, Tooltip, IconButton } from '@mui/material';
import { SkipPrevious as SkipPreviousIcon, ContentCut as ContentCutIcon } from '@mui/icons-material';
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

interface CutMarker {
  id: string;
  startSeconds: number;
  endSeconds: number;
  title?: string;
}

interface VideoTimelineProps {
  duration: number;
  events: TimelineEvent[];
  onSeek: (seconds: number) => void;
  onEventMove?: (eventId: number, newSeconds: number) => void;
  onSeekToGameStart?: () => void;
  gameStart?: number;
  // Schnittmarken Props
  cutMarkers?: CutMarker[];
  onCutMarkerAdd?: (startSeconds: number, endSeconds: number) => void;
  onCutMarkerMove?: (id: string, startSeconds: number, endSeconds: number) => void;
  cutModeActive?: boolean;
  onToggleCutMode?: () => void;
  showCutMarkers?: boolean; // Neue Prop um Schnittmarken-Elemente zu steuern
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

const VideoTimeline: React.FC<VideoTimelineProps> = ({ 
  duration, 
  events, 
  onSeek, 
  onEventMove, 
  onSeekToGameStart, 
  gameStart = 0,
  cutMarkers = [],
  onCutMarkerAdd,
  onCutMarkerMove,
  cutModeActive = false,
  onToggleCutMode,
  showCutMarkers = false
}) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const dragTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const [dragState, setDragState] = useState<null | {
    eventId: number;
    origSeconds: number;
    row: number;
    offsetX: number;
    mouseX: number;
  }>(null);
  
  // State für Schnittmarken-Modus
  const [cutDragState, setCutDragState] = useState<null | {
    type: 'start' | 'end' | 'range';
    markerId: string;
    origSeconds: number;
    origEndSeconds?: number; // für Range-Drag
    offsetX: number;
    currentMouseX: number;
    initialMouseX?: number; // für Range-Drag: Start-Position der Maus
  }>(null);
  const [pendingCut, setPendingCut] = useState<null | { startSeconds: number }>(null);
  const [isMobile, setIsMobile] = useState(false);

  // Mobile Detection
  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth < 768);
    };
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  // Dynamische Timeline-Breite basierend auf Container
  const getTimelineWidth = () => {
    return containerRef.current?.getBoundingClientRect().width || 800;
  };

  // Globales Mousemove/Up-Handling für Event-Dragging
  useEffect(() => {
    if (!dragState) return;
    const handleMove = (e: MouseEvent | TouchEvent) => {
      const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
      setDragState(ds => {
        if (!ds) return null;
        const mouseX = clientX;
        // Video-Position während Dragging setzen
        const box = containerRef.current?.getBoundingClientRect();
        if (box && typeof onSeek === 'function') {
        const x = mouseX - ds.offsetX - box.left;
        let newSeconds = (x / getTimelineWidth()) * duration;
          newSeconds = Math.max(0, Math.min(duration, newSeconds));
          onSeek(newSeconds);
        }
        return { ...ds, mouseX };
      });
    };
    const handleUp = (e: MouseEvent | TouchEvent) => {
      if (!dragState) return;
      const box = containerRef.current?.getBoundingClientRect();
      if (!box) {
        setDragState(null);
        return;
      }
      const clientX = 'changedTouches' in e ? e.changedTouches[0].clientX : e.clientX;
      // Offset berücksichtigen
      const x = clientX - dragState.offsetX - box.left;
      let videoPosition = (x / getTimelineWidth()) * duration;
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
  }, [dragState, duration, onEventMove, onSeek, gameStart]);

  // Globales Mousemove/Up-Handling für Cut-Marker-Dragging
  useEffect(() => {
    if (!cutDragState) return;
    
    const getTimelineWidth = () => containerRef.current?.getBoundingClientRect().width || 800;
    
    const handleMove = (e: MouseEvent | TouchEvent) => {
      const box = containerRef.current?.getBoundingClientRect();
      if (!box) return;
      
      const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
      
      if (cutDragState.type === 'range') {
        // Bei Range-Drag: Delta berechnen und Video entsprechend bewegen
        const currentX = clientX - box.left;
        const currentSeconds = (currentX / getTimelineWidth()) * duration;
        onSeek(Math.max(0, Math.min(duration, currentSeconds)));
      } else {
        // Bei Start/End-Marker: normale Berechnung mit Offset
        const x = clientX - cutDragState.offsetX - box.left;
        let newSeconds = (x / getTimelineWidth()) * duration;
        newSeconds = Math.max(0, Math.min(duration, newSeconds));
        onSeek(newSeconds);
      }
      
      // Update mouse position for overlay
      setCutDragState(prev => prev ? { ...prev, currentMouseX: clientX } : null);
    };
    
    const handleUp = (e: MouseEvent | TouchEvent) => {
      const clientX = 'changedTouches' in e ? e.changedTouches[0].clientX : e.clientX;
      if (!cutDragState) return;
      const box = containerRef.current?.getBoundingClientRect();
      if (!box) {
        setCutDragState(null);
        return;
      }
      
      const marker = cutMarkers.find(m => m.id === cutDragState.markerId);
      if (!marker || !onCutMarkerMove) {
        setCutDragState(null);
        return;
      }
      
      if (cutDragState.type === 'start' || cutDragState.type === 'end') {
        const x = clientX - cutDragState.offsetX - box.left;
        let newSeconds = (x / getTimelineWidth()) * duration;
        newSeconds = Math.max(0, Math.min(duration, newSeconds));
        
        if (cutDragState.type === 'start') {
          onCutMarkerMove(marker.id, newSeconds, marker.endSeconds);
        } else {
          onCutMarkerMove(marker.id, marker.startSeconds, newSeconds);
        }
      } else if (cutDragState.type === 'range' && cutDragState.origEndSeconds !== undefined && cutDragState.initialMouseX !== undefined) {
        // Bei Range-Drag: Delta zwischen ursprünglicher und aktueller Mausposition
        const deltaX = clientX - cutDragState.initialMouseX;
        const deltaSeconds = (deltaX / getTimelineWidth()) * duration;
        
        const rangeDuration = cutDragState.origEndSeconds - cutDragState.origSeconds;
        let newStart = cutDragState.origSeconds + deltaSeconds;
        let newEnd = cutDragState.origEndSeconds + deltaSeconds;
        
        // Grenzen prüfen und anpassen
        if (newStart < 0) {
          newStart = 0;
          newEnd = rangeDuration;
        } else if (newEnd > duration) {
          newEnd = duration;
          newStart = duration - rangeDuration;
        }
        
        onCutMarkerMove(marker.id, newStart, newEnd);
      }
      
      setCutDragState(null);
    };
    
    window.addEventListener('mousemove', handleMove);
    window.addEventListener('mouseup', handleUp);
    window.addEventListener('touchmove', handleMove as any, { passive: false });
    window.addEventListener('touchend', handleUp as any);
    return () => {
      window.removeEventListener('mousemove', handleMove);
      window.removeEventListener('mouseup', handleUp);
      window.removeEventListener('touchmove', handleMove as any);
      window.removeEventListener('touchend', handleUp as any);
    };
  }, [cutDragState, duration, onSeek, cutMarkers, onCutMarkerMove]);

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
  const timelineWidth = getTimelineWidth(); // dynamische Breite
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

  // Handler für Timeline-Klick im Cut-Modus
  const handleTimelineClick = (e: React.MouseEvent) => {
    if (!cutModeActive || !containerRef.current) return;
    
    const box = containerRef.current.getBoundingClientRect();
    const x = e.clientX - box.left;
    const clickedSeconds = (x / getTimelineWidth()) * duration;
    
    if (pendingCut) {
      // Zweiter Klick: Cut abschließen
      const startSeconds = Math.min(pendingCut.startSeconds, clickedSeconds);
      const endSeconds = Math.max(pendingCut.startSeconds, clickedSeconds);
      onCutMarkerAdd?.(startSeconds, endSeconds);
      setPendingCut(null);
    } else {
      // Erster Klick: Start setzen
      setPendingCut({ startSeconds: clickedSeconds });
      onSeek(clickedSeconds);
    }
  };

  // Handler für Cut-Marker Drag
  const handleCutMarkerMouseDown = (
    e: React.MouseEvent | React.TouchEvent,
    markerId: string,
    type: 'start' | 'end',
    seconds: number
  ) => {
    e.stopPropagation();
    e.preventDefault();
    
    const box = containerRef.current?.getBoundingClientRect();
    if (!box) return;
    
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    // Berechne die Position des Markers auf der Timeline
    const markerX = box.left + (seconds / duration) * getTimelineWidth();
    const offsetX = clientX - markerX;
    
    setCutDragState({
      type,
      markerId,
      origSeconds: seconds,
      offsetX: offsetX,
      currentMouseX: clientX,
    });
  };

  // Handler für Range-Drag (gesamte Schnittmarke verschieben)
  const handleRangeMouseDown = (
    e: React.MouseEvent | React.TouchEvent,
    markerId: string,
    startSeconds: number,
    endSeconds: number
  ) => {
    e.stopPropagation();
    e.preventDefault();
    
    const box = containerRef.current?.getBoundingClientRect();
    if (!box) return;
    
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    // Speichere initiale Mausposition für Delta-Berechnung
    setCutDragState({
      type: 'range',
      markerId,
      origSeconds: startSeconds,
      origEndSeconds: endSeconds,
      offsetX: 0,
      currentMouseX: clientX,
      initialMouseX: clientX,
    });
  };

  // Mouse-Event-Handler für Click oder Drag
  const handleMouseDown = (e: React.MouseEvent | React.TouchEvent, eventId: number, origSeconds: number, row: number) => {
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
      const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
      const markerX = box.left + (origSeconds / duration) * getTimelineWidth();
      setDragState({
        eventId,
        origSeconds,
        row,
        offsetX: clientX - markerX,
        mouseX: clientX,
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
      {/* Time labels and info - Events Timeline */}
      {!showCutMarkers && (
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
      )}
      
      {/* Time labels and info - Schnittmarken Timeline */}
      {showCutMarkers && (
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1, px: 1, fontSize: '0.875rem', color: '#666' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <ContentCutIcon sx={{ fontSize: 16, color: '#ff9800', mr: 0.5 }} />
                <strong>Schnittmarken-Timeline</strong>
            </Box>
            <Box>
                <strong>Länge:</strong> {formatSeconds(duration)}
            </Box>
        </Box>
      )}

    {/* Events Timeline */}
    {!showCutMarkers && (
    <Box
        sx={{ position: 'relative', width: '100%', height: 32 + maxRow * rowHeight }}
    >
    {/* Timeline Bar */}
    <Box sx={{ 
      position: 'absolute', 
      top: 16 + maxRow * rowHeight / 2, 
      left: 0, 
      right: 0, 
      height: 4, 
      bgcolor: '#e0e0e0', 
      borderRadius: 2, 
      transform: 'translateY(-50%)'
    }} />
      
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
                p: { xs: 0.5, sm: 0.2 },
                fontSize: { xs: 24, sm: 18 },
                minWidth: { xs: 44, sm: 'auto' },
                minHeight: { xs: 44, sm: 'auto' },
                transition: 'box-shadow 0.2s',
                '&:hover': { boxShadow: 4, bgcolor: '#f5f5f5' },
                userSelect: 'none',
              }}
              onMouseDown={(e) => handleMouseDown(e, event.id, event.timestamp + gameStart, row)}
              onTouchStart={(e) => handleMouseDown(e as unknown as React.MouseEvent, event.id, event.timestamp + gameStart, row)}
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
            left: `${((dragState.mouseX - dragState.offsetX - containerRef.current.getBoundingClientRect().left) / getTimelineWidth()) * 100}%`,
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
    )}
    
    {/* Schnittmarken Timeline - separate Timeline */}
    {showCutMarkers && (
      <Box
        sx={{ position: 'relative', width: '100%', height: 60 }}
        onClick={handleTimelineClick}
      >
        {/* Timeline Bar */}
        <Box sx={{ 
          position: 'absolute', 
          top: 30, 
          left: 0, 
          right: 0, 
          height: 6, 
          bgcolor: '#e0e0e0', 
          borderRadius: 2, 
          transform: 'translateY(-50%)',
          cursor: cutModeActive ? 'crosshair' : 'default'
        }} />
        
        {/* Cut Markers - Bereiche */}
        {cutMarkers.map((marker) => {
          const startPx = (marker.startSeconds / duration) * 100;
          const widthPx = ((marker.endSeconds - marker.startSeconds) / duration) * 100;
          
          // Skip rendering if currently being dragged as range
          const isDraggingRange = cutDragState && cutDragState.markerId === marker.id && cutDragState.type === 'range';
          
          if (isDraggingRange) return null;
          
          return (
            <Box
              key={marker.id}
              sx={{
                position: 'absolute',
                top: 30,
                left: `${startPx}%`,
                width: `${widthPx}%`,
                height: { xs: 24, sm: 14 },
                bgcolor: 'rgba(255, 152, 0, 0.3)',
                border: '2px solid #ff9800',
                borderRadius: 1,
                transform: 'translateY(-50%)',
                zIndex: 1,
                cursor: 'grab',
                '&:hover': {
                  bgcolor: 'rgba(255, 152, 0, 0.5)',
                  boxShadow: 2,
                },
                '&:active': {
                  cursor: 'grabbing',
                },
              }}
              onMouseDown={(e) => handleRangeMouseDown(e, marker.id, marker.startSeconds, marker.endSeconds)}
              onTouchStart={(e) => handleRangeMouseDown(e as unknown as React.MouseEvent, marker.id, marker.startSeconds, marker.endSeconds)}
            >
              <Tooltip title={`${formatSeconds(marker.startSeconds)} - ${formatSeconds(marker.endSeconds)} (${formatSeconds(marker.endSeconds - marker.startSeconds)})`}>
                <Box sx={{ width: '100%', height: '100%' }} />
              </Tooltip>
            </Box>
          );
        })}
        
        {/* Cut Markers - Start/End Griffe */}
        {cutMarkers.map((marker) => {
          const startPx = (marker.startSeconds / duration) * 100;
          const endPx = (marker.endSeconds / duration) * 100;
          
          // Skip rendering if currently being dragged (will show overlay instead)
          const isDraggingStart = cutDragState && cutDragState.markerId === marker.id && cutDragState.type === 'start';
          const isDraggingEnd = cutDragState && cutDragState.markerId === marker.id && cutDragState.type === 'end';
          
          return (
            <React.Fragment key={`handles-${marker.id}`}>
              {/* Start Marker */}
              {!isDraggingStart && (
              <Tooltip title={`Start: ${formatSeconds(marker.startSeconds)}`}>
                <Box
                  sx={{
                    position: 'absolute',
                    top: 30,
                    left: `${startPx}%`,
                    transform: 'translate(-50%, -50%)',
                    cursor: 'ew-resize',
                    zIndex: 3,
                    bgcolor: '#ff9800',
                    color: 'white',
                    borderRadius: '50%',
                    width: { xs: 44, sm: 22 },
                    height: { xs: 44, sm: 22 },
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: { xs: 20, sm: 14 },
                    boxShadow: 2,
                    '&:hover': { boxShadow: 4, bgcolor: '#f57c00' },
                  }}
                  onMouseDown={(e) => handleCutMarkerMouseDown(e, marker.id, 'start', marker.startSeconds)}
                  onTouchStart={(e) => handleCutMarkerMouseDown(e as unknown as React.MouseEvent, marker.id, 'start', marker.startSeconds)}
                >
                  ◀
                </Box>
              </Tooltip>
              )}
              
              {/* End Marker */}
              {!isDraggingEnd && (
              <Tooltip title={`Ende: ${formatSeconds(marker.endSeconds)}`}>
                <Box
                  sx={{
                    position: 'absolute',
                    top: 30,
                    left: `${endPx}%`,
                    transform: 'translate(-50%, -50%)',
                    cursor: 'ew-resize',
                    zIndex: 3,
                    bgcolor: '#ff9800',
                    color: 'white',
                    borderRadius: '50%',
                    width: { xs: 44, sm: 22 },
                    height: { xs: 44, sm: 22 },
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: { xs: 20, sm: 14 },
                    boxShadow: 2,
                    '&:hover': { boxShadow: 4, bgcolor: '#f57c00' },
                  }}
                  onMouseDown={(e) => handleCutMarkerMouseDown(e, marker.id, 'end', marker.endSeconds)}
                  onTouchStart={(e) => handleCutMarkerMouseDown(e as unknown as React.MouseEvent, marker.id, 'end', marker.endSeconds)}
                >
                  ▶
                </Box>
              </Tooltip>
              )}
            </React.Fragment>
          );
        })}
        
        {/* Pending Cut - erster Klick gesetzt */}
        {pendingCut && (
          <Tooltip title={`Startpunkt: ${formatSeconds(pendingCut.startSeconds)}`}>
            <Box
              sx={{
                position: 'absolute',
                top: 30,
                left: `${(pendingCut.startSeconds / duration) * 100}%`,
                transform: 'translate(-50%, -50%)',
                zIndex: 3,
                bgcolor: '#ff9800',
                color: 'white',
                borderRadius: '50%',
                width: { xs: 44, sm: 22 },
                height: { xs: 44, sm: 22 },
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: { xs: 22, sm: 16 },
                boxShadow: 2,
                animation: 'pulse 1s ease-in-out infinite',
                '@keyframes pulse': {
                  '0%, 100%': { opacity: 1 },
                  '50%': { opacity: 0.6 },
                },
              }}
            >
              <ContentCutIcon sx={{ fontSize: 14 }} />
            </Box>
          </Tooltip>
        )}
        
        {/* Drag-Overlay für Cut Marker */}
        {cutDragState && containerRef.current && (() => {
          const box = containerRef.current.getBoundingClientRect();
          let leftPercent;
          
          if (cutDragState.type === 'range' && cutDragState.initialMouseX !== undefined) {
            // Range: Berechne neue Position basierend auf Delta
            const deltaX = cutDragState.currentMouseX - cutDragState.initialMouseX;
            const deltaSeconds = (deltaX / getTimelineWidth()) * duration;
            let newStart = cutDragState.origSeconds + deltaSeconds;
            const rangeDuration = cutDragState.origEndSeconds! - cutDragState.origSeconds;
            
            // Grenzen
            if (newStart < 0) newStart = 0;
            if (newStart + rangeDuration > duration) newStart = duration - rangeDuration;
            
            leftPercent = (newStart / duration) * 100;
          } else {
            // Start/End Marker
            leftPercent = ((cutDragState.currentMouseX - cutDragState.offsetX - box.left) / getTimelineWidth()) * 100;
          }
          
          return (
            <Box
              sx={{
                position: 'absolute',
                top: 30,
                left: `${leftPercent}%`,
                transform: cutDragState.type === 'range' ? 'translateY(-50%)' : 'translate(-50%, -50%)',
                pointerEvents: 'none',
                zIndex: 10,
                bgcolor: '#ff9800',
                color: 'white',
                border: '2px solid #f57c00',
                borderRadius: cutDragState.type === 'range' ? 1 : '50%',
                width: cutDragState.type === 'range' 
                  ? `${((cutDragState.origEndSeconds! - cutDragState.origSeconds) / duration) * 100}%`
                  : 22,
                height: cutDragState.type === 'range' ? 14 : 22,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 14,
                boxShadow: 4,
                opacity: 0.9,
              }}
            >
              {cutDragState.type === 'start' && '◀'}
              {cutDragState.type === 'end' && '▶'}
              {cutDragState.type === 'range' && '⇔'}
            </Box>
          );
        })()}
      </Box>
    )}
    </Box>
  );
};

export default VideoTimeline;
