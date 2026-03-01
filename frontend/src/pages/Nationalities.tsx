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
import NationalityDeleteConfirmationModal from '../modals/NationalityDeleteConfirmationModal';
import NationalityEditModal from '../modals/NationalityEditModal';
import { Nationality } from '../types/nationality';

const Nationalities = () => {
  const [nationalities, setNationalities] = useState<Nationality[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [nationalityId, setNationalityId] = useState<number | null>(null);
  const [nationalityEditModalOpen, setNationalityEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteNationality, setDeleteNationality] = useState<Nationality | null>(null);

  const loadNationalities = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ nationalities: Nationality[] }>('/api/nationalities');
      if (res && Array.isArray(res.nationalities)) {
        setNationalities(res.nationalities);
      } else {
        setNationalities([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Nationalitäten.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadNationalities();
  }, []);

  const handleDelete = async (nationalityId: number) => {
    try {
      await apiJson(`/api/nationalities/${nationalityId}`, { method: 'DELETE' });
      setNationalities(nationalities => nationalities.filter(c => c.id !== nationalityId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Nationalitäten.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Nationalitäten
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setNationalityId(null); setNationalityEditModalOpen(true) }}>
          Neue Nationalität erstellen
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
                <TableCell>ISO Code</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {nationalities.map((nationality, idx) => (
                <TableRow key={nationality.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {nationality.name || ''}
                  </TableCell>
                  <TableCell>
                    {nationality.isoCode || ''}
                  </TableCell>
                  <TableCell>
                    { nationality.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setNationalityId(nationality.id);
                          setNationalityEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Nationalität bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { nationality.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteNationality(nationality);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Nationalität löschen"
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
        <NationalityEditModal
            openNationalityEditModal={nationalityEditModalOpen}
            nationalityId={nationalityId}
            onNationalityEditModalClose={() => setNationalityEditModalOpen(false)}
            onNationalitySaved={(savedNationality) => {
                setNationalityEditModalOpen(false);
                loadNationalities();
            }}
        />
        <NationalityDeleteConfirmationModal
            open={deleteModalOpen}
            nationalityName={deleteNationality?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteNationality!.id) }
        />
    </Box>
  );
};

export default Nationalities;
