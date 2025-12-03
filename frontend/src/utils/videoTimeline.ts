
/**
 * Wandelt Spielereignisse in Timeline-Events für ein bestimmtes Video um.
 * @param params
 *   gameEvents: Array aller Events des Spiels
 *   video: { gameStart: Sekunden seit Spielbeginn, length: Sekunden, ... }
 *   gameStartDate: ISO-String des Spielbeginns (z.B. aus calendarEvent.startDate)
 * @returns Array von Timeline-Events für die Timeline-Komponente
 */
// Typen für Events und Video
export interface GameEventType {
  id: number;
  name: string;
  code: string;
  color?: string;
  icon?: string;
}

export interface GameEvent {
  id: number;
  timestamp: string; // ISO-String
  gameEventType: GameEventType;
  description?: string;
}

export interface Video {
  id: number;
  youtubeId?: string; // YouTube Video-ID
  gameStart: number | null; // Sekunden seit Spielbeginn
  length: number; // Sekunden
}

export interface TimelineEvent {
  id: number;
  timestamp: number;
  label: string;
  icon: string;
  color: string;
  description?: string;
}

interface MapParams {
  gameEvents: GameEvent[];
  video: Video;
  gameStartDate: string;
  cumulativeOffset?: number; // Sekunden seit Spielbeginn für Videos mit gameStart=null
  youtubeLinks?: any; // { [eventId]: { [cameraId]: [urls] } }
  cameraId?: number; // Kamera-ID zum Filtern
}

/**
 * Berechnet die kumulative Start-Zeit für Videos mit gameStart=null.
 * Wenn gameStart definiert ist, wird es verwendet.
 * Wenn gameStart null ist und videos vorhanden sind, werden die Längen aller vorherigen Videos addiert.
 * 
 * @param currentVideo Das aktuelle Video
 * @param allVideos Alle Videos in Sortierungsreihenfolge (mit sort-Feld)
 * @returns Die kumulative Start-Zeit in Sekunden seit Spielbeginn
 */
export function calculateCumulativeOffset(currentVideo: Video & { sort?: number; camera?: { id: number } }, allVideos: (Video & { sort?: number; camera?: { id: number } })[]): number {
  // Filtere Videos nach der gleichen Kamera
  const cameraId = currentVideo.camera?.id;
  const sameCamera = cameraId 
    ? allVideos.filter(v => v.camera?.id === cameraId)
    : allVideos;
  
  // Sortiere Videos nach 'sort' Feld
  const sortedVideos = [...sameCamera].sort((a, b) => (a.sort ?? 0) - (b.sort ?? 0));
  const currentIndex = sortedVideos.findIndex(v => v.id === currentVideo.id);

  if (currentIndex < 0) {
    return 0; // Video nicht gefunden
  }

  // Summiere die Netto-Spielzeit ALLER vorherigen Videos dieser Kamera
  // gameStart signalisiert nur einen Offset im Video selbst, nicht einen Zeitsprung
  let cumulativeGameTime = 0;
  
  for (let i = 0; i < currentIndex; i++) {
    const prevVideo = sortedVideos[i];
    const gameStartOffset = prevVideo.gameStart ?? 0;
    const nettoGameTime = (prevVideo.length || 0) - gameStartOffset;
    cumulativeGameTime += nettoGameTime;
  }

  return cumulativeGameTime;
}

