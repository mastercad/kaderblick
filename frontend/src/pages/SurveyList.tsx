import React, { useEffect, useState } from 'react';
import { Card, CardContent, Typography, Box, CircularProgress, Alert, Button, Dialog, IconButton, Stack, Tooltip } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import AddIcon from '@mui/icons-material/Add';
import LinkIcon from '@mui/icons-material/Link';
import SurveyCreateWizard from './SurveyCreateWizard';
import SurveyStatusDialog from './SurveyStatusDialog';
import { ConfirmationModal } from '../modals/ConfirmationModal';
import { apiJson } from '../utils/api';

interface Survey {
  id: number;
  title: string;
  description?: string;
}

const SurveyList: React.FC = () => {
  const [surveys, setSurveys] = useState<Survey[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [wizardOpen, setWizardOpen] = useState(false);
  const [editSurvey, setEditSurvey] = useState<Survey | null>(null);
  const [reloadFlag, setReloadFlag] = useState(0);
  const [deleteSurveyId, setDeleteSurveyId] = useState<number | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [statusDialogOpen, setStatusDialogOpen] = useState(false);
  const [selectedSurveyId, setSelectedSurveyId] = useState<number | null>(null);
  const navigate = useNavigate();
  const [tooltipOpenId, setTooltipOpenId] = useState<number | null>(null);

  useEffect(() => {
    setLoading(true);
    apiJson<Survey[]>('/api/surveys')
      .then((data) => {
        setSurveys(data);
        setLoading(false);
      })
      .catch(() => {
        setError('Fehler beim Laden der Umfragen');
        setLoading(false);
      });
  }, [reloadFlag]);

  if (loading) return <CircularProgress />;
  if (error) return <Alert severity="error">{error}</Alert>;

  return (
    <Box sx={{mx: 'auto', mt: 4, p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">Umfragen</Typography>
        {surveys.length > 0 && (
          <Button variant="contained" startIcon={<AddIcon />} onClick={() => setWizardOpen(true)}>
            Neue Umfrage erstellen
          </Button>
        )}
      </Stack>
        {loading ? (
          <Typography>Loading...</Typography>
        ) : surveys.length === 0 ? (
          <Box textAlign="center" py={5}>
            <Typography color="text.secondary" variant="h6">Keine Umfragen verfügbar.</Typography>
            <Button variant="outlined" startIcon={<AddIcon />} onClick={() => setWizardOpen(true)} sx={{ mt: 2 }}>
              Erste Umfrage erstellen
            </Button>
          </Box>
        ) : (
            surveys.map((survey) => (
          <Card
              key={survey.id}
              sx={{ width: '100%', minWidth: 0, maxWidth: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'stretch', position: 'relative' }}
            >
              <Tooltip
              title="Link kopiert!"
              open={tooltipOpenId === survey.id}
              disableFocusListener
              disableHoverListener
              disableTouchListener
              placement="top"
              arrow
            >
              <IconButton
                aria-label="Umfragelink kopieren"
                size="small"
                sx={{ position: 'absolute', top: 8, right: 72, zIndex: 2 }}
                color="primary"
                onClick={async e => {
                  e.stopPropagation();
                  try {
                    await navigator.clipboard.writeText(`${window.location.origin}/api/surveys/${survey.id}`);
                    setTooltipOpenId(survey.id);
                    setTimeout(() => setTooltipOpenId(null), 1000);
                  } catch (err) {
                    // Fehlerbehandlung optional
                  }
                }}
              >
                <LinkIcon fontSize="small" />
              </IconButton>
            </Tooltip>
            <IconButton
              aria-label="Umfrage bearbeiten"
              size="small"
              sx={{ position: 'absolute', top: 8, right: 40, zIndex: 2 }}
              onClick={async e => {
                e.stopPropagation();
                try {
                  const fullSurvey = await apiJson(`/api/surveys/${survey.id}`);
                  setEditSurvey(fullSurvey);
                  setWizardOpen(true);
                } catch (err) {
                  // Fehlerbehandlung optional
                }
              }}
            >
              <EditIcon fontSize="small" />
            </IconButton>
            <IconButton
              aria-label="Umfrage löschen"
              size="small"
              sx={{ position: 'absolute', top: 8, right: 8, zIndex: 2 }}
              onClick={e => {
                e.stopPropagation();
                setDeleteSurveyId(survey.id);
              }}
              color="error"
            >
              <DeleteIcon fontSize="small" />
            </IconButton>
            <CardContent
              sx={{ cursor: 'pointer' }}
              onClick={() => { setSelectedSurveyId(survey.id); setStatusDialogOpen(true); }}
            >
              <Typography variant="h6">{survey.title}</Typography>
              {survey.description && (
                <Typography variant="body2" color="text.secondary">
                  {survey.description}
                </Typography>
              )}
              <Stack direction="row" spacing={1} mt={2}>
                <Button
                  variant="outlined"
                  size="small"
                  onClick={e => {
                    e.stopPropagation();
                    navigate(`/survey/fill/${survey.id}`);
                  }}
                >
                  Ausfüllen
                </Button>
              </Stack>
            </CardContent>
          </Card>
        ))
      )}
      <ConfirmationModal
        open={!!deleteSurveyId}
        onClose={() => setDeleteSurveyId(null)}
        onConfirm={async () => {
          if (!deleteSurveyId) return;
          setDeleteLoading(true);
          try {
            await apiJson(`/api/surveys/${deleteSurveyId}`, { method: 'DELETE' });
            setDeleteSurveyId(null);
            setReloadFlag(f => f + 1);
          } catch (e) {
            // Fehlerbehandlung optional
            setDeleteSurveyId(null);
          } finally {
            setDeleteLoading(false);
          }
        }}
        title="Umfrage löschen"
        message="Möchtest du diese Umfrage wirklich löschen?"
        confirmText={deleteLoading ? 'Lösche...' : 'Löschen'}
        confirmColor="error"
        cancelText="Abbrechen"
      />
      <SurveyStatusDialog
        surveyId={selectedSurveyId}
        open={statusDialogOpen}
        onClose={() => setStatusDialogOpen(false)}
      />
      <Dialog open={wizardOpen} onClose={() => { setWizardOpen(false); setEditSurvey(null); }} maxWidth="md" fullWidth>
        <SurveyCreateWizard
          onSurveyCreated={() => { setWizardOpen(false); setEditSurvey(null); setReloadFlag(f => f + 1); }}
          editSurvey={editSurvey}
        />
      </Dialog>
    </Box>
  );
};

export default SurveyList;
