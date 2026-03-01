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
import CoachDetailsModal from '../modals/CoachDetailsModal';
import CoachDeleteConfirmationModal from '../modals/CoachDeleteConfirmationModal';
import CoachEditModal from '../modals/CoachEditModal';
import { Coach } from '../types/coach';
import { CoachClubAssignment } from '../types/coachClubAssignment';
import { CoachTeamAssignment } from '../types/coachTeamAssignment';
import { CoachLicenseAssignment } from '../types/coachLicenseAssignment';
import { CoachNationalityAssignment } from '../types/coachNationalityAssignment';

const Coaches = () => {
  const [coaches, setCoaches] = useState<Coach[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [coachId, setCoachId] = useState<number | null>(null);
  const [coachDetailsModalOpen, setCoachDetailsModalOpen] = useState(false);
  const [coachEditModalOpen, setCoachEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteCoach, setDeleteCoach] = useState<Coach | null>(null);

  const loadCoaches = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ coaches: Coach[] }>('/api/coaches');
      setCoaches(res.coaches);
    } catch (e) {
      setError('Fehler beim Laden der Trainer.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCoaches();
  }, []);

  const handleDelete = async (coachId: number) => {
    try {
      await apiJson(`/api/coaches/${coachId}`, { method: 'DELETE' });
      setCoaches(coaches => coaches.filter(c => c.id !== coachId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen des Trainers.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Trainer
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setCoachId(null); setCoachEditModalOpen(true) }}>
          Neuen Trainer erstellen
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
                <TableCell>Licenses</TableCell>
                <TableCell>Nationalities</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {coaches.map((coach, idx) => (
                <TableRow key={coach.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setCoachId(coach.id);
                      setCoachDetailsModalOpen(true);
                    }}
                  >
                    {coach.firstName || ''} {coach.lastName || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setCoachId(coach.id);
                      setCoachDetailsModalOpen(true);
                    }}
                  >
                    {coach.clubAssignments.length > 0
                      ? coach.clubAssignments.map((coachClubAssignment: CoachClubAssignment) => (
                          <div key={coachClubAssignment.id}>{coachClubAssignment.club.name}</div>
                        ))
                      : ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setCoachId(coach.id);
                      setCoachDetailsModalOpen(true);
                    }}
                  >
                    {coach.teamAssignments.length > 0
                      ? coach.teamAssignments.map((coachTeamAssignment: CoachTeamAssignment) => (
                          <div key={coachTeamAssignment.id}>
                            {coachTeamAssignment.team.name}
                            {coachTeamAssignment.team.type?.name ? ` (${coachTeamAssignment.team.type?.name})` : ''}
                          </div>
                        ))
                      : ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setCoachId(coach.id);
                      setCoachDetailsModalOpen(true);
                    }}
                  >
                    {coach.licenseAssignments.length > 0
                      ? coach.licenseAssignments.map((coachLicenseAssignment: CoachLicenseAssignment) => (
                          <div key={coachLicenseAssignment.id}>{coachLicenseAssignment.license.name}</div>
                        ))
                      : ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setCoachId(coach.id);
                      setCoachDetailsModalOpen(true);
                    }}
                  >
                    {coach.nationalityAssignments.length > 0
                      ? coach.nationalityAssignments.map((coachNationalityAssignment: CoachNationalityAssignment) => (
                          <div key={coachNationalityAssignment.id}>{coachNationalityAssignment.nationality.name}</div>
                        ))
                      : ''}
                  </TableCell>
                  <TableCell>
                    { coach.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setCoachId(coach.id);
                          setCoachEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Formation löschen"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { coach.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteCoach(coach);
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
      <CoachDetailsModal
        open={coachDetailsModalOpen}
        loadCoaches={() => loadCoaches()}
        coachId={coachId}
        onClose={() => setCoachDetailsModalOpen(false)}
      />
      <CoachEditModal
        openCoachEditModal={coachEditModalOpen}
        coachId={coachId}
        onCoachEditModalClose={() => setCoachEditModalOpen(false)}
        onCoachSaved={(savedCoach) => {
          setCoachEditModalOpen(false);
          loadCoaches();
        }}
      />
      <CoachDeleteConfirmationModal
        open={deleteModalOpen}
        coachName={deleteCoach?.firstName + " " + deleteCoach?.lastName}
        onClose={() => setDeleteModalOpen(false)}
        onConfirm={async () => handleDelete(deleteCoach!.id) }
      />
    </Box>
  );
};

export default Coaches;
