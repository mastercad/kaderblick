import React, { useEffect, useState, useRef } from 'react';
import { useToast } from '../context/ToastContext';
import {
  Dialog, DialogTitle, DialogContent, DialogActions, Button, Box, Typography, Alert, CircularProgress, IconButton, List, ListItem, ListItemText, TextField, MenuItem
} from '@mui/material';
import AddIcon from '@mui/icons-material/AddCircle';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';

interface Player {
  id: number;
  name: string;
  shirtNumber?: string | number;
}

interface Team {
  id: number;
  name: string;
}

interface FormationType {
  name: string;
  cssClass?: string;
  backgroundPath?: string;
}

interface PlayerData {
  id: number;
  x: number;
  y: number;
  number: string | number;
  name: string;
  playerId?: number | null;
  isRealPlayer?: boolean;
}

interface FormationData {
  code?: string;
  players?: PlayerData[];
}

interface Formation {
  id: number;
  name: string;
  formationType: FormationType;
  formationData: FormationData;
}

interface FormationEditModalProps {
  open: boolean;
  formationId: number | null;
  onClose: () => void;
  onSaved?: (formation: Formation) => void;
}

const FormationEditModal: React.FC<FormationEditModalProps> = ({ open, formationId, onClose, onSaved }) => {
  const [formation, setFormation] = useState<Formation | null>(null);
  const [players, setPlayers] = useState<PlayerData[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [availablePlayers, setAvailablePlayers] = useState<Player[]>([]);
  const { showToast } = useToast();
  const [teams, setTeams] = useState<Team[]>([]);
  const [selectedTeam, setSelectedTeam] = useState<number | ''>('');
  const [name, setName] = useState('');
  const [nextPlayerNumber, setNextPlayerNumber] = useState(1);
  const pitchRef = useRef<HTMLDivElement>(null);
  const [draggedPlayerId, setDraggedPlayerId] = useState<number | null>(null);

  // Initialdaten laden
  useEffect(() => {
    if (open) {
      apiJson<{ teams: Team[] }>(`/api/teams`)
        .then(data => {
          const loadedTeams = Array.isArray(data.teams) ? data.teams : [];
          setTeams(loadedTeams);
          if (loadedTeams.length === 1) {
            setSelectedTeam(loadedTeams[0].id);
          } else if (loadedTeams.length > 1) {
            setSelectedTeam(loadedTeams[0].id); // Automatisch erstes Team wählen
          }
        })
        .catch(() => setTeams([]));
    }
  }, [open]);

  useEffect(() => {
    if (open && formationId) {
      setLoading(true);
      apiJson<any>(`/formation/${formationId}/edit`)
        .then(data => {
          // Formation-Daten
          const f = data.formation;
          setFormation(f);
          setName(f.name);
          const loadedPlayers = Array.isArray(f.formationData?.players)
            ? f.formationData.players.map((p: any) => ({ ...p, id: p.id ?? Date.now() + Math.random() }))
            : [];
          setPlayers(loadedPlayers);
          setNextPlayerNumber(
            loadedPlayers.length > 0
              ? Math.max(...loadedPlayers.map(p => typeof p.number === 'number' ? p.number : 0)) + 1
              : 1
          );
          if (Array.isArray(data.availablePlayers?.players)) {
            setAvailablePlayers(
              data.availablePlayers.players.map((entry: any) => ({
                id: entry.player.id,
                name: entry.player.name,
                shirtNumber: entry.shirtNumber
              }))
            );
          } else {
            setAvailablePlayers([]);
          }
        })
        .catch(err => setError(err.message || 'Fehler beim Laden'))
        .finally(() => setLoading(false));
    } else if (open && !formationId) {
      setFormation(null);
      setName('');
      setPlayers([]);
      setNextPlayerNumber(1);
      setAvailablePlayers([]);
      setSelectedTeam('');
    }
  }, [open, formationId]);

  useEffect(() => {
    if (open && selectedTeam) {
      apiJson<any>(`/formation/team/${selectedTeam}/players`)
        .then(data => {
          // Debug-Ausgabe
          // eslint-disable-next-line no-console
          console.log('Geladene Spieler vom Backend:', data);
          if (Array.isArray(data.players)) {
            // Backend liefert direkt ein Array von Spielern
            const mapped = data.players
              .filter((entry: any) => entry && entry.id)
              .map((entry: any) => ({
                id: entry.id,
                name: entry.name,
                shirtNumber: entry.shirtNumber
              }));
            setAvailablePlayers(mapped);
            setError(mapped.length === 0 ? 'Keine Spieler gefunden' : null);
          } else {
            setAvailablePlayers([]);
            setError('Keine Spieler gefunden');
          }
        })
        .catch((err) => {
          setAvailablePlayers([]);
          if (err && err.message) {
            setError(err.message);
          } else {
            setError('Fehler beim Laden der Spieler');
          }
        });
    } else if (open && !selectedTeam) {
      setAvailablePlayers([]);
    }
  }, [open, selectedTeam, teams]);

  // Drag&Drop-Handler
  const handlePlayerMouseDown = (id: number) => (e: React.MouseEvent) => {
    setDraggedPlayerId(id);
    e.stopPropagation();
  };

  const handlePitchMouseMove = (e: React.MouseEvent) => {
    if (draggedPlayerId !== null && pitchRef.current) {
      const rect = pitchRef.current.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width) * 100;
      const y = ((e.clientY - rect.top) / rect.height) * 100;
      setPlayers(players => players.map(p => p.id === draggedPlayerId ? { ...p, x, y } : p));
    }
  };

  const handlePitchMouseUp = () => {
    setDraggedPlayerId(null);
  };

  // Spieler hinzufügen
  const addGenericPlayer = () => {
    const position = findFreePosition();
    setPlayers(players => [
      ...players,
      {
        id: Date.now(),
        x: position.x,
        y: position.y,
        number: nextPlayerNumber,
        name: `Spieler ${nextPlayerNumber}`,
        playerId: null,
        isRealPlayer: false,
      },
    ]);
    setNextPlayerNumber(n => n + 1);
  };

  // Freie Position finden
  const findFreePosition = () => {
    // Einfache Gitter-Logik wie im Original
    const gridSize = 15;
    const startX = 15;
    const startY = 15;
    for (let row = 0; row < 5; row++) {
      for (let col = 0; col < 5; col++) {
        const x = startX + col * gridSize;
        const y = startY + row * gridSize;
        if (!players.some(p => Math.abs(p.x - x) < 5 && Math.abs(p.y - y) < 5)) {
          return { x, y };
        }
      }
    }
    return { x: 20 + Math.random() * 60, y: 20 + Math.random() * 60 };
  };

  // Spieler entfernen
  const removePlayer = (id: number) => {
    setPlayers(players => players.filter(p => p.id !== id));
  };

  // Spieler aus verfügbarer Liste zur Formation hinzufügen
  const addPlayerToFormation = (player: Player) => {
    if (players.some(p => p.playerId === player.id)) return;
    const position = findFreePosition();
    setPlayers(players => [
      ...players,
      {
        id: Date.now(),
        x: position.x,
        y: position.y,
        number: player.shirtNumber || nextPlayerNumber,
        name: player.name,
        playerId: player.id,
        isRealPlayer: true,
      },
    ]);
    setNextPlayerNumber(n => n + 1);
  };

  // Speichern
  const handleSave = async () => {
    setLoading(true);
    setError(null);
    try {
      // Sende ausschließlich die aktuellen State-Werte!
      // formationData muss alle Felder enthalten (players, code, timestamp, ...)
      const formationData = {
        ...(formation?.formationData || {}),
        players,
      };
      const payload: any = {
        name,
        team: selectedTeam,
        formationData,
      };
      let url = '/formation/new';
      let method: 'POST' | 'PUT' = 'POST';
      if (formationId) {
        url = `/formation/${formationId}/edit`;
        method = 'POST';
      }
      const response = await apiJson(url, {
        method,
        body: payload,
      });
      if (response && typeof response === 'object' && response.error) {
        setError(response.error);
        setLoading(false);
        return;
      }
      showToast('Formation erfolgreich gespeichert!', 'success');
      // Versuche, die Formation aus der Backend-Response zu holen
      let savedFormation = response && response.formation ? response.formation : null;
      if (!savedFormation) {
        // Fallback: baue Formation aus aktuellem State (nur für neue Formation)
        savedFormation = {
          id: response && response.id ? response.id : Math.random(),
          name,
          formationType: {
            name: formation?.formationType?.name || 'Fußball',
            cssClass: formation?.formationType?.cssClass || '',
            backgroundPath: formation?.formationType?.backgroundPath || '',
          },
          formationData: { ...((formation && formation.formationData) || {}), players },
        };
      }
      onSaved?.(savedFormation);
      onClose();
    } catch (err: any) {
      // Fehlertext aus Error-Objekt oder Response anzeigen
      if (err && typeof err === 'object' && err.message) {
        setError(err.message);
      } else if (typeof err === 'string') {
        setError(err);
      } else {
        setError('Fehler beim Speichern');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>{formationId ? 'Aufstellung bearbeiten' : 'Neue Aufstellung'}</DialogTitle>
      <DialogContent>
  {loading && <CircularProgress />}
  {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
  {/* Toast wird global über ToastContext angezeigt */}
        <Box display="flex" gap={3}>
          <Box flex={2}>
            <Box display="flex" gap={2} mb={2} mt={1}>
              <TextField
                label="Name der Aufstellung"
                value={name}
                onChange={e => setName(e.target.value)}
                fullWidth
                required
              />
              <TextField
                label="Team"
                select
                value={selectedTeam}
                onChange={e => setSelectedTeam(Number(e.target.value))}
                fullWidth
                required
              >
                {Array.isArray(teams) && teams.length > 0
                  ? teams.map(team => (
                      <MenuItem key={team.id} value={team.id}>{team.name}</MenuItem>
                    ))
                  : <MenuItem value="" disabled>Keine Teams verfügbar</MenuItem>
                }
              </TextField>
            </Box>
            <Box
              ref={pitchRef}
              className={`pitch formation-background sports-field editable ${formation?.formationType.cssClass || 'field-default'}`}
              sx={{
                width: '100%',
                height: 340,
                backgroundImage: `url(/images/formation/${formation?.formationType.backgroundPath || 'fussballfeld_haelfte.jpg'})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundRepeat: 'no-repeat',
                position: 'relative',
                mb: 2,
                cursor: draggedPlayerId ? 'grabbing' : 'default',
              }}
              data-background-image={formation?.formationType.backgroundPath || 'fussballfeld_haelfte.jpg'}
              onMouseMove={handlePitchMouseMove}
              onMouseUp={handlePitchMouseUp}
            >
              {players.map((player) => (
                <Box
                  key={player.id}
                  sx={{
                    position: 'absolute',
                    left: `${player.x}%`,
                    top: `${player.y}%`,
                    width: 32,
                    height: 32,
                    bgcolor: player.isRealPlayer ? 'primary.main' : 'grey.500',
                    color: 'white',
                    borderRadius: '50%',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontWeight: 700,
                    fontSize: 18,
                    border: '2px solid #fff',
                    boxShadow: 2,
                    transform: 'translate(-50%, -50%)',
                    cursor: 'grab',
                    userSelect: 'none',
                  }}
                  onMouseDown={handlePlayerMouseDown(player.id)}
                >
                  {player.number}
                </Box>
              ))}
            </Box>
            <Box display="flex" gap={2} mb={2}>
              <Button variant="outlined" startIcon={<AddIcon />} onClick={addGenericPlayer}>Generischen Spieler hinzufügen</Button>
            </Box>
          </Box>
          <Box flex={1}>
            <Typography variant="subtitle1" mb={1}>Verfügbare Spieler</Typography>
            {error === 'Keine Spieler gefunden' && (
              <Alert severity="info" sx={{ mb: 1 }}>Keine Spieler für dieses Team gefunden.</Alert>
            )}
            <List dense>
              {availablePlayers.map(player => (
                <ListItem key={player.id} disablePadding secondaryAction={
                  <Button size="small" variant="outlined" onClick={() => addPlayerToFormation(player)} disabled={players.some(p => p.playerId === player.id)}>
                    Hinzufügen
                  </Button>
                }>
                  <ListItemText primary={player.name} secondary={player.shirtNumber ? `#${player.shirtNumber}` : ''} />
                </ListItem>
              ))}
            </List>
            <Typography variant="subtitle1" mb={1} mt={3}>Spielerliste</Typography>
            <List dense>
              {players.map(player => (
                <ListItem key={player.id}>
                  <ListItemText primary={player.name} secondary={player.isRealPlayer ? `#${player.number}` : 'Generisch'} />
                  <IconButton size="small" onClick={() => removePlayer(player.id)}><DeleteIcon fontSize="small" /></IconButton>
                </ListItem>
              ))}
            </List>
          </Box>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} variant="outlined">Abbrechen</Button>
        <Button onClick={handleSave} variant="contained" disabled={loading}>Speichern</Button>
      </DialogActions>
    </Dialog>
  );
};

export default FormationEditModal;
