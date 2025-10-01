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
import PositionDeleteConfirmationModal from '../modals/PositionDeleteConfirmationModal';
import PositionEditModal from '../modals/PositionEditModal';
import { Position } from '../types/position';

const Positions = () => {
  const [positions, setPositions] = useState<Position[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [positionId, setPositionId] = useState<number | null>(null);
  const [positionEditModalOpen, setPositionEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deletePosition, setDeletePosition] = useState<Position | null>(null);

  const loadPositions = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiJson<{ positions: Position[] }>('/api/positions');
      if (res && Array.isArray(res.positions)) {
        setPositions(res.positions);
      } else {
        setPositions([]);
      }
    } catch (e) {
      setError('Fehler beim Laden der Positionen.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPositions();
  }, []);

  const handleDelete = async (positionId: number) => {
    try {
      await apiJson(`/api/positions/${positionId}`, { method: 'DELETE' });
      setPositions(positions => positions.filter(c => c.id !== positionId));
      setDeleteModalOpen(false);
    } catch (e) {
      alert('Fehler beim Löschen der Position.');
    }
  };

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">
          Positionen
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setPositionId(null); setPositionEditModalOpen(true) }}>
          Neue Position erstellen
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
                <TableCell>Beschreibung</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {positions.map((position, idx) => (
                <TableRow key={position.id}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? 'background.paper' : 'grey.100'
                  }}
                >
                  <TableCell>
                    {position.name || ''}
                  </TableCell>
                  <TableCell>
                    {position.shortName || ''}
                  </TableCell>
                  <TableCell>
                    {position.description || ''}
                  </TableCell>
                  <TableCell>
                    { position.permissions?.canEdit && (
                      <IconButton color="primary"
                        size="small"
                        onClick={() => {
                          setPositionId(position.id);
                          setPositionEditModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Position bearbeiten"
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    { position.permissions?.canDelete && (
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => {
                          setDeletePosition(position);
                          setDeleteModalOpen(true);
                        }}
                        sx={{ ml: 1 }}
                        aria-label="Position löschen"
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
        <PositionEditModal
            openPositionEditModal={positionEditModalOpen}
            positionId={positionId}
            onPositionEditModalClose={() => setPositionEditModalOpen(false)}
            onPositionSaved={(savedPosition) => {
                setPositionEditModalOpen(false);
                loadPositions();
            }}
        />
        <PositionDeleteConfirmationModal
            open={deleteModalOpen}
            positionName={deletePosition?.name}
            onClose={() => setDeleteModalOpen(false)}
            onConfirm={async () => handleDelete(deletePosition.id) }
        />
    </Box>
  );
};

export default Positions;
