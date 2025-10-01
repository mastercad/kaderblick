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
import TeamDetailsModal from '../modals/TeamDetailsModal';
import TeamDeleteConfirmationModal from '../modals/TeamDeleteConfirmationModal';
import TeamEditModal from '../modals/TeamEditModal';
import { Team } from '../types/team';

interface TeamResponseProps {
  teams: Team[]
}

const Teams = () => {
  const [teams, setTeams] = useState<Team[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [teamId, setTeamId] = useState<number | null>(null);
  const [teamDetailsModalOpen, setTeamDetailsModalOpen] = useState(false);
  const [teamEditModalOpen, setTeamEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteTeam, setDeleteTeam] = useState<Team | null>(null);

  const loadTeams = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ teams: TeamResponseProps[] }>('/api/teams/list');
      setTeams(res.teams);
    } catch (e) {
      setError('Fehler beim Laden der Teams.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTeams();
  }, []);

  const handleDelete = async (teamId: number) => {
    try {
      await apiJson(`/api/teams/${teamId}/delete`, { method: 'DELETE' });
      setTeams(teams => teams.filter(c => c.id !== teamId));
    } catch (e) {
      alert('Fehler beim Löschen des Teams.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Teams
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setTeamId(null); setTeamEditModalOpen(true) }}>
          Neues Team erstellen
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
                <TableCell>Altersgruppe</TableCell>
                <TableCell>Liga</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {teams.map((team, idx) => (
                <TableRow key={team.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setTeamId(team.id);
                      setTeamDetailsModalOpen(true);
                    }}
                  >
                    {team.name || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setTeamId(team.id);
                      setTeamDetailsModalOpen(true);
                    }}
                  >
                    {team.ageGroup.name || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setTeamId(team.id);
                      setTeamDetailsModalOpen(true);
                    }}
                  >
                    {team.league.name || ''}
                  </TableCell>
                  <TableCell>
                    { team.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setTeamId(team.id);
                          setTeamEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Team bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { team.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteTeam(team);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Team löschen"
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
      <TeamDeleteConfirmationModal
        open={deleteModalOpen}
        teamName={deleteTeam?.name}
        onClose={() => setDeleteModalOpen(false)}
        onConfirm={async () => handleDelete(deleteTeam.id) }
      />
      <TeamDetailsModal
        open={teamDetailsModalOpen}
        loadTeams={() => loadTeams()}
        teamId={teamId}
        onClose={() => setTeamDetailsModalOpen(false)}
      />
      <TeamEditModal
        openTeamEditModal={teamEditModalOpen}
        teamId={teamId}
        onTeamEditModalClose={() => setTeamEditModalOpen(false)}
        onTeamSaved={(savedTeam) => {
          setTeamEditModalOpen(false);
          loadTeams();
        }}
      />
    </Box>
  );
};

export default Teams;
