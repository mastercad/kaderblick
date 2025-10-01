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
import AgeGroupDetailsModal from '../modals/AgeGroupDetailsModal';
import AgeGroupDeleteConfirmationModal from '../modals/AgeGroupDeleteConfirmationModal';
import AgeGroupEditModal from '../modals/AgeGroupEditModal';
import { AgeGroup } from '../types/ageGroup';

const AgeGroups = () => {
  const [ageGroups, setAgeGroups] = useState<AgeGroup[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [ageGroupId, setAgeGroupId] = useState<number | null>(null);
  const [ageGroupDetailsModalOpen, setAgeGroupDetailsModalOpen] = useState(false);
  const [ageGroupEditModalOpen, setAgeGroupEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteAgeGroup, setDeleteAgeGroup] = useState<AgeGroup | null>(null);

  const loadAgeGroups = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ ageGroups: AgeGroup[] }>('/api/age-groups');
      if (res && Array.isArray(res.ageGroups)) {
        setAgeGroups(res.ageGroups);
      } else {
        setAgeGroups([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Altersgruppen.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAgeGroups();
  }, []);

  const handleDelete = async (ageGroupId: number) => {
    try {
      await apiJson(`/api/age-groups/${ageGroupId}`, { method: 'DELETE' });
      setAgeGroups(ageGroups => ageGroups.filter(c => c.id !== ageGroupId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Altersgruppe.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Altersgruppen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setAgeGroupId(null); setAgeGroupEditModalOpen(true) }}>
          Neue Altersgruppe erstellen
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
                <TableCell>Bezeichnung</TableCell>
                <TableCell>Min Age</TableCell>
                <TableCell>Max Age</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {ageGroups.map((ageGroup, idx) => (
                <TableRow key={ageGroup.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setAgeGroupId(ageGroup.id);
                      setAgeGroupDetailsModalOpen(true);
                    }}
                  >
                    {ageGroup.name || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setAgeGroupId(ageGroup.id);
                      setAgeGroupDetailsModalOpen(true);
                    }}
                  >
                    {ageGroup.englishName || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setAgeGroupId(ageGroup.id);
                      setAgeGroupDetailsModalOpen(true);
                    }}
                  >
                    {ageGroup.minAge || ''}
                  </TableCell>
                  <TableCell sx={{ cursor: 'pointer' }}
                    onClick={() => {
                      setAgeGroupId(ageGroup.id);
                      setAgeGroupDetailsModalOpen(true);
                    }}
                  >
                    {ageGroup.maxAge || ''}
                  </TableCell>
                  <TableCell>
                    { ageGroup.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setAgeGroupId(ageGroup.id);
                          setAgeGroupEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Altergruppe bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { ageGroup.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteAgeGroup(ageGroup);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Altergruppe löschen"
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
        <AgeGroupDetailsModal
            ageGroupDetailOpen={ageGroupDetailsModalOpen}
            loadAgeGroups={() => loadAgeGroups()}
            ageGroupId={ageGroupId}
            onClose={() => setAgeGroupDetailsModalOpen(false)}
        />
        <AgeGroupEditModal
            openAgeGroupEditModal={ageGroupEditModalOpen}
            ageGroupId={ageGroupId}
            onAgeGroupEditModalClose={() => setAgeGroupEditModalOpen(false)}
            onAgeGroupSaved={(savedAgeGroup) => {
                setAgeGroupEditModalOpen(false);
                loadAgeGroups();
            }}
        />
        <AgeGroupDeleteConfirmationModal
            open={deleteModalOpen}
            ageGroupName={deleteAgeGroup?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteAgeGroup.id) }
        />
    </Box>
  );
};

export default AgeGroups;
