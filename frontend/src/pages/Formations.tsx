import React, { useEffect, useState } from 'react';
import { Box, Typography, Button, Card, CardHeader, CardContent, CardActions, Badge, IconButton } from '@mui/material';
import AddIcon from '@mui/icons-material/AddCircle';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import FormationEditModal from '../modals/FormationEditModal';
import FormationDeleteConfirmationModal from '../modals/FormationDeleteConfirmationModal';

// Typdefinitionen (angepasst an FormationController/index.html.twig)
interface FormationType {
  name: string;
  cssClass?: string;
  backgroundPath?: string;
}

interface PlayerData {
  x: number;
  y: number;
  number: string | number;
}

interface FormationData {
  code?: string;
  players?: PlayerData[];
}

interface Formation {
  id: number;
  name: string;
  formationType: FormationType;
  formationData: FormationData;
}

const Formations: React.FC = () => {
  const [formations, setFormations] = useState<Formation[]>([]);
  const [loading, setLoading] = useState(true);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editFormationId, setEditFormationId] = useState<number | null>(null);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteFormation, setDeleteFormation] = useState<Formation | null>(null);

  useEffect(() => {
    apiJson<{ formations: Formation[] }>('/formations')
      .then(data => setFormations(Array.isArray(data.formations) ? data.formations : []))
      .finally(() => setLoading(false));
  }, []);

  return (
    <Box sx={{ p: 3 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">Meine Teamaufstellungen</Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => {
            setEditFormationId(null);
            setEditModalOpen(true);
          }}
        >
          Neue Aufstellung
        </Button>
      </Box>
      {loading ? (
      <Typography>Lade Aufstellungen...</Typography>
      ) : formations.length > 0 ? (
        <Box display="flex" flexWrap="wrap" gap={3}>
          {formations.map((formation) => (
            <Card key={formation.id} sx={{ width: 340, display: 'flex', flexDirection: 'column', minHeight: 340 }}>
              <CardHeader
                title={<Typography variant="h6">{formation.name}</Typography>}
                action={
                  <Badge color="secondary" badgeContent={formation.formationType.name} />
                }
              />
              <CardContent>
                <Box
                  className={`formation-preview formation-background sports-field ${formation.formationType.cssClass || 'field-default'}`}
                  sx={{
                    width: '100%',
                    height: 180,
                    backgroundImage: `url(/images/formation/${formation.formationType.backgroundPath || 'fussballfeld_haelfte.jpg'})`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                    backgroundRepeat: 'no-repeat',
                    position: 'relative',
                    mb: 2,
                  }}
                  data-background-image={formation.formationType.backgroundPath || 'fussballfeld_haelfte.jpg'}
                >
                  {(formation.formationData.players || []).map((player, idx) => (
                    <Box
                      key={idx}
                      sx={{
                        position: 'absolute',
                        left: `${player.x}%`,
                        top: `${player.y}%`,
                        width: 28,
                        height: 28,
                        bgcolor: 'primary.main',
                        color: 'white',
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontWeight: 700,
                        fontSize: 16,
                        border: '2px solid #fff',
                        boxShadow: 2,
                        transform: 'translate(-50%, -50%)',
                      }}
                    >
                      {player.number}
                    </Box>
                  ))}
                </Box>
                <Typography variant="body2" color="text.secondary" mb={1}>
                  Aufstellung: {formation.formationData.code || 'Kein Code'}
                </Typography>
              </CardContent>
              <CardActions sx={{ mt: 'auto' }}>
                <Button
                  size="small"
                  variant="outlined"
                  startIcon={<EditIcon />}
                  onClick={() => {
                    setEditFormationId(formation.id);
                    setEditModalOpen(true);
                  }}
                >
                  Bearbeiten
                </Button>
                <IconButton
                  size="small"
                  color="error"
                  onClick={() => {
                    setDeleteFormation(formation);
                    setDeleteModalOpen(true);
                  }}
                  sx={{ ml: 1 }}
                  aria-label="Formation löschen"
                >
                  <DeleteIcon />
                </IconButton>
              </CardActions>
              <FormationDeleteConfirmationModal
                open={deleteModalOpen}
                formationName={deleteFormation?.name}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={async () => {
                  if (!deleteFormation) return;
                  try {
                    await apiJson(`/formation/${deleteFormation.id}/delete`, { method: 'DELETE' });
                    setFormations(prev => prev.filter(f => f.id !== deleteFormation.id));
                  } catch (e) {
                    // Fehlerbehandlung ggf. Toast
                  } finally {
                    setDeleteModalOpen(false);
                    setDeleteFormation(null);
                  }
                }}
              />
            </Card>
          ))}
        </Box>
      ) : (
        <Box mt={2}>
          <Typography variant="body1" color="info.main">
            Sie haben noch keine Aufstellungen erstellt.{' '}
            <Button variant="text" onClick={() => {
              setEditFormationId(null);
              setEditModalOpen(true);
            }}>
              Erstellen Sie jetzt Ihre erste Aufstellung
            </Button>.
          </Typography>
        </Box>
      )}
      <FormationEditModal
        open={editModalOpen}
        formationId={editFormationId}
        onClose={() => setEditModalOpen(false)}
        onSaved={(savedFormation) => {
          setEditModalOpen(false);
          setFormations(prev => {
            if (!savedFormation) return prev;
            const exists = prev.some(f => f.id === savedFormation.id);
            if (exists) {
              // Update bestehende Formation
              return prev.map(f => f.id === savedFormation.id ? savedFormation : f);
            } else {
              // Neue Formation hinzufügen
              return [savedFormation, ...prev];
            }
          });
        }}
      />
    </Box>
  );
};

export default Formations;
