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
import PlayerDetailsModal from '../modals/PlayerDetailsModal';
import PlayerDeleteConfirmationModal from '../modals/PlayerDeleteConfirmationModal';
import PlayerEditModal from '../modals/PlayerEditModal';
import { Player } from '../types/player';
import { PlayerClubAssignment } from '../types/playerClubAssignment';
import { PlayerTeamAssignment } from '../types/playerTeamAssignment';
{/*import { PlayerLicenseAssignment } from '../types/playerLicenseAssignment';*/}
import { PlayerNationalityAssignment } from '../types/playerNationalityAssignment';

const Players = () => {
  const [players, setPlayers] = useState<Player[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [playerId, setPlayerId] = useState<number | null>(null);
  const [playerDetailsModalOpen, setPlayerDetailsModalOpen] = useState(false);
  const [playerEditModalOpen, setPlayerEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deletePlayer, setDeletePlayer] = useState<Player | null>(null);

  const loadPlayers = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ players: Player[] }>('/api/players');
      setPlayers(res.players);
    } catch (e) {
      setError('Fehler beim Laden der Spieler.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPlayers();
  }, []);

  const handleDelete = async (playerId: number) => {
    try {
      await apiJson(`/api/players/${playerId}`, { method: 'DELETE' });
      setPlayers(players => players.filter(p => p.id !== playerId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen des Spielers.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Spieler
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setPlayerId(null); setPlayerEditModalOpen(true) }}>
          Neuen Spieler erstellen
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
                <TableCell>Verein</TableCell>
                <TableCell>Teams</TableCell>
{/*                <TableCell>Licenses</TableCell>  */}
                <TableCell>Nationalities</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {players.map((player, idx) => (
                <TableRow key={player.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setPlayerId(player.id);
                      setPlayerDetailsModalOpen(true);
                    }}
                  >
                    {player.firstName || ''} {player.lastName || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setPlayerId(player.id);
                      setPlayerDetailsModalOpen(true);
                    }}
                  >
                    {player.clubAssignments.map((playerClubAssignment: PlayerClubAssignment) => playerClubAssignment.club.name).join(', ') || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setPlayerId(player.id);
                      setPlayerDetailsModalOpen(true);
                    }}
                  >
                    {player.teamAssignments.length > 0
                      ? player.teamAssignments.map((playerTeamAssignment: PlayerTeamAssignment) => (
                          <div key={playerTeamAssignment.id}>
                            {playerTeamAssignment.team.name} ({playerTeamAssignment.shirtNumber}) - {playerTeamAssignment.team.ageGroup.name}
                            {playerTeamAssignment.type?.name ? ` (${playerTeamAssignment.type?.name})` : ''}
                          </div>
                        ))
                      : ''
                    }
                  </TableCell>
{/*
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setPlayerId(player.id);
                      setPlayerDetailsModalOpen(true);
                    }}
                  >
                    {player.licenseAssignments.map((playerLicenseAssignment: PlayerLicenseAssignment) => playerLicenseAssignment.license.name).join(', ') || ''}
                  </TableCell>
*/}
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setPlayerId(player.id);
                      setPlayerDetailsModalOpen(true);
                    }}
                  >
                    {player.nationalityAssignments.map((playerNationalityAssignment: PlayerNationalityAssignment) => playerNationalityAssignment.nationality.name).join(', ') || ''}
                  </TableCell>

                  <TableCell>
                    { player.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setPlayerId(player.id);
                          setPlayerEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Formation löschen"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { player.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeletePlayer(player);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Formation löschen"
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
      <PlayerDetailsModal
        open={playerDetailsModalOpen}
        loadPlayers={() => loadPlayers()}
        playerId={playerId}
        onClose={() => setPlayerDetailsModalOpen(false)}
      />
      <PlayerEditModal
        openPlayerEditModal={playerEditModalOpen}
        playerId={playerId}
        onPlayerEditModalClose={() => setPlayerEditModalOpen(false)}
        onPlayerSaved={(savedPlayer) => {
          setPlayerEditModalOpen(false);
          loadPlayers();
        }}
      />
      <PlayerDeleteConfirmationModal
        open={deleteModalOpen}
        playerName={deletePlayer?.firstName + " " + deletePlayer?.lastName}
        onClose={() => setDeleteModalOpen(false)}
        onConfirm={async () => handleDelete(deletePlayer.id) }
      />
    </Box>
  );
};

export default Players;