export function mapGameEventsToTimelineEvents({
  gameEvents,
  video,
  gameStartDate,
  cumulativeOffset,
  youtubeLinks,
  cameraId,
}: MapParams): TimelineEvent[] {
  if (!video || !gameEvents || !gameStartDate) {
    return [];
  }
  
  const gameStart = new Date(gameStartDate);
  
  // Bestimme den Video-Start im Spiel-Koordinatensystem:
  // cumulativeOffset wird von außen übergeben und berücksichtigt bereits alle vorherigen Videos
  // video.gameStart ist der Zeitpunkt IM VIDEO, wo die Aufnahme beginnt (Offset innerhalb des Videos)
  let videoStartInGame: number;
  if (typeof cumulativeOffset === 'number') {
    videoStartInGame = cumulativeOffset;
  } else {
    videoStartInGame = 0;
  }
  
  // Die Netto-Spielzeit in diesem Video (ohne Pre-Roll)
  const gameStartOffset = video.gameStart ?? 0;
  const nettoGameTimeInVideo = (video.length || 0) - gameStartOffset;
  const videoEndInGame = videoStartInGame + nettoGameTimeInVideo;

  const mapped = gameEvents
    .map((event: any): TimelineEvent | null => {
      // 1. Sicherheitscheck: timestamp muss existieren
      if (!event.timestamp) {
        return null;
      }
      
      const eventTime = new Date(event.timestamp);
      if (isNaN(eventTime.getTime())) {
        return null;
      }
      
      // 2. HAUPTFILTER: Prüfe ob das Event zu DIESEM Video gehört
      // youtubeLinks: { eventId: { cameraId: ["https://youtu.be/VIDEO_ID&t=123s"] } }
      if (youtubeLinks && video.youtubeId) {
        const eventLinks = youtubeLinks[event.id];
        if (!eventLinks) {
          return null; // Event hat keine Video-Links
        }
        
        // Prüfe ob eine der URLs zu diesem Video gehört (gleiche youtubeId)
        let belongsToThisVideo = false;
        
        // Durchsuche alle Kamera-IDs
        for (const camId in eventLinks) {
          const urls = eventLinks[camId];
          if (Array.isArray(urls)) {
            for (const url of urls) {
              // Extrahiere youtubeId aus URL
              // Unterstützt: https://youtu.be/VIDEO_ID, https://youtube.com/watch?v=VIDEO_ID
              let urlVideoId = null;
              
              // Format: https://youtu.be/VIDEO_ID
              let match = url.match(/youtu\.be\/([A-Za-z0-9_-]+)/);
              if (match) {
                urlVideoId = match[1];
              } else {
                // Format: https://youtube.com/watch?v=VIDEO_ID oder https://www.youtube.com/watch?v=VIDEO_ID
                match = url.match(/[?&]v=([A-Za-z0-9_-]+)/);
                if (match) {
                  urlVideoId = match[1];
                }
              }
              
              if (typeof window !== 'undefined') {
                console.log('[VideoTimeline] Event', event.id, '- URL:', url, '- Extracted ID:', urlVideoId, '- Current Video ID:', video.youtubeId, '- Match:', urlVideoId === video.youtubeId);
              }
              
              if (urlVideoId && urlVideoId === video.youtubeId) {
                belongsToThisVideo = true;
                break;
              }
            }
          }
          if (belongsToThisVideo) break;
        }
        
        if (!belongsToThisVideo) {
          if (typeof window !== 'undefined') {
            console.log('[VideoTimeline] Event', event.id, 'filtered out - does not belong to video', video.youtubeId);
          }
          return null; // Event gehört nicht zu diesem Video
        }
      }
      
      // 3. Berechne die Position des Events IN DIESEM VIDEO
      const secondsSinceGameStart = (eventTime.getTime() - gameStart.getTime()) / 1000;
      
      if (typeof window !== 'undefined' && video.youtubeId === 'OLnoG-Og6sI') {
        console.log('[VideoTimeline] Event', event.id, '- secondsSinceGameStart:', secondsSinceGameStart, '- videoStartInGame:', videoStartInGame, '- videoEndInGame:', videoEndInGame, '- inRange:', secondsSinceGameStart >= videoStartInGame && secondsSinceGameStart <= videoEndInGame);
      }
      
      // KRITISCH: Der Event muss innerhalb des Video-Zeitfensters liegen!
      if (secondsSinceGameStart < videoStartInGame || secondsSinceGameStart > videoEndInGame) {
        return null;
      }
      
      // Die Position des Events im Video ist: seine absolute Zeit - video start zeit
      const timelineTimestamp = secondsSinceGameStart - videoStartInGame;
      
      // 4. Extrahiere Event-Metadaten
      const label = event.gameEventType?.name || event.type || '';
      const icon = event.gameEventType?.icon || event.typeIcon || '';
      const color = event.gameEventType?.color || event.typeColor || '#1976d2';
      
      return {
        id: event.id,
        timestamp: timelineTimestamp,
        label,
        icon: typeof icon === 'string' ? icon : '',
        color: typeof color === 'string' ? color : '#1976d2',
        description: event.description,
      };
    })
    .filter((e): e is TimelineEvent => Boolean(e));
  
  return mapped;
}
