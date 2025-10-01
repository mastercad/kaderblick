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
import ClubDetailsModal from '../modals/ClubDetailsModal';
import ClubDeleteConfirmationModal from '../modals/ClubDeleteConfirmationModal';
import ClubEditModal from '../modals/ClubEditModal';
import { Club } from '../types/club';

interface ClubResponseProps {
  clubs: Club[]
}

const Clubs = () => {
  const [clubs, setClubs] = useState<Club[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [clubId, setClubId] = useState<number | null>(null);
  const [clubDetailsModalOpen, setClubDetailsModalOpen] = useState(false);
  const [clubEditModalOpen, setClubEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteClub, setDeleteClub] = useState<Club | null>(null);

  const loadClubs = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ clubs: ClubResponseProps[] }>('/clubs');
      if (res && typeof res === 'object') {
        // Die eigentlichen Einträge stehen unter numerischen Keys
        const clubList = Object.keys(res)
          .filter(key => /^\d+$/.test(key))
          .map(key => res[key].club);
        setClubs(clubList);
      } else {
        setClubs([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Vereine.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadClubs();
  }, []);

  const handleDelete = async (clubId: number) => {
    try {
      await apiJson(`/clubs/${clubId}/delete`, { method: 'DELETE' });
      setClubs(clubs => clubs.filter(c => c.id !== clubId));
    } catch (e) {
      alert('Fehler beim Löschen des Vereins.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Vereine
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setClubId(null); setClubEditModalOpen(true) }}>
          Neuen Verein erstellen
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
                <TableCell>Stadion</TableCell>
                <TableCell>Website</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {clubs.map((club, idx) => (
                <TableRow key={club.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setClubId(club.id);
                      setClubDetailsModalOpen(true);
                    }}
                  >
                    {club.name || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setClubId(club.id);
                      setClubDetailsModalOpen(true);
                    }}
                  >
                    {club.stadiumName || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setClubId(club.id);
                      setClubDetailsModalOpen(true);
                    }}
                  >
                    {club.website || ''}
                  </TableCell>
                  <TableCell>
                    { club.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setClubId(club.id);
                          setClubEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Formation löschen"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { club.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteClub(club);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Formation löschen"
                      >
                        <DeleteIcon />
                      </IconButton>
                    )}
                  </TableCell>
                  { club.permissions?.canDelete && (
                  <ClubDeleteConfirmationModal
                    open={deleteModalOpen}
                    clubName={deleteClub?.name}
                    onClose={() => setDeleteModalOpen(false)}
                    onConfirm={async () => handleDelete(deleteClub.id) }
                  />
                  )}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      )}
      <ClubDetailsModal
        open={clubDetailsModalOpen}
        loadClubs={() => loadClubs()}
        clubId={clubId}
        onClose={() => setClubDetailsModalOpen(false)}
      />
      <ClubEditModal
        openClubEditModal={clubEditModalOpen}
        clubId={clubId}
        onClubEditModalClose={() => setClubEditModalOpen(false)}
        onClubSaved={(savedClub) => {
          setClubEditModalOpen(false);
          loadClubs();
        }}
      />
    </Box>
  );
};

export default Clubs;
