import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Typography,
  Card,
  CardContent,
  CardHeader,
  Button,
  Chip,
  List,
  ListItem,
  ListItemText,
  Alert,
  CircularProgress,
  IconButton,
  Fab,
  Icon,
  Link
} from '@mui/material';
import {
  ArrowBack as ArrowBackIcon,
  SportsSoccer as SoccerIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Add as AddIcon,
  VideoLibrary as VideoIcon,
  CalendarToday as CalendarIcon,
  Sync as SyncIcon,
  LocationOn as LocationIcon,
  ContentCut as ContentCutIcon
} from '@mui/icons-material';
import { 
  fetchGameDetails, 
  fetchGameEvents,
  deleteGameEvent, 
  syncFussballDe 
} from '../services/games';
import { fetchVideos, saveVideo, deleteVideo, Video, YoutubeLink, Camera } from '../services/videos';
import VideoModal from '../modals/VideoModal';
import { VideoSegmentModal } from '../modals/VideoSegmentModal';
import { Game, GameEvent } from '../types/games';
import { useAuth } from '../context/AuthContext';
import { ToastProvider, useToast } from '../context/ToastContext';
import { ConfirmationModal } from '../modals/ConfirmationModal';
import { GameEventModal } from '../modals/GameEventModal';
import Location from '../components/Location';
import { getGameEventIconByCode } from '../constants/gameEventIcons';
import YouTubeIcon from '@mui/icons-material/YouTube';
import WeatherModal from '../modals/WeatherModal';
import { WeatherDisplay } from '../components/WeatherIcons';
import { formatEventTime, formatDateTime } from '../utils/formatter'
import { UserAvatar } from '../components/UserAvatar';
import { getAvatarFrameUrl } from '../utils/avatarFrame';

interface GameDetailsProps {
  gameId?: number;
  onBack?: () => void;
}

