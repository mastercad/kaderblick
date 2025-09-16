import React, { useEffect, useState } from 'react';
import LocationEditModal, { LocationFormValues } from '../modals/LocationEditModal';
import { Box, Typography, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Button, Paper } from '@mui/material';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import { Location } from '../types/location';
import { SurfaceType } from '../types/surfaceType';

const Locations = () => {
  const [locations, setLocations] = useState<Location[]>([]);
  const [surfaceTypes, setSurfaceTypes] = useState<SurfaceType[]>([]);
  const [loading, setLoading] = useState(true);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editInitialValues, setEditInitialValues] = useState<LocationFormValues | undefined>(undefined);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Location | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);

  useEffect(() => {
    loadLocations();
  }, []);

  const loadLocations = async () => {
    try {
      const res = await apiJson('/api/locations');
      setLocations(res.locations || []);
      setSurfaceTypes(res.surfaceTypes || []);
    } catch (e) {
      // handle error
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <Box sx={{ p: 4 }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 4 }}>
        <Typography variant="h4">Spielstätten verwalten</Typography>
        <Button
          variant="contained"
          color="primary"
          onClick={() => {
            setEditInitialValues(undefined);
            setEditModalOpen(true);
          }}
        >
          Neue Spielstätte anlegen
        </Button>
      </Box>
      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>Name</TableCell>
              <TableCell>Adresse</TableCell>
              <TableCell>Stadt</TableCell>
              <TableCell>Latitude</TableCell>
              <TableCell>Longitude</TableCell>
              <TableCell>Aktionen</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {locations.map((location) => (
              <TableRow key={location.id}>
                <TableCell>{location.name}</TableCell>
                <TableCell>{location.address}</TableCell>
                <TableCell>{location.city}</TableCell>
                <TableCell>{location.latitude}</TableCell>
                <TableCell>{location.longitude}</TableCell>
                <TableCell>
                  {location.permissions?.canEdit && (
                    <Button
                      size="small"
                      variant="contained"
                      color="primary"
                      startIcon={<EditIcon />}
                      sx={{ mr: 1 }}
                      onClick={() => {
                        setEditInitialValues({
                          id: location.id,
                          name: location.name,
                          address: location.address,
                          city: location.city,
                          latitude: location.latitude,
                          longitude: location.longitude,
                          capacity: location.capacity ?? '',
                          surfaceType: location.surfaceType?.id ?? '',
                          hasFloodlight: location.hasFloodlight ?? false,
                          facilities: location.facilities ?? '',
                        });
                        setEditModalOpen(true);
                      }}
                    >
                      Bearbeiten
                    </Button>
                  )}
                  {location.permissions?.canDelete && (
                    <Button
                        size="small"
                        variant="contained"
                        color="error"
                        startIcon={<DeleteIcon />}
                        onClick={() => {
                        setDeleteTarget(location);
                        setDeleteModalOpen(true);
                        }}
                    >
                        Löschen
                    </Button>
                      )}
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
      <LocationEditModal
        openLocationEditModal={editModalOpen}
        onLocationEditModalClose={() => setEditModalOpen(false)}
        initialValues={editInitialValues}
        isEdit={!!editInitialValues}
        surfaceTypes={surfaceTypes}
        onSaved={() => {
          setLoading(true);
          loadLocations();
        }}
      />
      <DynamicConfirmationModal
          open={deleteModalOpen}
          onClose={() => setDeleteModalOpen(false)}
          onConfirm={async () => {
          if (!deleteTarget) return;
          setDeleteLoading(true);
          try {
              await apiJson(`/locations/delete/${deleteTarget.id}`, { method: 'DELETE' });
              setLocations((prev) => prev.filter(l => l.id !== deleteTarget.id));
              setDeleteModalOpen(false);
              setDeleteTarget(null);
          } finally {
              setDeleteLoading(false);
          }
          }}
          title="Löschen bestätigen"
          message={`Möchtest du die Spielstätte "${deleteTarget?.name}" wirklich löschen?`}
          confirmText="Löschen"
          confirmColor="error"
          loading={deleteLoading}
      />
    </Box>
  );
};

export default Locations;
