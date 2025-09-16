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
import StrongFeetDeleteConfirmationModal from '../modals/StrongFeetDeleteConfirmationModal';
import StrongFeetEditModal from '../modals/StrongFeetEditModal';
import { StrongFeet } from '../types/strongFeet';

const StrongFeets = () => {
  const [strongFeets, setStrongFeets] = useState<StrongFeet[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [strongFeetId, setStrongFeetId] = useState<number | null>(null);
  const [strongFeetEditModalOpen, setStrongFeetEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteStrongFeet, setDeleteStrongFeet] = useState<StrongFeet | null>(null);

  const loadStrongFeets = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ strongFeets: StrongFeet[] }>('/api/strong-feet');
      if (res && Array.isArray(res.strongFeets)) {
        setStrongFeets(res.strongFeets);
      } else {
        setStrongFeets([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der starken Füße.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadStrongFeets();
  }, []);

  const handleDelete = async (strongFeetId: number) => {
    try {
      await apiJson(`/api/strong-feet/${strongFeetId}`, { method: 'DELETE' });
      setStrongFeets(strongFeets => strongFeets.filter(c => c.id !== strongFeetId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der starken Füße.');
    }
  };

  return (
    <Box sx={{mx: 'auto', mt: 4, p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Starke Füße
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setStrongFeetId(null); setStrongFeetEditModalOpen(true) }}>
          Neuen starken Fuß erstellen
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
              {strongFeets.map((strongFeet, idx) => (
                <TableRow key={strongFeet.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {strongFeet.name || ''}
                  </TableCell>
                  <TableCell>
                    {strongFeet.code || ''}
                  </TableCell>
                  <TableCell>
                    { strongFeet.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setStrongFeetId(strongFeet.id);
                          setStrongFeetEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Starken Fuß bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { strongFeet.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteStrongFeet(strongFeet);
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
        <StrongFeetEditModal
            openStrongFeetEditModal={strongFeetEditModalOpen}
            strongFeetId={strongFeetId}
            onStrongFeetEditModalClose={() => setStrongFeetEditModalOpen(false)}
            onStrongFeetSaved={(savedStrongFeet) => {
                setStrongFeetEditModalOpen(false);
                loadStrongFeets();
            }}
        />
        <StrongFeetDeleteConfirmationModal
            open={deleteModalOpen}
            strongFeetName={deleteStrongFeet?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteStrongFeet.id) }
        />
    </Box>
  );
};

export default StrongFeets;
