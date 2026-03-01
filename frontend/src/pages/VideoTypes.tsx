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
import VideoTypeDeleteConfirmationModal from '../modals/VideoTypeDeleteConfirmationModal';
import VideoTypeEditModal from '../modals/VideoTypeEditModal';
import { VideoType } from '../types/videoType';

const VideoTypes = () => {
  const [videoTypes, setVideoTypes] = useState<VideoType[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [videoTypeId, setVideoTypeId] = useState<number | null>(null);
  const [videoTypeEditModalOpen, setVideoTypeEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteVideoType, setDeleteVideoType] = useState<VideoType | null>(null);

  const loadVideoTypes = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ videoTypes: VideoType[] }>('/api/video-types');
      if (res && Array.isArray(res.videoTypes)) {
        setVideoTypes(res.videoTypes);
      } else {
        setVideoTypes([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Videotypen.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadVideoTypes();
  }, []);

  const handleDelete = async (videoTypeId: number) => {
    try {
      await apiJson(`/api/video-types/${videoTypeId}`, { method: 'DELETE' });
      setVideoTypes(videoTypes => videoTypes.filter(vt => vt.id !== videoTypeId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen des Videotyps.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Videotypen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setVideoTypeId(null); setVideoTypeEditModalOpen(true) }}>
          Neuen Videotyp erstellen
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
                <TableCell>Sortierung</TableCell>
                <TableCell>Erstellt von</TableCell>
                <TableCell>Erstellt am</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {videoTypes.map((videoType, idx) => (
                <TableRow key={videoType.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {videoType.name || ''}
                  </TableCell>
                  <TableCell>
                    {videoType.sort}
                  </TableCell>
                  <TableCell>
                    {videoType.createdFrom?.fullName || ''}
                  </TableCell>
                  <TableCell>
                    {videoType.createdAt ? new Date(videoType.createdAt).toLocaleDateString('de-DE') : ''}
                  </TableCell>
                  <TableCell>
                    { videoType.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setVideoTypeId(videoType.id);
                          setVideoTypeEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Videotyp bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { videoType.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeleteVideoType(videoType);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Videotyp löschen"
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
        <VideoTypeEditModal
            openVideoTypeEditModal={videoTypeEditModalOpen}
            videoTypeId={videoTypeId}
            onVideoTypeEditModalClose={() => setVideoTypeEditModalOpen(false)}
            onVideoTypeSaved={(savedVideoType) => {
                setVideoTypeEditModalOpen(false);
                loadVideoTypes();
            }}
        />
        <VideoTypeDeleteConfirmationModal
            open={deleteModalOpen}
            videoTypeName={deleteVideoType?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deleteVideoType!.id) }
        />
    </Box>
  );
};

export default VideoTypes;
