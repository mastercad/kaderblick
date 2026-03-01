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
import SurfaceTypeDeleteConfirmationModal from '../modals/SurfaceTypeDeleteConfirmationModal';
import SurfaceTypeEditModal from '../modals/SurfaceTypeEditModal';
import { SurfaceType } from '../types/surfaceType';

const SurfaceTypes = () => {
  const [surfaceTypes, setSurfaceTypes] = useState<SurfaceType[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [surfaceTypeId, setSurfaceTypeId] = useState<number | null>(null);
  const [surfaceTypeEditModalOpen, setSurfaceTypeEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteSurfaceType, setDeleteSurfaceType] = useState<SurfaceType | null>(null);

  const loadSurfaceTypes = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ surfaceTypes: SurfaceType[] }>('/api/surface-types');
      if (res && Array.isArray(res.surfaceTypes)) {
        setSurfaceTypes(res.surfaceTypes);
      } else {
        setSurfaceTypes([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Oberflächenarten.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadSurfaceTypes();
  }, []);

  const handleDelete = async (surfaceTypeId: number) => {
    try {
      await apiJson(`/api/surface-types/${surfaceTypeId}`, { method: 'DELETE' });
      setSurfaceTypes(surfaceTypes => surfaceTypes.filter(c => c.id !== surfaceTypeId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Oberflächenarten.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Oberflächenarten
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setSurfaceTypeId(null); setSurfaceTypeEditModalOpen(true) }}>
          Neue Oberflächenart erstellen
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
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {surfaceTypes.map((surfaceType, idx) => (
                <TableRow key={surfaceType.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {surfaceType.name || ''}
                  </TableCell>
                  <TableCell>
                    {surfaceType.description || ''}
                  </TableCell>
                  <TableCell>
                    { surfaceType.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setSurfaceTypeId(surfaceType.id);
                          setSurfaceTypeEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Oberflächenart bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { surfaceType.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteSurfaceType(surfaceType);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Oberflächenart löschen"
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
        <SurfaceTypeEditModal
            openSurfaceTypeEditModal={surfaceTypeEditModalOpen}
            surfaceTypeId={surfaceTypeId}
            onSurfaceTypeEditModalClose={() => setSurfaceTypeEditModalOpen(false)}
            onSurfaceTypeSaved={(savedSurfaceType) => {
                setSurfaceTypeEditModalOpen(false);
                loadSurfaceTypes();
            }}
        />
        <SurfaceTypeDeleteConfirmationModal
            open={deleteModalOpen}
            surfaceTypeName={deleteSurfaceType?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteSurfaceType!.id) }
        />
    </Box>
  );
};

export default SurfaceTypes;
