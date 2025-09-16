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
import CoachLicenseDeleteConfirmationModal from '../modals/CoachLicenseDeleteConfirmationModal';
import CoachLicenseEditModal from '../modals/CoachLicenseEditModal';
import { CoachLicense } from '../types/coachLicense';

const CoachLicenses = () => {
  const [coachLicenses, setCoachLicenses] = useState<CoachLicense[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [coachLicenseId, setCoachLicenseId] = useState<number | null>(null);
  const [coachLicenseEditModalOpen, setCoachLicenseEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteCoachLicense, setDeleteCoachLicense] = useState<CoachLicense | null>(null);

  const loadCoachLicenses = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ coachLicenses: CoachLicense[] }>('/api/coach-licenses');
      if (res && Array.isArray(res.coachLicenses)) {
        setCoachLicenses(res.coachLicenses);
      } else {
        setCoachLicenses([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Trainerlizenzen.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCoachLicenses();
  }, []);

  const handleDelete = async (coachLicenseId: number) => {
    try {
      await apiJson(`/api/coach-licenses/${coachLicenseId}`, { method: 'DELETE' });
      setCoachLicenses(coachLicenses => coachLicenses.filter(c => c.id !== coachLicenseId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Trainerlizenzen.');
    }
  };

  return (
    <Box sx={{mx: 'auto', mt: 4, p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Trainerlizenzen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setCoachLicenseId(null); setCoachLicenseEditModalOpen(true) }}>
          Neue Trainerlizenz erstellen
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
                <TableCell>Beschreibung</TableCell>
                <TableCell>Länder Code</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {coachLicenses.map((coachLicense, idx) => (
                <TableRow key={coachLicense.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {coachLicense.name || ''}
                  </TableCell>
                  <TableCell>
                    {coachLicense.description || ''}
                  </TableCell>
                  <TableCell>
                    { coachLicense.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setCoachLicenseId(coachLicense.id);
                          setCoachLicenseEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Oberflächenart bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { coachLicense.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteCoachLicense(coachLicense);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Trainerlizenz löschen"
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
        <CoachLicenseEditModal
            openCoachLicenseEditModal={coachLicenseEditModalOpen}
            coachLicenseId={coachLicenseId}
            onCoachLicenseEditModalClose={() => setCoachLicenseEditModalOpen(false)}
            onCoachLicenseSaved={(savedCoachLicense) => {
                setCoachLicenseEditModalOpen(false);
                loadCoachLicenses();
            }}
        />
        <CoachLicenseDeleteConfirmationModal
            open={deleteModalOpen}
            coachLicenseName={deleteCoachLicense?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteCoachLicense.id) }
        />
    </Box>
  );
};

export default CoachLicenses;