function GameDetailsInner({ gameId: propGameId, onBack }: GameDetailsProps) {
  const { user } = useAuth();
  const { showToast } = useToast();
  const params = useParams<{ id: string }>();
  const navigate = useNavigate();
  
  // Use URL param if available, otherwise fall back to prop
  const gameId = params.id ? parseInt(params.id, 10) : propGameId;
  
  const [game, setGame] = useState<Game | null>(null);
  const [gameStartDate, setGameStartDate] = useState<string | null>(null);
  const [gameEvents, setGameEvents] = useState<GameEvent[]>([]);
  const [homeScore, setHomeScore] = useState<number | null>(null);
  const [awayScore, setAwayScore] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [eventToDelete, setEventToDelete] = useState<GameEvent | null>(null);
  const [eventFormOpen, setEventFormOpen] = useState(false);
  const [eventToEdit, setEventToEdit] = useState<GameEvent | null>(null);
  const [syncing, setSyncing] = useState(false);
  const [videos, setVideos] = useState<Video[]>([]);
  const [videoTypes, setVideoTypes] = useState<any[]>([]);
  const [youtubeLinks, setYoutubeLinks] = useState<YoutubeLink[]>([]);
  const [cameras, setCameras] = useState<any[]>([]);
  const [eventVideos, setEventVideos] = useState<Record<number, Video[]>>({});
  const [mappedCameras, setMappedCameras] = useState<Record<number, string>>({});
  const [videoDialogOpen, setVideoDialogOpen] = useState(false);
  const [videoDialogLoading, setVideoDialogLoading] = useState(false);
  const [videoToEdit, setVideoToEdit] = useState<Video | null>(null);
  const [videoToDelete, setVideoToDelete] = useState<Video | null>(null);
  const [videoDeleteLoading, setVideoDeleteLoading] = useState(false);
  const [weatherModalOpen, setWeatherModalOpen] = useState(false);
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null);
  const [videoSegmentModalOpen, setVideoSegmentModalOpen] = useState(false);

  useEffect(() => {
    if (!gameId) {
      setError('Keine Spiel-ID angegeben');
      setLoading(false);
      return;
    }
    loadGameDetails();
    loadVideos();
  }, [gameId]);

  const loadVideos = async () => {
    if (!gameId) return;
    try {
      const res = await fetchVideos(gameId);
      setVideos(res.videos);
      setYoutubeLinks(res.youtubeLinks);
      setVideoTypes(res.videoTypes);
      // Mapping Event-ID -> Videos
      const mapping: Record<number, Video[]> = {};
      if (res.videos) {
        res.videos.forEach((video: any) => {
          if (Array.isArray(video.eventIds)) {
            video.eventIds.forEach((eventId: number) => {
              if (!mapping[eventId]) mapping[eventId] = [];
              mapping[eventId].push(video);
            });
          }
        });
      }
      const mappedCameras: Record<number, string> = {};
      if (res.cameras) {
        res.cameras.forEach((camera: Camera) => {
          if (camera.id) { 
            mappedCameras[camera.id] = camera.name;
          }
        });
      }

      setCameras(res.cameras);
      setMappedCameras(mappedCameras);
      setEventVideos(mapping);
    } catch (e) {
      // Fehler ignorieren, Videos optional
    }
  };

  // Video Handlers
  const handleOpenAddVideo = () => {
    setVideoToEdit(null);
    setVideoDialogOpen(true);
  };

  const handleOpenEditVideo = (video: Video) => {
    setVideoToEdit(video);
    setVideoDialogOpen(true);
  };

  const handleCloseVideoDialog = () => {
    setVideoDialogOpen(false);
    setVideoToEdit(null);
  };

  const handleSaveVideo = async (data: any) => {
    if (!gameId) return;
    setVideoDialogLoading(true);
    try {
      await saveVideo(gameId, data);
      setVideoDialogOpen(false);
      setVideoToEdit(null);
      await loadVideos();
      showToast('Das Video wurde erfolgreich gespeichert.', 'success');
    } catch (e: any) {
      setError(e.message || 'Fehler beim Speichern des Videos');
    } finally {
      setVideoDialogLoading(false);
    }
  };

  const handleDeleteVideo = async () => {
    if (!videoToDelete) return;
    setVideoDeleteLoading(true);
    try {
      await deleteVideo(videoToDelete.id);
      setVideoToDelete(null);
      await loadVideos();
    } catch (e: any) {
      setError(e.message || 'Fehler beim Löschen des Videos');
    } finally {
      setVideoDeleteLoading(false);
    }
  };

  const loadGameDetails = async () => {
    if (!gameId) return;
    try {
      setLoading(true);
      setError(null);
      const result = await fetchGameDetails(gameId);
      setGame(result.game);
      setGameEvents(result.gameEvents);
      setHomeScore(result.homeScore);
      setAwayScore(result.awayScore);
      setGameStartDate(result.game?.calendarEvent?.startDate ?? null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Spieldetails');
    } finally {
      setLoading(false);
    }
  };

  // Nur Events laden (für SPA-Feeling)
  const loadGameEvents = async () => {
    if (!gameId) return;
    try {
      const events = await fetchGameEvents(gameId);
      setGameEvents(events);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Ereignisse');
    }
  };

  const handleDeleteEvent = async () => {
    if (!eventToDelete || !gameId) return;
    
    try {
      await deleteGameEvent(gameId, eventToDelete.id);
      await loadGameDetails(); // Reload data
      setEventToDelete(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Löschen des Ereignisses');
    }
  };

  const handleSyncFussballDe = async () => {
    if (!game?.fussballDeUrl || !gameId) return;
    
    try {
      setSyncing(true);
      await syncFussballDe(gameId);
      await loadGameDetails(); // Reload data
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Synchronisieren mit Fussball.de');
    } finally {
      setSyncing(false);
    }
  };

  const handleEventFormSuccess = async () => {
    setEventFormOpen(false);
    setEventToEdit(null);
    await loadGameEvents(); // Nur Events neu laden, game bleibt erhalten
  };

  const openWeatherModal = (eventId: number | null) => {
    setSelectedEventId(eventId);
    setWeatherModalOpen(true);
  };

  const isGameRunning = () => {
    if (!game?.calendarEvent?.startDate || !game?.calendarEvent?.endDate) return false;
    const now = new Date();
    const start = new Date(game.calendarEvent.startDate);
    const end = new Date(game.calendarEvent.endDate);
    return now >= start && now <= end;
  };

  const canCreateEvents = () => {
    return game?.permissions?.can_create_game_events ?? false;
  };

  const canCreateVideos = () => {
    return game?.permissions?.can_create_videos ?? false;
  }

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
        <Button variant="contained" onClick={loadGameDetails}>
          Erneut versuchen
        </Button>
      </Box>
    );
  }

  if (!game) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="info">Spiel nicht gefunden</Alert>
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
        <IconButton 
          onClick={() => onBack ? onBack() : navigate('/games')} 
          sx={{ mr: 2 }}
        >
          <ArrowBackIcon />
        </IconButton>
        <Typography variant="h4" component="h1">
          Spieldetails
        </Typography>
      </Box>

      {/* Game Info Card */}
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <Box sx={{ mb: 2, display: 'flex', flexDirection: 'row', alignItems: 'stretch', gap: 2 }}>
            {/* Linke Box: Teams, Zeit, Location */}
            <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'flex-start', gap: 1 }}>
              <Typography variant="h5" component="h2" gutterBottom>
                <strong>{game.homeTeam.name}</strong> vs <strong>{game.awayTeam.name}</strong>
              </Typography>
              {(game.calendarEvent?.startDate || game.location) && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  {game.calendarEvent?.startDate && (
                    <>
                      <CalendarIcon fontSize="small" />
                      <Typography variant="body2" color="text.secondary">
                        {formatDateTime(game.calendarEvent.startDate)}
                        {game.calendarEvent.endDate && ` - ${formatDateTime(game.calendarEvent.endDate).split(' ')[1]}`}
                      </Typography>
                    </>
                  )}
                  {game.location && (
                    <Location
                      name={game.location.name}
                      latitude={game.location.latitude ?? 0}
                      longitude={game.location.longitude ?? 0}
                      address={game.location.address ?? ''}
                    />
                  )}
                </Box>
              )}
            </Box>
            {/* Rechte Box: Weather Icon */}
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minWidth: 80, pr: 2, pt: 1 }}
              onClick={() => {
                openWeatherModal(game.calendarEvent?.id ?? null);
              }}>
              <span style={{ cursor: 'pointer' }} title="Wetterdetails anzeigen">
                <WeatherDisplay 
                  code={game.calendarEvent?.weatherData?.dailyWeatherData?.weathercode?.[0]} theme={'light'}
                />
              </span>
            </Box>
          </Box>

          <Box sx={{ mb: 2 }}>
            {homeScore !== null && awayScore !== null ? (
              <Chip 
                label={`${homeScore} : ${awayScore}`} 
                color="primary" 
                sx={{ fontSize: '1.2rem', p: 1 }}
              />
            ) : (
              <Chip label="Noch kein Ergebnis" color="default" />
            )}
          </Box>
          {game.fussballDeUrl && (
            <Box sx={{ mt: 2 }}>
              <Button
                variant="outlined"
                startIcon={<SyncIcon />}
                onClick={handleSyncFussballDe}
                disabled={syncing}
                size="small"
              >
                {syncing ? 'Synchronisiere...' : 'Mit Fussball.de synchronisieren'}
              </Button>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Game Events Card */}
      <Card sx={{ mb: 3 }}>
        <CardHeader
          title="Spielereignisse"
          action={
            canCreateEvents() && (
              <Button
                variant="contained"
                color="primary"
                startIcon={<AddIcon />}
                onClick={() => {
                  setEventToEdit(null);
                  setEventFormOpen(true);
                }}
                sx={{ ml: 1 }}
              >
                Ereignis erfassen
              </Button>
            )
          }
        />
        <CardContent>
          {gameEvents.length > 0 ? (
            <List>
              {gameEvents.map((event) => {
                const videosForEvent = youtubeLinks[event.id] || [];
                
                const e = event as any;
                let playerDisplay = '';
                let minute = 0;
                let code = '';
                let icon = '';
                let color = '#FFF';

                if (typeof e.player === 'string') {
                  playerDisplay = e.player;
                } else if (e.player && typeof e.player === 'object') {
                  playerDisplay = `${e.player.firstName ?? ''} ${e.player.lastName ?? ''}`.trim();
                }
                if (e.minute) {
                  // minutes are the old name, for better video marker we get seconds
                  minute = e.minute / 60;
                } else if (e.timestamp) {
                  minute = formatEventTime(e.timestamp, gameStartDate ?? '') as unknown as number;
                }
                if (e.code) {
                  code = e.code;
                } else if (e.gameEventType?.code) {
                  code = e.gameEventType.code;
                }
                if (e.icon) {
                  icon = e.icon;
                } else if (e.gameEventType?.icon) {
                  icon = e.gameEventType.icon;
                }
                if (e.color) {
                  color = e.color;
                } else if (e.gameEventType?.color) {
                  color = e.gameEventType?.color;
                }
                return (
                  <ListItem
                    key={e.id}
                    alignItems="flex-start"
                    secondaryAction={
                      user && (
                        <Box>
                          <IconButton
                            edge="end"
                            onClick={() => {
                              setEventToEdit(event);
                              setEventFormOpen(true);
                            }}
                            sx={{ mr: 1 }}
                          >
                            <EditIcon />
                          </IconButton>
                          <IconButton
                            edge="end"
                            onClick={() => {
                              setEventToDelete(event);
                            }}
                          >
                            <DeleteIcon />
                          </IconButton>
                        </Box>
                      )
                    }
                  >
                    <ListItemText
                      primary={
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, flexWrap: 'wrap' }}>
                          <span>
                            <Chip label={minute ?? ''} size="small" sx={{ mr: 1, bgcolor: 'grey.200', color: 'text.primary' }} />
                            {e.typeColor && (
                              <span style={{
                                display: 'inline-block',
                                width: 12,
                                height: 12,
                                borderRadius: '50%',
                                background: e.typeColor,
                                marginRight: 8,
                                verticalAlign: 'middle'
                              }} />
                            )}
                            { /*
                            {e.typeIcon && (
                              <i className={e.typeIcon} style={{ marginRight: 8, verticalAlign: 'middle' }} />
                            )}
                            */ }
                            <span style={{ color: color, marginLeft: 8 }}>
                            {
                              getGameEventIconByCode(icon)
                            }
                            </span>
                            <strong style={{ marginRight: 10 }}>{e.type ?? e?.gameEventType.name ?? 'Unbekannt'}</strong>
                            <UserAvatar
                              icon={e.player?.playerAvatarUrl}
                              name={playerDisplay || 'Unbekannt'}
                              avatarSize={26}
                              fontSize={12}
                              titleObj={e.player?.title && e.player?.title.hasTitle ? e.player.title : undefined}
                              level={e.player?.level?.level}
                            />
                            {e.description && (
                              <span style={{ color: '#888', marginLeft: 8 }}>{e.description}</span>
                            )}
                          </span>
                          {/* Video-Links zu diesem Event, falls vorhanden */}
                          {Object.keys(videosForEvent).length > 0 && (
                            <Box sx={{ ml: 2, display: 'flex', gap: 1 }}>
                              {Object.entries(videosForEvent).map((currentVideo) => (
                                <>
                                  <YouTubeIcon fontSize="small" />
                                  <Link
                                    href={currentVideo[1]}
                                    target="_blank"
                                  >
                                    { mappedCameras[currentVideo[0]] }
                                  </Link>
                                </>
                              ))}
                            </Box>
                          )}
                        </Box>
                      }
                    />
                  </ListItem>
                );
              })}
            </List>
          ) : (
            <Typography color="text.secondary">
              Keine Ereignisse für dieses Spiel.
            </Typography>
          )}
        </CardContent>
      </Card>

      {/* Videos Card */}
      <Card sx={{ mb: 3 }}>
        <CardHeader
          title="Videos"
          action={
            <Box>
              {videos.length > 0 && (
                <Button
                  variant="outlined"
                  startIcon={<ContentCutIcon />}
                  size="small"
                  onClick={() => setVideoSegmentModalOpen(true)}
                  sx={{ mr: 1 }}
                >
                  Schnittliste
                </Button>
              )}
              {canCreateVideos() && (
                <Button
                  variant="contained"
                  startIcon={<VideoIcon />}
                  size="small"
                  onClick={handleOpenAddVideo}
                >
                  Video hinzufügen
                </Button>
              )}
            </Box>
          }
        />
        <CardContent>
          {videos.length > 0 ? (
            <List>
              {videos.map((video) => (
                <ListItem
                  key={video.id}
                  secondaryAction={
                    user && (
                      <>
                        <IconButton edge="end" onClick={() => handleOpenEditVideo(video)} sx={{ mr: 1 }}>
                          <EditIcon />
                        </IconButton>
                        <IconButton edge="end" color="error" onClick={() => setVideoToDelete(video)}>
                          <DeleteIcon />
                        </IconButton>
                      </>
                    )
                  }
                  alignItems="flex-start"
                >
                  {video.youtubeId && (
                    <a href={`https://youtu.be/${video.youtubeId}`} target="_blank" rel="noopener noreferrer">
                      <img
                        src={`https://img.youtube.com/vi/${video.youtubeId}/hqdefault.jpg`}
                        alt="Video Thumbnail"
                        style={{ width: 120, height: 'auto', marginRight: 16, borderRadius: 4 }}
                      />
                    </a>
                  )}
                  <ListItemText
                    primary={
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        {video.youtubeId ? (
                          <a href={`https://youtu.be/${video.youtubeId}`} target="_blank" rel="noopener noreferrer">
                            <Typography variant="body1" color="primary" fontWeight="bold">{video.name}</Typography>
                          </a>
                        ) : video.url ? (
                          <a href={video.url} target="_blank" rel="noopener noreferrer">
                            <Typography variant="body1" color="primary" fontWeight="bold">{video.name}</Typography>
                          </a>
                        ) : (
                          <Typography variant="body1">{video.name}</Typography>
                        )}
                        {video.videoType?.name && (
                          <Chip label={video.videoType.name} size="small" sx={{ ml: 1 }} />
                        )}
                        {video.length && (
                          <Chip label={`${video.length}s`} size="small" sx={{ ml: 1 }} />
                        )}
                      </Box>
                    }
                    secondary={video.filePath ? `Dateipfad: ${video.filePath}` : undefined}
                  />
                </ListItem>
              ))}
            </List>
          ) : (
            <Typography color="text.secondary">
              Keine Videos für dieses Spiel.
            </Typography>
          )}
        </CardContent>
      </Card>

      {/* Video Modal */}
      <VideoModal
        open={videoDialogOpen}
        onClose={handleCloseVideoDialog}
        onSave={handleSaveVideo}
        videoTypes={videoTypes}
        cameras={cameras}
        initialData={videoToEdit || undefined}
        loading={videoDialogLoading}
      />

      {/* Video Delete Confirmation */}
      <ConfirmationModal
        open={!!videoToDelete}
        onClose={() => setVideoToDelete(null)}
        onConfirm={handleDeleteVideo}
        title="Video löschen"
        message={`Soll das Video "${videoToDelete?.name}" wirklich gelöscht werden?`}
        confirmText="Löschen"
        confirmColor="error"
      />

      {/* Floating Action Button for quick event creation */}
      {canCreateEvents() && (
        <Fab
          color="primary"
          aria-label="add event"
          sx={{ position: 'fixed', bottom: 16, right: 16 }}
          onClick={() => setEventFormOpen(true)}
        >
          <AddIcon />
        </Fab>
      )}

      {/* Confirmation Modal for Event Deletion */}
      <ConfirmationModal
        open={!!eventToDelete}
        onClose={() => setEventToDelete(null)}
        onConfirm={handleDeleteEvent}
        title="Ereignis löschen"
        message={`Soll das Ereignis "${eventToDelete?.gameEventType.name}" wirklich gelöscht werden?`}
        confirmText="Löschen"
        confirmColor="error"
      />

      {/* Game Event Modal */}
      <GameEventModal
        open={eventFormOpen}
        onClose={() => {
          setEventFormOpen(false);
          setEventToEdit(null);
        }}
        onSuccess={handleEventFormSuccess}
        gameId={gameId!}
        game={game}
        existingEvent={eventToEdit}
      />

      <WeatherModal
        open={weatherModalOpen}
        onClose={() => setWeatherModalOpen(false)}
        eventId={selectedEventId}
      />

      {/* Video Segment Modal */}
      <VideoSegmentModal
        open={videoSegmentModalOpen}
        onClose={() => setVideoSegmentModalOpen(false)}
        videos={videos}
        gameId={gameId!}
      />
    </Box>
  );
}

export default function GameDetails(props: GameDetailsProps) {
  return (
    <ToastProvider>
      <GameDetailsInner {...props} />
    </ToastProvider>
  );
}
