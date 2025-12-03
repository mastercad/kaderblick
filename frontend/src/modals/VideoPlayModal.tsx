import React, { useRef, useImperativeHandle, forwardRef, useState } from 'react';
import { GameEvent } from '../types/games';
import YouTube, { YouTubePlayer, YouTubeEvent } from 'react-youtube';
import BaseModal from './BaseModal';
import { Box } from '@mui/material';
import VideoTimeline from '../components/VideoTimeline';
import { mapGameEventsToTimelineEvents, calculateCumulativeOffset } from '../utils/videoTimeline';
import { updateGameEvent } from '../services/games';

interface VideoPlayModalProps {
  open: boolean;
  onClose: () => void;
  videoId?: string;
  videoName?: string;
  videoObj: {
    id: number;
    youtubeId?: string;
    gameStart: number | null;
    length: number;
    camera?: { id: number; name: string } | null;
  };
  gameEvents: GameEvent[];
  gameStartDate: string;
  gameId?: number;
  onEventUpdated?: () => void | Promise<void>;
  allVideos?: Array<any>; // Videos array with id, gameStart, length, sort
  youtubeLinks?: any; // { [eventId]: { [cameraId]: [urls] } } - flexible type from backend
  children?: React.ReactNode;
}

/**
 * Konvertiert Sekunden in das MM:SS Format für den Backend-API
 */
function secondsToMinuteFormat(seconds: number): string {
  const totalSeconds = Math.round(seconds);
  const minutes = Math.floor(totalSeconds / 60);
  const secs = totalSeconds % 60;
  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

const VideoPlayModal = forwardRef<any, VideoPlayModalProps>(({ open, onClose, videoId, videoName, videoObj, gameEvents, gameStartDate, gameId, onEventUpdated, allVideos, youtubeLinks, children }, ref) => {
  const playerRef = useRef<YouTubePlayer | null>(null);
  // Fallback für videoObj, falls nicht gesetzt
  const safeVideoObj = videoObj || { id: 0, gameStart: null, length: 0, camera: undefined };
  const [videoDuration, setVideoDuration] = useState<number>(safeVideoObj.length || 0);
  const [movedEvents, setMovedEvents] = useState<ReturnType<typeof mapGameEventsToTimelineEvents> | null>(null);
  const [updateError, setUpdateError] = useState<string | null>(null);
  
  // Berechne den kumulativen Offset für Videos mit gameStart=null
  const cumulativeOffset = allVideos ? calculateCumulativeOffset(
    { ...safeVideoObj, length: videoDuration },
    allVideos
  ) : (typeof safeVideoObj.gameStart === 'number' ? -safeVideoObj.gameStart : 0);
  
  // Timeline-Events berechnen (nutze aktuelle Videolänge, falls YouTube-API sie liefert)
  const timelineEvents = movedEvents || mapGameEventsToTimelineEvents({
    gameEvents: gameEvents,
    video: { ...safeVideoObj, length: videoDuration },
    gameStartDate,
    cumulativeOffset,
    youtubeLinks,
    cameraId: safeVideoObj.camera?.id,
  });

  // Wenn sich die gameEvents-Prop ändert (z.B. nach Event-Anlage), neue Events ergänzen
  React.useEffect(() => {
    setMovedEvents(prev => {
      const mapped = mapGameEventsToTimelineEvents({
        gameEvents: gameEvents,
        video: { ...safeVideoObj, length: videoDuration },
        gameStartDate,
        cumulativeOffset,
        youtubeLinks,
        cameraId: safeVideoObj.camera?.id,
      });
      if (!prev) return mapped;
      // Sync: Für jedes Event aus mapped:
      // - Wenn es in prev existiert, behalte die lokale Zeit (z.B. nach Drag & Drop)
      // - Sonst nimm das neue Event
      // - Entferne Events, die nicht mehr im Backend sind
      const prevMap = new Map(prev.map(ev => [ev.id, ev]));
      const result = mapped.map(ev => {
        const local = prevMap.get(ev.id);
        // Nur timestamp (Drag & Drop) lokal behalten, aber NUR wenn es eine Zahl ist (Timeline-Format)
        if (local && typeof local.timestamp === 'number') {
          return { ...ev, timestamp: local.timestamp };
        }
        return ev;
      });
      return result;
    });
  }, [gameEvents, gameStartDate, videoDuration, cumulativeOffset, youtubeLinks, safeVideoObj.camera?.id]);

  // Handler für Drag & Drop
  const handleEventMove = (eventId: number, newSeconds: number) => {
    // Lokalen State aktualisieren
    setMovedEvents(prev => {
      const base = prev || mapGameEventsToTimelineEvents({
        gameEvents: gameEvents,
        video: { ...safeVideoObj, length: videoDuration },
        gameStartDate,
        cumulativeOffset,
        youtubeLinks,
        cameraId: safeVideoObj.camera?.id,
      });
      return base.map(ev =>
        ev.id === eventId ? { ...ev, timestamp: Math.max(0, Math.min(videoDuration, newSeconds)) } : ev
      );
    });

    if (gameId) {
      const minuteFormat = secondsToMinuteFormat(newSeconds);
      updateGameEvent(gameId, eventId, { minute: minuteFormat })
        .then(async () => {
          setUpdateError(null);
          if (onEventUpdated) {
            await onEventUpdated();
          }
        })
        .catch(err => {
          setUpdateError('Fehler beim Speichern des Events');
        });
    }
  };

  const handleSeekToGameStart = () => {
    if (playerRef.current && typeof safeVideoObj.gameStart === 'number') {
      playerRef.current.seekTo(safeVideoObj.gameStart, true);
    }
  };

  const handleSeek = (seconds: number) => {
    if (playerRef.current) {
      playerRef.current.seekTo(seconds, true);
    }
  };

  useImperativeHandle(ref, () => ({
    getCurrentTime: () => playerRef.current?.getCurrentTime() ?? 0,
    pauseVideo: () => playerRef.current?.pauseVideo(),
  }));

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title={videoName || 'Video ansehen'}
      maxWidth="md"
      fullWidth
      showCloseButton
    >
      <Box sx={{ position: 'relative', width: '100%', mb: 2 }}>
        {videoId ? (
          <Box sx={{ position: 'relative', paddingTop: '56.25%' }}>
            <YouTube
              videoId={videoId}
              opts={{
                width: '100%',
                height: '100%',
                playerVars: {
                  autoplay: 1,
                  modestbranding: 1,
                  rel: 0,
                },
              }}
              onReady={(e: YouTubeEvent) => {
                playerRef.current = e.target;
                setVideoDuration(e.target.getDuration?.() || 0);
              }}
              onStateChange={(e: YouTubeEvent) => {
                // Falls Dauer erst nach Play verfügbar ist
                if (!videoDuration && e.target.getDuration) {
                  setVideoDuration(e.target.getDuration());
                }
              }}
              style={{
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                border: 0,
                borderRadius: 8,
              }}
            />
          </Box>
        ) : (
          <Box>Kein Video gefunden.</Box>
        )}
      </Box>
      {/* Timeline unter dem Video */}
      {videoDuration > 0 && (
        <VideoTimeline
          duration={videoDuration}
          events={timelineEvents}
          onSeek={handleSeek}
          onSeekToGameStart={handleSeekToGameStart}
          onEventMove={handleEventMove}
          gameStart={safeVideoObj.gameStart || 0}
        />
      )}
      {children}
    </BaseModal>
  );
});

export default VideoPlayModal;
