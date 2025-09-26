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
import LeagueDeleteConfirmationModal from '../modals/LeagueDeleteConfirmationModal';
import LeagueEditModal from '../modals/LeagueEditModal';
import { League } from '../types/league';

const Leagues = () => {
  const [leagues, setLeagues] = useState<League[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [leagueId, setLeagueId] = useState<number | null>(null);
  const [leagueEditModalOpen, setLeagueEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteLeague, setDeleteLeague] = useState<League | null>(null);

  const loadLeagues = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ leagues: League[] }>('/api/leagues');
      if (res && Array.isArray(res.leagues)) {
        setLeagues(res.leagues);
      } else {
        setLeagues([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der starken Füße.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadLeagues();
  }, []);

  const handleDelete = async (leagueId: number) => {
    try {
      await apiJson(`/api/leagues/${leagueId}`, { method: 'DELETE' });
      setLeagues(leagues => leagues.filter(c => c.id !== leagueId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Liga.');
    }
  };

  return (
    <Box sx={{mx: 'auto', mt: 4, p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Ligen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setLeagueId(null); setLeagueEditModalOpen(true) }}>
          Neue Liga erstellen
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
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {leagues.map((league, idx) => (
                <TableRow key={league.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {league.name || ''}
                  </TableCell>
                  <TableCell>
                    { league.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setLeagueId(league.id);
                          setLeagueEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Starken Fuß bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { league.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteLeague(league);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Starken Fuß löschen"
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
        <LeagueEditModal
            openLeagueEditModal={leagueEditModalOpen}
            leagueId={leagueId}
            onLeagueEditModalClose={() => setLeagueEditModalOpen(false)}
            onLeagueSaved={(savedLeague) => {
                setLeagueEditModalOpen(false);
                loadLeagues();
            }}
        />
        <LeagueDeleteConfirmationModal
            open={deleteModalOpen}
            leagueName={deleteLeague?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteLeague.id) }
        />
    </Box>
  );
};

export default Leagues;
