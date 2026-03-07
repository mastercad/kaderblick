import React from 'react';
import {
  Box, Typography, TextField, Button, Divider,
  List, ListItem, ListItemText, IconButton, Tooltip,
  InputAdornment,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import AddIcon from '@mui/icons-material/AddCircle';
import DeleteIcon from '@mui/icons-material/Delete';
import type { Player, PlayerData } from '../types';

interface SquadListProps {
  // Kader (all available players for the team)
  availablePlayers: Player[];
  searchQuery: string;
  onSearchChange: (q: string) => void;
  activePlayerIds: Set<number | null | undefined>;
  onAddToField: (player: Player) => void;
  onAddToBench: (player: Player) => void;
  onAddGeneric: () => void;

  // Startelf (players currently on the pitch)
  fieldPlayers: PlayerData[];
  onRemoveFromField: (id: number) => void;
  onSendToBench: (id: number) => void;

  // Taktische Notizen
  notes: string;
  onNotesChange: (v: string) => void;
}

/**
 * Right-hand panel of the formation editor.
 * Sections: Kader (squad list), Startelf (lineup), Taktische Notizen.
 */
const SquadList: React.FC<SquadListProps> = ({
  availablePlayers, searchQuery, onSearchChange, activePlayerIds,
  onAddToField, onAddToBench, onAddGeneric,
  fieldPlayers, onRemoveFromField, onSendToBench,
  notes, onNotesChange,
}) => {
  const filtered = availablePlayers.filter(p =>
    p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    String(p.shirtNumber ?? '').includes(searchQuery),
  );

  return (
    <Box
      flex={1}
      minWidth={200}
      display="flex"
      flexDirection="column"
      gap={1}
      sx={{ maxHeight: { xs: 320, md: 580 }, overflowY: 'auto', pr: 0.5 }}
    >
      {/* ── Kader ─────────────────────────────────────────────────────────────── */}
      <Typography variant="subtitle2" fontWeight={700}>Kader</Typography>

      <TextField
        size="small"
        placeholder="Spieler suchen…"
        value={searchQuery}
        onChange={e => onSearchChange(e.target.value)}
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <SearchIcon fontSize="small" />
            </InputAdornment>
          ),
        }}
      />

      <List dense disablePadding>
        {filtered.map(player => {
          const isActive = activePlayerIds.has(player.id);
          return (
            <ListItem
              key={player.id}
              disablePadding
              sx={{ opacity: isActive ? 0.4 : 1, py: 0.2 }}
              secondaryAction={
                <Box display="flex" gap={0.5}>
                  <Button
                    size="small" variant="outlined"
                    sx={{ minWidth: 44, fontSize: '0.7rem', px: 0.75 }}
                    onClick={() => onAddToField(player)}
                    disabled={isActive}
                  >
                    Feld
                  </Button>
                  <Button
                    size="small" variant="outlined" color="secondary"
                    sx={{ minWidth: 44, fontSize: '0.7rem', px: 0.75 }}
                    onClick={() => onAddToBench(player)}
                    disabled={isActive}
                  >
                    Bank
                  </Button>
                </Box>
              }
            >
              <ListItemText
                primary={player.name}
                secondary={player.shirtNumber ? `#${player.shirtNumber}` : undefined}
                primaryTypographyProps={{ variant: 'body2' }}
              />
            </ListItem>
          );
        })}
        {filtered.length === 0 && (
          <Typography variant="caption" color="text.secondary" sx={{ px: 1, display: 'block', py: 0.5 }}>
            {searchQuery ? 'Kein Ergebnis' : 'Wähle ein Team, um Spieler zu sehen'}
          </Typography>
        )}
      </List>

      <Button
        size="small" variant="text" startIcon={<AddIcon />}
        onClick={onAddGeneric}
        sx={{ alignSelf: 'flex-start', fontSize: '0.75rem' }}
      >
        Platzhalter hinzufügen
      </Button>

      <Divider />

      {/* ── Startelf ──────────────────────────────────────────────────────────── */}
      {fieldPlayers.length > 0 && (
        <>
          <Typography variant="subtitle2" fontWeight={700}>
            Startelf ({fieldPlayers.length})
          </Typography>
          <List dense disablePadding>
            {fieldPlayers.map(player => (
              <ListItem
                key={player.id}
                disablePadding
                sx={{ py: 0.2 }}
                secondaryAction={
                  <Box display="flex" gap={0.25}>
                    <Tooltip title="Auf die Bank setzen">
                      <Button
                        size="small"
                        sx={{ minWidth: 40, fontSize: '0.65rem', px: 0.5 }}
                        onClick={() => onSendToBench(player.id)}
                      >
                        Bank
                      </Button>
                    </Tooltip>
                    <IconButton size="small" onClick={() => onRemoveFromField(player.id)} sx={{ p: 0.25 }}>
                      <DeleteIcon fontSize="small" />
                    </IconButton>
                  </Box>
                }
              >
                <ListItemText
                  primary={player.name}
                  secondary={`#${player.number}${player.position ? ` · ${player.position}` : ''}`}
                  primaryTypographyProps={{ variant: 'body2' }}
                />
              </ListItem>
            ))}
          </List>
          <Divider />
        </>
      )}

      {/* ── Taktische Notizen ─────────────────────────────────────────────────── */}
      <Typography variant="subtitle2" fontWeight={700}>Taktische Notizen</Typography>
      <TextField
        multiline rows={4} size="small" fullWidth
        placeholder="z.B. Hoch pressen, Umschalten über rechts, Gegner hat schwache linke Seite…"
        value={notes}
        onChange={e => onNotesChange(e.target.value)}
      />
    </Box>
  );
};

export default SquadList;
