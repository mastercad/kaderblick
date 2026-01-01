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
import CameraDeleteConfirmationModal from '../modals/CameraDeleteConfirmationModal';
import CameraEditModal from '../modals/CameraEditModal';
import { Camera } from '../types/camera';

const Cameras = () => {
  const [cameras, setCameras] = useState<Camera[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [cameraId, setCameraId] = useState<number | null>(null);
  const [cameraEditModalOpen, setCameraEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteCamera, setDeleteCamera] = useState<Camera | null>(null);

  const loadCameras = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ cameras: Camera[] }>('/api/cameras');
      if (res && Array.isArray(res.cameras)) {
        setCameras(res.cameras);
      } else {
        setCameras([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Kameras.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCameras();
  }, []);

  const handleDelete = async (cameraId: number) => {
    try {
      await apiJson(`/api/cameras/${cameraId}`, { method: 'DELETE' });
      setCameras(cameras => cameras.filter(c => c.id !== cameraId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Kamera.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Kameras
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setCameraId(null); setCameraEditModalOpen(true) }}>
          Neue Kamera erstellen
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
                <TableCell>Erstellt von</TableCell>
                <TableCell>Erstellt am</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {cameras.map((camera, idx) => (
                <TableRow key={camera.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {camera.name || ''}
                  </TableCell>
                  <TableCell>
                    {camera.createdFrom?.fullName || ''}
                  </TableCell>
                  <TableCell>
                    {camera.createdAt ? new Date(camera.createdAt).toLocaleDateString('de-DE') : ''}
                  </TableCell>
                  <TableCell>
                    { camera.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setCameraId(camera.id);
                          setCameraEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Kamera bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { camera.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteCamera(camera);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Kamera löschen"
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
        <CameraEditModal
            openCameraEditModal={cameraEditModalOpen}
            cameraId={cameraId}
            onCameraEditModalClose={() => setCameraEditModalOpen(false)}
            onCameraSaved={(savedCamera) => {
                setCameraEditModalOpen(false);
                loadCameras();
            }}
        />
        <CameraDeleteConfirmationModal
            open={deleteModalOpen}
            cameraName={deleteCamera?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteCamera.id) }
        />
    </Box>
  );
};

export default Cameras;
