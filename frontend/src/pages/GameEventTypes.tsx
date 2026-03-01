import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';
import CircularProgress from '@mui/material/CircularProgress';
import Stack from '@mui/material/Stack';
import IconButton from '@mui/material/IconButton';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import { apiJson } from '../utils/api';
import GameEventTypeDeleteConfirmationModal from '../modals/GameEventTypeDeleteConfirmationModal';
import GameEventTypeEditModal from '../modals/GameEventTypeEditModal';
import { GameEventType } from '../types/gameEventType';
import { getGameEventIconByCode } from '../constants/gameEventIcons';

const GameEventTypes = () => {
  const [gameEventTypes, setGameEventTypes] = useState<GameEventType[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [gameEventTypeId, setGameEventTypeId] = useState<number | null>(null);
  const [gameEventTypeEditModalOpen, setGameEventTypeEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteGameEventType, setDeleteGameEventType] = useState<GameEventType | null>(null);

  const loadGameEventTypes = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ gameEventTypes: GameEventType[] }>('/api/game-event-types');
      if (res && Array.isArray(res.gameEventTypes)) {
        setGameEventTypes(res.gameEventTypes);
      } else {
        setGameEventTypes([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Spielereignistypen.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadGameEventTypes();
  }, []);

  const handleDelete = async (gameEventTypeId: number) => {
    try {
      await apiJson(`/api/game-event-types/${gameEventTypeId}`, { method: 'DELETE' });
      setGameEventTypes(gameEventTypes => gameEventTypes.filter(c => c.id !== gameEventTypeId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Spielereignistypen.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Spielereignistypen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setGameEventTypeId(null); setGameEventTypeEditModalOpen(true) }}>
          Neuen Spielereignistyp erstellen
        </Button>
      </Stack>
      {loading ? (
        <Box display="flex" justifyContent="center" alignItems="center" minHeight={200}>
          <CircularProgress />
        </Box>
      ) : error ? (
        <Typography color="error">{error}</Typography>
      ) : (
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Name</TableCell>
                <TableCell>Code</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {gameEventTypes.map((gameEventType, idx) => (
                <TableRow key={gameEventType.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ color: gameEventType.color }}>
                    {
                      getGameEventIconByCode(gameEventType.icon)
                    }
                    {gameEventType.name}
                  </TableCell>
                  <TableCell>
                    {gameEventType.code || ''}
                  </TableCell>
                  <TableCell>
                    { gameEventType.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setGameEventTypeId(gameEventType.id);
                          setGameEventTypeEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Spielereignistyp bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { gameEventType.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteGameEventType(gameEventType);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Spielereignistyp löschen"
                      >
                        <DeleteIcon />
                      </IconButton>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
        )}
        <GameEventTypeEditModal
            openGameEventTypeEditModal={gameEventTypeEditModalOpen}
            gameEventTypeId={gameEventTypeId}
            onGameEventTypeEditModalClose={() => setGameEventTypeEditModalOpen(false)}
            onGameEventTypeSaved={(savedGameEventType) => {
                setGameEventTypeEditModalOpen(false);
                loadGameEventTypes();
            }}
        />
        <GameEventTypeDeleteConfirmationModal
            open={deleteModalOpen}
            gameEventTypeName={deleteGameEventType?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteGameEventType!.id) }
        />
    </Box>
  );
};

export default GameEventTypes;
